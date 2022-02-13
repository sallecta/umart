<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\User;
use Umart\Classes\Utility;
use Umart\View\ListView;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

class UmartViewProductList extends ListView
{
	protected $showNavbar = false;
	protected $useFilter = false;
	protected $addToolbar = false;
	protected $filters = [];
	protected $category;
	protected $menuItem = null;

	public function display($tpl = null)
	{
		$app   = plg_sytem_umart_main('app');
		$menu  = $app->getMenu('site')->getActive();
		$model = plg_sytem_umart_main('model', 'products', UMART_COMPONENT_ADMINISTRATOR);

		if (!plg_sytem_umart_main(User::class)->core('admin'))
		{
			$model->setState('filter.published', 1);
		}

		$categoryId = (int) $app->input->get('id', 0, 'uint');
		$nodes      = Categories::getInstance('umart.product');
		$category   = $nodes->get($categoryId);

		if ($categoryId < 1 || !$category)
		{
			throw new RuntimeException(Text::_('COM_UMART_ERROR_CATEGORY_NOT_FOUND'));
		}

		$category->params = new Registry($category->params);
		$ignoreNames      = [];

		foreach ($category->params->toArray() as $name => $value)
		{
			if ($this->config->exists($name) && trim($value) !== '')
			{
				$this->config->set($name, $value);
				$ignoreNames[] = $name;
			}
		}

		if ($menu
			&& @$menu->query['option'] == 'com_umart'
			&& @$menu->query['view'] == 'productlist'
		)
		{
			if (@$menu->query['id'] == $categoryId)
			{
				$this->menuItem = $menu;
			}

			foreach ($menu->getParams()->toArray() as $name => $value)
			{
				if (!in_array($name, $ignoreNames)
					&& $this->config->exists($name)
					&& trim($value) !== ''
				)
				{
					$this->config->set($name, $value);
				}
			}
		}

		$model->setState('filter.category_id', $categoryId);
		$model->setState('filter.include_sub_categories', (int) $this->config->get('product_in_sub_categories'));
		$model->setState('filter.language', Multilanguage::isEnabled());
		$limitList      = $this->config->get('list_limit');
		$this->category = $category;
		$this->filters  = array_merge(
			[
				'sort'    => $this->config->get('product_list_default_ordering', 'ordering'),
				'display' => (int) $this->config->get('product_list_default_limit', $limitList[0]),
			],
			(array) $app->getUserState('com_umart.product_filter_list', [])
		);
		/** @var Utility $utility */
		$utility = plg_sytem_umart_main(Utility::class);
		$utility->parseOrderingData($this->filters['sort'], $ordering, $direction);
		$display = abs((int) $this->filters['display']);
		$model->setState('list.ordering', $ordering);
		$model->setState('list.direction', $direction);
		$model->setState('list.start', $app->input->get('limitstart', 0, 'uint'));
		$model->setState('list.limit', $display > 0 ? $display : (int) $limitList[0]);
		$this->setModel($model, true);
		$displayMode = $this->config->get('list_mode', 'toggle');

		if ($displayMode != 'toggle')
		{
			plg_sytem_umart_main('doc')->addScriptDeclaration(<<<JS
				_umart.$(document).ready(function() {
				    _umart.storage.setData('list.view', '{$displayMode}');
				});
JS
			);
		}

		$this->prepareDocument();
		parent::display($tpl);
	}

	protected function prepareDocument()
	{
		$document = plg_sytem_umart_main('doc');

		if ($document->getType() != 'html')
		{
			return;
		}

		$app      = plg_sytem_umart_main('app');
		$pathway  = $app->getPathway();
		$title    = trim($this->category->title);
		$metaDesc = trim($this->category->metadesc);
		$metaKey  = trim($this->category->metakey);
		$domain   = Uri::getInstance()->toString(['scheme', 'host']);
		$document->addHeadLink(htmlspecialchars($domain . Route::_(UmartHelperRoute::getCategoryRoute($this->category->id), false)), 'canonical');

		if ($this->menuItem)
		{
			$menuParams = $this->menuItem->getParams();

			if ($menuParams->get('page_title', ''))
			{
				$title = $menuParams->get('page_title');
			}

			if ($menuParams->get('menu-meta_description'))
			{
				$metaDesc = $menuParams->get('menu-meta_description');
			}

			if ($menuParams->get('menu-meta_keywords'))
			{
				$metaKey = $menuParams->get('menu-meta_keywords');
			}
		}

		if (empty($metaDesc))
		{
			$category = $this->category;

			while ($category && empty($metaDesc))
			{
				$category = $category->getParent();
				$metaDesc = $category ? $category->metadesc : $metaDesc;
			}
		}

		if (empty($metaKey))
		{
			$category = $this->category;

			while ($category && empty($metaKey))
			{
				$category = $category->getParent();
				$metaKey  = $category ? $category->metakey : $metaKey;
			}
		}

		if ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$document->setTitle($title);

		if ($metaDesc)
		{
			$document->setDescription($metaDesc);
		}

		if ($metaKey)
		{
			$document->setMetadata('keywords', $metaKey);
		}

		if ($this->category->params->get('robots'))
		{
			$document->setMetadata('robots', $this->category->params->get('robots'));
		}

		if (!is_object($this->category->metadata))
		{
			$this->category->metadata = new Registry($this->category->metadata);
		}

		if (($app->get('MetaAuthor') == '1') && $this->category->get('author', ''))
		{
			$document->setMetaData('author', $this->category->get('author', ''));
		}

		foreach ($this->category->metadata->toArray() as $k => $v)
		{
			if ($v)
			{
				$document->setMetadata($k, $v);
			}
		}

		if ($menu = $app->getMenu()->getActive())
		{
			$id   = isset($menu->query['id']) ? (int) $menu->query['id'] : 0;
			$view = isset($menu->query['view']) ? $menu->query['view'] : '';

			if ($view === 'productdetail' || ($view === 'productlist' && $id != $this->category->id))
			{
				$path     = [
					[
						'title' => $this->category->title,
						'link'  => '',
					],
				];
				$category = $this->category->getParent();

				if ($view === 'productdetail')
				{
					while ($category && $category->id > 1)
					{
						$path[]   = [
							'title' => $category->title,
							'link'  => UmartHelperRoute::getCategoryRoute($category->id, $category->language),
						];
						$category = $category->getParent();
					}
				}
				elseif ($view === 'productlist')
				{
					while ($category && $id != $category->id && $category->id > 1)
					{
						$path[]   = [
							'title' => $category->title,
							'link'  => UmartHelperRoute::getCategoryRoute($category->id, $category->language),
						];
						$category = $category->getParent();
					}
				}

				$path = array_reverse($path);

				foreach ($path as $item)
				{
					$pathway->addItem($item['title'], $item['link']);
				}
			}
		}
	}
}
