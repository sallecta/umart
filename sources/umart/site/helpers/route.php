<?php

/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Multilanguage;

abstract class EasyshopHelperRoute
{
	protected static $lookup = [];

	public static function getProductRoute($id, $categoryId = 0, $language = 0)
	{
		if (0 === $language)
		{
			$language = easyshop('app')->getLanguage()->getTag();
		}

		$needles = ['productdetail' => [(int) $id]];
		$link    = 'index.php?option=com_easyshop&view=productdetail&id=' . $id;

		if (!$categoryId)
		{
			$db    = easyshop('db');
			$query = $db->getQuery(true)
				->select('a.category_id')
				->from($db->quoteName('#__easyshop_products', 'a'))
				->where('a.id = ' . (int) $id);
			$db->setQuery($query);
			$categoryId = (int) $db->loadResult();
		}

		if ((int) $categoryId > 0)
		{
			$nodes    = Categories::getInstance('easyshop.product');
			$category = $nodes->get($categoryId);

			if ($category)
			{
				$needles['productlist'] = array_reverse($category->getPath());
				$link                   .= '&category_id=' . $categoryId;
			}
		}

		if ($language && $language != '*' && Multilanguage::isEnabled())
		{
			$link                .= '&lang=' . $language;
			$needles['language'] = $language;
		}

		if ($item = static::findItem($needles))
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}

	protected static function findItem($needles = [])
	{
		$menus    = easyshop('app')->getMenu('site');
		$language = isset($needles['language']) ? $needles['language'] : '*';

		if (!isset(static::$lookup[$language]))
		{
			static::$lookup[$language] = [];
			$items                     = self::findItems($language);

			foreach ($items as $item)
			{
				if (isset($item->query) && isset($item->query['view']))
				{
					$view = $item->query['view'];

					if (!isset(static::$lookup[$language][$view]))
					{
						static::$lookup[$language][$view] = [];
					}

					if (isset($item->query['id']))
					{
						if (!isset(static::$lookup[$language][$view][$item->query['id']]) || $item->language != '*')
						{
							static::$lookup[$language][$view][$item->query['id']] = $item->id;
						}
					}
					else
					{
						static::$lookup[$language][$view][0] = $item->id;
					}
				}
			}
		}

		if (count($needles))
		{
			foreach ($needles as $view => $ids)
			{
				if (isset(static::$lookup[$language][$view]))
				{
					foreach ($ids as $id)
					{
						if (isset(static::$lookup[$language][$view][(int) $id]))
						{
							return static::$lookup[$language][$view][(int) $id];
						}
					}
				}
			}
		}

		$active = $menus->getActive();

		if ($active
			&& $active->component == 'com_easyshop'
			&& ($active->language == '*' || !Multilanguage::isEnabled())
		)
		{
			return $active->id;
		}

		$default = $menus->getDefault($language);

		return !empty($default->id) ? $default->id : null;
	}

	public static function findItems($language = 0)
	{
		if (0 === $language)
		{
			$language = easyshop('app')->getLanguage()->getTag();
		}

		static $items = [];

		if (!isset($items[$language]))
		{
			$menus      = easyshop('app')->getMenu('site');
			$component  = ComponentHelper::getComponent('com_easyshop');
			$attributes = ['component_id'];
			$values     = [$component->id];

			if ($language != '*')
			{
				$attributes[] = 'language';
				$values[]     = [$language, '*'];
			}

			$items[$language] = $menus->getItems($attributes, $values);
		}

		return $items[$language];
	}

	public static function getCategoryRoute($category, $language = 0)
	{
		if (0 === $language)
		{
			$language = easyshop('app')->getLanguage()->getTag();
		}

		if ($category instanceof CategoryNode)
		{
			$id = $category->id;
		}
		else
		{
			$id       = (int) $category;
			$nodes    = Categories::getInstance('easyshop.product');
			$category = $nodes->get($id);
		}

		if ($id < 1)
		{
			$link = '';
		}
		else
		{
			$link    = 'index.php?option=com_easyshop&view=productlist&id=' . $id;
			$needles = [
				'productlist' => [$id],
			];

			if ($language && $language != '*' && Multilanguage::isEnabled())
			{
				$link                .= '&lang=' . $language;
				$needles['language'] = $language;
			}

			if ($category)
			{
				$catids                 = array_reverse($category->getPath());
				$needles['productlist'] = $catids;
			}

			if ($item = static::findItem($needles))
			{
				$link .= '&Itemid=' . $item;
			}
		}

		return $link;
	}

	public static function getCartRoute($layout = 'default', $language = 0)
	{
		$needles = ['cart' => [0]];
		$link    = 'index.php?option=com_easyshop&view=cart';

		if ($layout != 'default')
		{
			$link .= '&layout=' . $layout;
		}

		if ($language && $language != '*' && Multilanguage::isEnabled())
		{
			$link                .= '&lang=' . $language;
			$needles['language'] = $language;
		}

		if ($item = static::findItem($needles))
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}

	public static function getCategory($id)
	{
		static $categories = [];

		if (!isset($categories[$id]))
		{
			$nodes    = Categories::getInstance('easyshop.product');
			$category = $nodes->get($id);

			if (!$category)
			{
				$nodes    = Categories::getInstance('easyshop.brand');
				$category = $nodes->get($id);
			}

			$categories[$id] = $category;
		}

		return $categories[$id];
	}

	public static function getTagRoute($tag, $language = 0)
	{
		if (0 === $language)
		{
			$language = easyshop('app')->getLanguage()->getTag();
		}

		static $tagItems = [];

		if (!isset($tagItems[$language]))
		{
			$items               = self::findItems($language);
			$tagItems[$language] = [0, []];

			foreach ($items as $item)
			{
				if (@$item->query['view'] == 'search'
					&& @$item->query['layout'] == 'tag'
				)
				{
					$tagItems[$language][0] = $item->id;
					break;
				}
			}
		}

		if (!isset($tagItems[$language][1][$tag]))
		{
			$Itemid = $tagItems[$language][0];

			if ($Itemid)
			{
				$link = 'index.php?option=com_easyshop&view=search&layout=tag&tag=' . $tag;

				if ($language
					&& $language != '*'
					&& Multilanguage::isEnabled()
				)
				{
					$link .= '&lang=' . $language;
				}

				$link .= '&Itemid=' . $Itemid;

				$tagItems[$language][1][$tag] = $link;
			}
			else
			{
				$tagItems[$language][1][$tag] = self::getSearchRoute(['task' => 'search', 'tag' => $tag], $language);
			}

		}

		return $tagItems[$language][1][$tag];
	}

	public static function getSearchRoute($extraQuery = [], $language = 0)
	{
		static $searchItems = [];

		if (0 === $language)
		{
			$language = easyshop('app')->getLanguage()->getTag();
		}

		if (!isset($searchItems[$language]))
		{
			$searchItems[$language] = 0;

			foreach (self::findItems($language) as $item)
			{
				if (@$item->query['view'] == 'search'
					&& @$item->query['layout'] != 'tag'
				)
				{
					$searchItems[$language] = $item->id;
					break;
				}
			}
		}

		$link = 'index.php?option=com_easyshop&view=search';

		if (count($extraQuery))
		{
			$link .= '&' . http_build_query($extraQuery);
		}

		if ($searchItems[$language])
		{
			$link .= '&Itemid=' . $searchItems[$language];
		}

		return $link;
	}

	public static function getCustomerRoute($language = 0)
	{
		return self::getCustomViewRoute('customer', $language);
	}

	public static function getCustomViewRoute($view, $language = 0)
	{
		if (0 === $language)
		{
			$language = easyshop('app')->getLanguage()->getTag();
		}

		static $customViewItems = [];

		if (!isset($customViewItems[$view]))
		{
			$customViewItems[$view] = [];
			$items                  = self::findItems($language);

			foreach ($items as $item)
			{
				if (@$item->query['view'] == $view)
				{
					$customViewItems[$view][$item->language] = $item->id;
				}
			}
		}

		$link = 'index.php?option=com_easyshop&view=' . $view;

		if (isset($customViewItems[$view][$language]))
		{
			$link = 'index.php?Itemid=' . $customViewItems[$view][$language];
		}
		elseif (isset($customViewItems[$view]['*']))
		{
			$link = 'index.php?Itemid=' . $customViewItems[$view]['*'];
		}

		return $link;
	}
}
