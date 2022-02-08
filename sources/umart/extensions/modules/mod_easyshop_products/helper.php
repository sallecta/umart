<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use ES\Classes\Product;
use ES\Classes\Utility;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Language\Multilanguage;

defined('_JEXEC') or die;

JLoader::register('EasyshopHelperRoute', ES_COMPONENT_SITE . '/helpers/route.php');

class ModEasyshopProductsHelper
{
	protected static $categories = [];

	public static function getItems($params)
	{
		/** @var $productClass Product */
		$productClass = easyshop(Product::class);
		$mode         = $params->get('product_mode');
		$pks          = (array) $params->get($mode, []);
		$items        = [];

		if (empty($pks))
		{
			return $items;
		}

		if ($mode == 'products')
		{
			foreach ($pks as $pk)
			{
				$items[] = $productClass->getItem($pk, false, true);
			}
		}
		else
		{
			$db            = easyshop('db');
			$query         = $db->getQuery(true)
				->select('a.id')
				->from($db->quoteName('#__easyshop_products', 'a'))
				->leftJoin($db->quoteName('#__categories', 'a2') . ' ON a2.id = a.category_id');
			$subCategories = $params->get('product_in_sub_categories');
			$limit         = (int) $params->get('limit', 8);

			/** @var Utility $utility */
			$utility = easyshop(Utility::class);
			$utility->parseOrderingData(strtolower($params->get('sort_by')), $ordering, $direction);
			$query->order($ordering . ' ' . $direction);

			// @since 1.2.4
			$nullDate = $db->quote($db->getNullDate());
			$now      = $db->quote(CMSFactory::getDate()->toSql());
			$where    = 'a.state = 1 AND (CASE WHEN  a.sale_from_date <> ' . $nullDate . ' AND a.sale_to_date <> ' . $nullDate
				. ' THEN ' . $now . ' BETWEEN a.sale_from_date AND a.sale_to_date ELSE 1 END)';


			if (Multilanguage::isEnabled())
			{
				$where .= ' AND a.language IN (' . $db->quote('*') . ',' . $db->quote(CMSFactory::getLanguage()->getTag()) . ')';
			}

			$where .= ' AND a.display_in_search = 1 AND a.approved = 1';

			foreach ($pks as $pk)
			{
				$query->clear('where')
					->where($where);

				if ($subCategories)
				{
					$subQuery = $db->getQuery(true)
						->select('sub.id')
						->from($db->quoteName('#__categories', 'sub'))
						->innerJoin($db->quoteName('#__categories', 'this') . ' ON sub.lft > this.lft AND sub.rgt < this.rgt')
						->where('this.id = ' . (int) $pk);
					$query->where('(a.category_id = ' . (int) $pk . ' OR a.category_id IN (' . (string) $subQuery . '))');
				}
				else
				{
					$query->where('a.category_id = ' . (int) $pk);
				}

				$db->setQuery($query, 0, $limit);

				if ($ids = $db->loadColumn())
				{
					if ($category = Categories::getInstance('easyshop.product')->get($pk))
					{
						self::$categories[$pk] = $category;
					}
					else
					{
						continue;
					}

					foreach ($ids as $id)
					{
						$items[$pk][] = $productClass->getItem($id, false, true);
					}
				}
			}
		}

		return $items;
	}

	public static function getCategories()
	{
		return self::$categories;
	}
}
