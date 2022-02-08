<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Helper\ModuleHelper;

JLoader::register('EasyshopHelperRoute', ES_COMPONENT_SITE . '/helpers/route.php');
HTMLHelper::addIncludePath(ES_COMPONENT_ADMINISTRATOR . '/helpers/html');

class ModEasyshopCategoriesHelper
{
	public static function getActiveId()
	{
		static $active = null;

		if (null === $active)
		{
			$active = 0;
			$input  = easyshop('app')->input;

			if ($input->get('option') == 'com_easyshop'
				&& $input->get('view') == 'productlist'
				&& ($id = (int) $input->getUint('id', 0))
			)
			{
				$active = $id;
			}
		}

		return $active;

	}

	public static function loadChildren($node, $activeId = 0, $countAll = [], $source = 'category', $showIcon = true)
	{
		$buffer = '';

		foreach ($node->getChildren() as $child)
		{
			$title = $child->title;

			if ($showIcon)
			{
				$nodeParams = new Registry((string) $child->params);

				if ($icon = $nodeParams->get('icon'))
				{
					$title = HTMLHelper::_('easyshop.icon', $icon) . ' ' . $title;
				}
			}

			$buffer .= '<li class="' . ($activeId == $child->id ? 'uk-active' : '') . '">'
				. '<a href="' . self::getLink($child, $source) . '">' . $title
				. (isset($countAll[$child->id]) ? '<span class="es-product-count uk-text-meta"> (' . $countAll[$child->id] . ')</span>' : '') . '</a>';

			if ($child->hasChildren())
			{
				$buffer .= '<ul>' . self::loadChildren($child, $activeId, $countAll, $source) . '</ul>';
			}

			$buffer .= '</li>';
		}

		return $buffer;
	}

	public static function getCountAll($source = 'category')
	{
		static $countAll = [];

		if (!isset($countAll[$source]))
		{
			$sourceId = $source == 'category' ? 'category_id' : 'brand_id';
			$db       = easyshop('db');
			$query    = $db->getQuery(true)
				->select('DISTINCT a.' . $sourceId . ' AS source_id, COUNT(a.id) AS count')
				->from($db->quoteName('#__easyshop_products', 'a'))
				->where('a.state = 1')
				->group('source_id');
			$db->setQuery($query);
			$countAll[$source] = [];

			if ($rows = $db->loadObjectList())
			{
				foreach ($rows as $row)
				{
					$countAll[$source][$row->source_id] = (int) $row->count;
				}
			}
		}

		return $countAll[$source];
	}

	public static function getLink(JCategoryNode $node, $source = 'category')
	{
		if ($source == 'category')
		{
			return Route::_(EasyshopHelperRoute::getCategoryRoute($node, $node->language), false);
		}

		return Route::_(EasyshopHelperRoute::getSearchRoute(['task' => 'search', 'brand' => $node->id], $node->language), false);
	}

	public static function loadCardLayout(JCategoryNode $node, $params)
	{
		if ($node->params instanceof Registry)
		{
			$nodeParams = $node->params;
		}
		else
		{
			$nodeParams = new Registry((string) $node->params);
		}

		static $countAll = null;

		if (null === $countAll)
		{
			$countAll = $params->get('count_products', 1) ? self::getCountAll($params->get('source', 'category')) : [];
		}

		require ModuleHelper::getLayoutPath('mod_easyshop_categories', 'card_default');

		if ($params->get('includeChildren', 0))
		{
			if ($node->hasChildren())
			{
				foreach ($node->getChildren() as $childNode)
				{
					self::loadCardLayout($childNode, $params);
				}
			}
		}
	}
}
