<?php
/**
 
 
 
 
 
 */

namespace Umart\Classes;
defined('_JEXEC') or die;

use UmartTableZone;
use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;

class Zone
{
	public function loadByParents($parentIds = [])
	{
		static $zones = [];
		$key     = md5(serialize($parentIds));
		$display = (int) plg_sytem_umart_main('config', 'zone_display', 1);

		if (!isset($zones[$key]) && !empty($parentIds))
		{
			$name  = $display === 1 ? 'CONCAT(a.name, " (", a.name_english, ")")' : ($display === 2 ? 'a.name' : 'a.name_english');
			$pks   = ArrayHelper::toInteger((array) $parentIds);
			$db    = plg_sytem_umart_main('db');
			$query = $db->getQuery(true)
				->select('a.id AS value, ' . $name . ' AS text')
				->from($db->quoteName('#__umart_zones', 'a'))
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

		if (!$table instanceof UmartTableZone)
		{
			Table::addIncludePath(UMART_COMPONENT_ADMINISTRATOR . '/tables');
			$table = Table::getInstance('Zone', 'UmartTable');
		}

		return $table->load($pk) ? $table : false;
	}
}
