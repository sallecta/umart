<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use ES\Model\ListModel;

class EasyshopModelMethods extends ListModel
{
	protected $searchField = 'name';
	protected $key = 'id';

	public function __construct(array $config)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = [
				'id', 'a.id',
				'name', 'a.name',
				'state', 'a.state',
				'ordering', 'a.ordering',
				'is_default', 'a.is_default',
				'language', 'a.language',
				'e.folder',
			];
		}
		parent::__construct($config);
	}

	protected function populateState($ordering = null, $direction = null)
	{
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$group = $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type', '');
		$this->setState('filter.type', $group);

		$vendorId = $this->getUserStateFromRequest($this->context . '.filter.vendor_id', 'filter_vendor_id', '');
		$this->setState('filter.vendor_id', $vendorId);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		parent::populateState('a.ordering', 'asc');
	}

	public function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.type');
		$id .= ':' . $this->getState('filter.vendor_id');
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = parent::getListQuery();
		$query->select(
			$this->getState('list.select', 'a.id, a.name, a.state, a.created_date, a.created_by, a.checked_out, a.checked_out_time, a.plugin_id AS method_id, a.ordering, a.is_default, a.vendor_id, a.language')
		);
		$query->from($db->quoteName('#__easyshop_methods', 'a'));
		$query->select('uu.name AS editor, l.title AS language_title, l.image AS language_image')
			->leftJoin($db->quoteName('#__users', 'uu') . ' ON uu.id = a.checked_out')
			->leftJoin($db->quoteName('#__languages', 'l') . ' ON l.lang_code = a.language');
		$group         = 'easyshop' . $this->getState('filter.type');
		$easyshopGroup = ['easyshopshipping', 'easyshoppayment'];
		$query
			->select('e.folder')
			->leftJoin($db->quoteName('#__extensions', 'e') . ' ON e.extension_id = a.plugin_id')
			->where('e.type = ' . $db->quote('plugin'));

		if (empty($group) || !in_array($group, $easyshopGroup))
		{
			$easyshopGroup = array_map(function ($g) {
				return ES\easyshop('db')->q($g);
			}, $easyshopGroup);
			$query->where('e.folder IN (' . join(',', $easyshopGroup) . ')');
		}
		else
		{
			$query->where('e.folder = ' . $db->quote($group));
		}

		$vendorId = $this->getState('filter.vendor_id');

		if (is_numeric($vendorId))
		{
			$query->where('a.vendor_id = ' . (int) $vendorId);
		}

		if ($language = $this->getState('filter.language'))
		{
			$query->where('a.language = ' . $db->quote($language));
		}

		$this->standardFilter($db, $query);

		return $query;
	}

	public function getMethods()
	{
		static $methods = null;

		if (is_array($methods))
		{
			return $methods;
		}

		$user   = JFactory::getUser();
		$levels = implode(',', $user->getAuthorisedViewLevels());
		$db     = $this->getDbo();
		$group  = 'easyshop' . $this->getState('filter.type');

		if ($group && in_array($group, ['easyshopshipping', 'easyshoppayment']))
		{
			$group = $db->quote($group);
		}
		else
		{
			$group = $db->quote('easyshopshipping') . ',' . $db->quote('easyshoppayment');
		}

		$query = $db->getQuery(true)
			->select('a.extension_id AS method_id, a.folder AS type, a.element AS name, a.name AS origin_name')
			->from($db->quoteName('#__extensions', 'a'))
			->where('a.enabled = 1')
			->where('a.type =' . $db->quote('plugin'))
			->where('a.folder IN (' . $group . ')')
			->where('a.state IN (0,1)')
			->where('a.access IN (' . $levels . ')')
			->order('a.ordering');
		$db->setQuery($query);

		if ($plugins = $db->loadObjectList('method_id'))
		{
			$language = JFactory::getLanguage();

			foreach ($plugins as &$plugin)
			{
				$path = JPATH_PLUGINS . '/' . $plugin->type . '/' . $plugin->name;
				$language->load('plg_' . $plugin->type . '_' . $plugin->name, $path);
			}
		}

		settype($plugins, 'array');

		return $plugins;
	}

}
