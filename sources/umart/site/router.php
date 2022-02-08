<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\Translator;
use ES\Classes\User;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\RouterBase;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Plugin\PluginHelper;

class EasyshopRouter extends RouterBase
{
	public $app;
	public $menu;

	public function __construct()
	{
		$app  = CMSFactory::getApplication('site');
		$menu = $app->getMenu();
		parent::__construct($app, $menu);

		if (Multilanguage::isEnabled()
			&& ComponentHelper::getParams('com_languages')->get('site', 'en-GB') !== CMSFactory::getLanguage()->getTag()
		)
		{
			$this->isMultiLanguage = true;
		}
	}

	public function build(&$query)
	{
		$segments = [];

		if (isset($query['limitstart']) && $query['limitstart'] == 0)
		{
			unset($query['limitstart']);
		}

		if (empty($query['Itemid']))
		{
			$menuItem      = $this->menu->getActive();
			$menuItemGiven = false;
		}
		else
		{
			$menuItem      = $this->menu->getItem($query['Itemid']);
			$menuItemGiven = true;
		}

		if ($menuItemGiven
			&& isset($menuItem)
			&& $menuItem->component != 'com_easyshop'
		)
		{
			$menuItemGiven = false;
			unset($query['Itemid']);
		}

		if (isset($query['view']))
		{
			$view = $query['view'];
		}
		else
		{
			return $segments;
		}

		if (isset($menuItem))
		{
			$qView   = @$menuItem->query['view'];
			$qLayout = @$menuItem->query['layout'];

			if ($qView == $view
				&& isset($query['id'])
				&& isset($menuItem->query['id'])
				&& $menuItem->query['id'] == $query['id'])
			{
				if (isset($query['category_id']))
				{
					unset($query['category_id']);
				}

				unset($query['view'], $query['id']);

				if (isset($query['layout'])
					&& ($query['layout'] == 'default' || $query['layout'] == $menuItem->query['layout']))
				{
					unset($query['layout']);
				}

				return $segments;
			}

			$views = [
				'cart',
				'search',
				'customer',
				'track',
			];

			if ($qView == $view && in_array($qView, $views))
			{
				unset($query['view']);

				if ($qView == 'search'
					&& $qLayout == 'tag'
					&& isset($query['tag'])
				)
				{
					$segments = [$query['tag']];

					unset($query['tag']);

					if (isset($query['layout']))
					{
						unset($query['layout']);
					}

					return $segments;
				}

				if (isset($query['layout']))
				{
					if ($qView == 'cart' && $query['layout'] != 'default')
					{
						$segments[] = $query['layout'];
					}

					unset($query['layout']);
				}

				return $segments;
			}
		}

		if ($view == 'productlist' || $view == 'productdetail')
		{
			if (!$menuItemGiven)
			{
				$segments[] = $view;
			}

			unset($query['view']);

			if (!isset($query['id']))
			{
				return $segments;
			}

			$db = easyshop('db');

			if ($view == 'productdetail')
			{
				if (!empty($query['category_id']))
				{
					$categoryId = $query['category_id'];
					unset($query['category_id']);

					// Make sure we have the id and the alias
					if (strpos($query['id'], ':') === false)
					{
						$dbQuery = $db->getQuery(true)
							->select('a.id, a.alias')
							->from($db->quoteName('#__easyshop_products', 'a'))
							->where('a.id = ' . (int) $query['id']);
						$db->setQuery($dbQuery);

						if ($product = $db->loadObject())
						{
							Translator::translateObject($product, 'easyshop_products', $product->id);
							$query['id'] = $product->alias;
						}
					}
				}
				else
				{
					return $segments;
				}
			}
			else
			{
				$categoryId = $query['id'];
			}

			if ($menuItemGiven && isset($menuItem->query['id'])
			)
			{
				$mCatId = (int) $menuItem->query['id'];
			}
			else
			{
				$mCatId = 0;
			}

			$nodes    = Categories::getInstance('easyshop.product');
			$category = $nodes->get($categoryId);

			if (!$category)
			{
				return $segments;
			}

			$path  = array_reverse($category->getPath());
			$array = [];

			foreach ($path as $id)
			{
				list($nodeId, $nodeAlias) = explode(':', $id, 2);

				if ($nodeId == $mCatId)
				{
					break;
				}

				if ($node = $nodes->get($nodeId))
				{
					Translator::translateObject($node, 'categories', $nodeId);
					$array[] = $node->alias;
				}
			}

			$array    = array_reverse($array);
			$segments += $array;

			if ($view == 'productdetail')
			{
				$segments[] = $query['id'];
			}

			unset($query['id']);
		}

		if (isset($query['layout']))
		{
			if ($menuItemGiven && isset($menuItem->query['layout']))
			{
				if ($query['layout'] == $menuItem->query['layout'])
				{
					unset($query['layout']);
				}
			}
			else
			{
				if ($query['layout'] == 'default')
				{
					unset($query['layout']);
				}
			}
		}

		$total = count($segments);

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = str_replace(':', '-', $segments[$i]);
		}

		return $segments;
	}

	public function parse(&$segments)
	{
		// @since 1.1.5
		PluginHelper::importPlugin('easyshop');
		$total = count($segments);
		$item  = $this->menu->getActive();
		$vars  = [];

		// @since 1.3.1
		if ($total > 1)
		{
			preg_match('/^pm-callback-tok([0-9a-zA-Z]{40})$/', $segments[0], $matches);

			if (!empty($matches[1]))
			{
				$vars['task']          = 'payment.callBack';
				$vars['token']         = $matches[1];
				$vars['callBackParam'] = $segments[1];

				return $vars;
			}
		}

		if (isset($item)
			&& @$item->query['view'] == 'search'
			&& @$item->query['layout'] == 'tag'
		)
		{
			return [
				'view' => 'search',
				'task' => 'search',
				'tag'  => $segments[0],
			];
		}

		if (!isset($item)
			|| @$item->query['option'] != 'com_easyshop'
		)
		{
			if (isset($segments[0]))
			{
				array_shift($segments);
				$total--;
			}

			return $this->parseVars($vars, $segments, $total, $item);
		}

		if ($total >= 1)
		{
			if ($total == 1)
			{
				// If segments[0] the same with cart layout alias then it's cart view
				$cartLayouts = [
					'login',
					'checkout',
					'confirm',
				];

				if (in_array($segments[0], $cartLayouts))
				{
					$vars['view']   = 'cart';
					$vars['layout'] = $segments[0];
					$segments       = [];

					return $vars;
				}
			}

			return $this->parseVars($vars, $segments, $total, $item);
		}
	}

	protected function parseVars(&$vars, &$segments, $total, $item = null)
	{
		$isMultiLanguage = Translator::isTranslatable(true);

		if ($total < 1)
		{
			return $vars;
		}

		$db       = easyshop('db');
		$langCode = CMSFactory::getLanguage()->getTag();
		$rootPath = '';

		if (isset($item)
			&& isset($item->query['option'])
			&& $item->query['option'] == 'com_easyshop'
			&& isset($item->query['view'])
			&& $item->query['view'] == 'productlist'
			&& isset($item->query['id'])
		)
		{
			$query = $db->getQuery(true)
				->select('a.id')
				->from($db->quoteName('#__categories', 'a'))
				->where('a.published = 1 AND a.id = ' . (int) $item->query['id']);
			$db->setQuery($query);

			if ($cid = $db->loadResult())
			{
				$category = Categories::getInstance('easyshop.product')->get($cid);
				$rootPath = $category->path . '/';
			}
		}

		$path  = $rootPath . implode('/', $segments);
		$alias = $segments[$total - 1];
		$query = $db->getQuery(true)
			->select('a.id')
			->from($db->quoteName('#__categories', 'a'))
			->where('a.extension = ' . $db->quote('com_easyshop.product'))
			->where('a.published = 1');

		if ($isMultiLanguage)
		{
			$query->join('LEFT', $db->quoteName('#__easyshop_translations', 'a2') . ' ON a2.translationId = CONCAT_WS(' . $db->quote('.') . ', ' . $db->quote($langCode) . ', ' . $db->quote('categories') . ', a.id, ' . $db->quote('path') . ')')
				->where('(a.path = ' . $db->quote($path) . ' OR a2.translatedValue = ' . $db->quote($path) . ')');
		}
		else
		{
			$query->where('a.path = ' . $db->quote($path));
		}

		$db->setQuery($query);

		if (ES_DETECT_JVERSION == 4)
		{
			foreach ($segments as $k => $v)
			{
				unset($segments[$k]);
			}
		}

		if ($id = $db->loadResult())
		{
			$vars['view'] = 'productlist';
			$vars['id']   = (int) $id;

			return $vars;
		}

		$categoryPath = explode('/', $path);
		array_pop($categoryPath);
		$categoryPath = implode('/', $categoryPath);

		if (empty($categoryPath))
		{
			// Oops. Product detail URL must have parent category path
			return $vars;
		}

		$query = $db->getQuery(true)
			->select('a.id, a.category_id')
			->from($db->quoteName('#__easyshop_products', 'a'))
			->join('INNER', $db->quoteName('#__categories', 'a2') . ' ON a2.id = a.category_id AND a2.extension = ' . $db->quote('com_easyshop.product'))
			->where('a2.published = 1');

		if (!easyshop(User::class)->core('admin'))
		{
			$query->where('a.state = 1');
		}

		if ($isMultiLanguage)
		{
			$query->join('LEFT', $db->quoteName('#__easyshop_translations', 'a3') . ' ON a3.translationId = CONCAT_WS(' . $db->quote('.') . ', ' . $db->quote($langCode) . ', ' . $db->quote('easyshop_products') . ', a.id, ' . $db->quote('alias') . ')')
				->where('(a.alias = ' . $db->quote($alias) . ' OR a3.translatedValue = ' . $db->quote($alias) . ')');
		}
		else
		{
			$query->where('a.alias = ' . $db->quote($alias));
		}

		$db->setQuery($query);

		if ($obj = $db->loadObject())
		{
			$category = Categories::getInstance('easyshop.product')->get($obj->category_id);

			if ($category->path === $categoryPath)
			{
				$vars['view'] = 'productdetail';
				$vars['id']   = (int) $obj->id;
			}
		}

		return $vars;
	}
}

function easyshopBuildRoute(&$query)
{
	$router = new EasyshopRouter;

	return $router->build($query);
}

function easyshopParseRoute($segments)
{
	$router = new EasyshopRouter;

	return $router->parse($segments);
}
