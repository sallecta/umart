<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ES\Classes;
defined('_JEXEC') or die;

use EasyshopTableZone;
use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;

class Zone
{
	public function loadByParents($parentIds = [])
	{
		static $zones = [];
		$key     = md5(serialize($parentIds));
		$display = (int) easyshop('config', 'zone_display', 1);

		if (!isset($zones[$key]) && !empty($parentIds))
		{
			$name  = $display === 1 ? 'CONCAT(a.name, " (", a.name_english, ")")' : ($display === 2 ? 'a.name' : 'a.name_english');
			$pks   = ArrayHelper::toInteger((array) $parentIds);
			$db    = easyshop('db');
			$query = $db->getQuery(true)
				->select('a.id AS value, ' . $name . ' AS text')
				->from($db->quoteName('#__easyshop_zones', 'a'))
				->where('a.state = 1 AND a.parent_id IN (' . implode(',', $pks) . ')')
				->order('a.name_english, a.name ASC');
			$db->setQuery($query);
			$zones[$key] = $db->loadObjectList();
		}

		return $zones[$key];
	}

	public function load($pk = 0)
	{
		static $table = null;

		if (!$table instanceof EasyshopTableZone)
		{
			Table::addIncludePath(ES_COMPONENT_ADMINISTRATOR . '/tables');
			$table = Table::getInstance('Zone', 'EasyshopTable');
		}

		return $table->load($pk) ? $table : false;
	}
}
