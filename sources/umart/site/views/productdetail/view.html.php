<?php

/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\Event;
use ES\Classes\Product;
use ES\Classes\User;
use ES\View\BaseView;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class EasyshopViewProductDetail extends BaseView
{
	protected $product;
	protected $event;
	protected $menuItem = null;

	public function __construct(array $config)
	{
		/**
		 * @var  $productClass Product
		 * @var  $user         User
		 * @var  $app          CMSApplication
		 */
		$app           = easyshop('app');
		$productClass  = easyshop(Product::class);
		$user          = easyshop(User::class);
		$pk            = $app->input->getInt('id');
		$this->product = $productClass->getItem($pk);
		$state         = (int) $this->product->state;
		$approved      = (int) $this->product->approved;
		$isUserAdmin   = $user->core('admin');

		if ($state === -2 || ($state === 0 && !$isUserAdmin))
		{
			throw new RuntimeException(Text::_('COM_EASYSHOP_CART_ERROR_PRODUCT_NOT_FOUND'), 404);
		}

		if (!$approved && !$isUserAdmin)
		{
			throw new RuntimeException(Text::_('COM_EASYSHOP_PRODUCT_NOT_APPROVED'), 403);
		}

		$app->triggerEvent('onEasyshopProductConstructView', [$this]);

		parent::__construct($config);
	}

	public function display($tpl = null)
	{
		$app  = easyshop('app');
		$menu = $app->getMenu()->getActive();
		$app->triggerEvent('onEasyshopPrepareItem', ['com_easyshop.product', $this->product]);
		$ignoreNames = [];

		foreach ($this->product->params->toArray() as $name => $value)
		{
			if (trim($value) !== '' && $this->config->exists($name))
			{
				$this->config->set($name, $value);
				$ignoreNames[] = $name;
			}
		}

		if ($menu
			&& @$menu->query['option'] == 'com_easyshop'
			&& @$menu->query['view'] == 'productdetail'
			&& @$menu->query['id'] == $this->product->id
		)
		{
			$this->menuItem = $menu;

			foreach ($menu->getParams()->toArray() as $name => $value)
			{
				if (!in_array($name, $ignoreNames)
					&& trim($value) !== ''
					&& $this->config->exists($name)
				)
				{
					$this->config->set($name, $value);
				}
			}
		}

		if (true === $this->product->expireDate)
		{
			$app->enqueueMessage(Text::_('COM_EASYSHOP_WARNING_PRODUCT_NOT_ENABLED'), 'warning');
		}

		if ($this->config->get('product_detail_prepare', 0))
		{
			$this->product->description = HTMLHelper::_('content.prepare', $this->product->description, null, 'com_easyshop.product');
		}

		$app->triggerEvent('onProductAddonDisplay', [$this->product]);
		$this->event = easyshop(Event::class);
		$this->event->register('onProductBeforeDisplay', [$this->product]);
		$this->event->register('onProductAfterDisplayName', [$this->product]);
		$this->event->register('onProductAfterDisplaySummary', [$this->product]);
		$this->event->register('onProductAfterDisplayFields', [$this->product]);
		$this->event->register('onProductBeforeRenderTab', [$this->product]);
		$this->event->register('onProductAfterDisplay', [$this->product]);

		// @since 1.1.6 Apply Images Lazy Load
		if ($this->config->get('image_advance_mode', '0') && strpos($this->product->description, 'src=') !== false)
		{
			$rootUrl = Uri::root(true) . '/';
			$regex   = '#\ssrc="(?!/|[a-zA-Z0-9\-]+:|\#|\')([^"]*)"#m';

			$this->product->description = preg_replace($regex, ' src="' . $rootUrl . '$1"', $this->product->description);
			$this->product->description = preg_replace('/\<img(.*)src=\"([^\"]+)\"([^\>]*)\>/', '<img$1data-src="$2" uk-img$3>', $this->product->description);
		}

		if (!empty($this->product->badgeData))
		{
			if (empty($this->product->badgeData['badge_detail_position']))
			{
				unset($this->product->badgeData);
			}
			else
			{
				$this->product->badgeData['badge_position'] = $this->product->badgeData['badge_detail_position'];
			}
		}

		$this->_prepareDocument();
		$key  = 'com_easyshop.product_hits_' . $this->product->id;
		$time = $app->getUserState($key, null);

		if (null === $time || $time + 180 <= time()) // Re-update hits after 3 minutes
		{
			$app->setUserState($key, time());
			$hits  = (int) $this->product->hits + 1;
			$db    = easyshop('db');
			$query = $db->getQuery(true)
				->update($db->quoteName('#__easyshop_products'))
				->set($db->quoteName('hits') . ' = ' . $hits)
				->where($db->quoteName('id') . ' = ' . (int) $this->product->id);
			$db->setQuery($query)->execute();
			$this->product->hits = $hits;
		}

		parent::display($tpl);
	}

	protected function _prepareDocument()
	{
		$document = easyshop('doc');

		if ($document->getType() != 'html')
		{
			return;
		}

		$app      = easyshop('app');
		$pathway  = $app->getPathway();
		$domain   = Uri::getInstance()->toString(['scheme', 'host']);
		$title    = trim($this->product->metatitle);
		$metaDesc = trim($this->product->metadesc);
		$metaKey  = trim($this->product->metakey);

		if ($menu = $app->getMenu()->getActive())
		{
			$id     = isset($menu->query['id']) ? (int) $menu->query['id'] : 0;
			$option = isset($menu->query['option']) ? $menu->query['option'] : '';
			$view   = isset($menu->query['view']) ? $menu->query['view'] : '';

			if ($option !== 'com_easyshop' || $view === 'productlist')
			{
				$path = [
					[
						'title' => $this->product->name,
						'link'  => '',
					],
				];

				if ($view === 'productlist')
				{
					$category = $this->product->category;

					while ($category && $id != $category->id && $category->id > 1)
					{
						$path[]   = [
							'title' => $category->title,
							'link'  => EasyshopHelperRoute::getCategoryRoute($category->id),
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

			if ($this->menuItem)
			{
				$menuParams = $this->menuItem->getParams();

				if (empty($title) && $menuParams->get('page_title', ''))
				{
					$title = $menuParams->get('page_title');
				}

				if (empty($metaDesc) && $menuParams->get('menu-meta_description'))
				{
					$metaDesc = $menuParams->get('menu-meta_description');
				}

				if (empty($metaKey) && $menuParams->get('menu-meta_keywords'))
				{
					$metaKey = $menuParams->get('menu-meta_keywords');
				}
			}
		}

		if (empty($title))
		{
			$title = trim($this->product->name);
		}

		if (empty($metaDesc))
		{
			$metaDesc = trim(strip_tags(substr($this->product->summary, 0, 159)));
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
		$document->setDescription($metaDesc);
		$document->setMetadata('keywords', $metaKey);

		if (isset($this->product->images[0]->image))
		{
			$document->setMetadata('og:image', $domain . $this->product->images[0]->image, 'property');
		}

		$document->addHeadLink(htmlspecialchars($domain . $this->product->link), 'canonical');
		$document->setMetadata('og:url', $domain . $this->product->link, 'property');
		$document->setMetadata('og:title', $title, 'property');
		$document->setMetadata('og:type', 'product', 'property');
		$document->setMetadata('og:description', trim($metaDesc), 'property');

		if (!empty($this->product->robots))
		{
			$document->setMetadata('robots', trim($this->product->robots));
		}

		if ($app->get('MetaAuthor') == '1' && !empty($this->product->author))
		{
			$document->setMetaData('author', trim($this->product->author));
		}
	}
}
