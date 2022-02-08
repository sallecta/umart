<?php
/**
 * @package     com_easyshop
 * @version     1.0.5
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use ES\Model\ListModel;

class EasyshopModelTaxes extends ListModel
{
	protected $searchField = 'name';

	public function __construct(array $config)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = [
				'id', 'a.id',
				'name', 'a.name',
				'rate', 'a.rate',
				'state', 'a.state',
				'ordering', 'a.ordering',
				'vendor_id', 'a.vendor_id',
				'published', 'vendor_name',
			];
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = null, $direction = null)
	{
		$value = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $value);

		$value = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $value);

		$value = $this->getUserStateFromRequest($this->context . '.filter.vendor_id', 'filter_vendor_id', '');
		$this->setState('filter.vendor_id', $value);

		parent::populateState('a.ordering', 'asc');
	}

	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.vendor_id');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = parent::getListQuery();
		$query->select(
			$this->getState('list.select',
				'a.id, a.name, a.rate,  a.state, a.ordering, a.vendor_id, a.checked_out, a
				.checked_out_time, a.created_date, a.created_by, a.vendor_id'
			)
		);
		$query->from($db->quoteName('#__easyshop_taxes', 'a'));
		$query->select('u.name AS author')
			->leftJoin($db->quoteName('#__users', 'u') . ' ON u.id = a.created_by');
		$query->select('uu.name AS editor')
			->leftJoin($db->quoteName('#__users', 'uu') . ' ON uu.id = a.checked_out');

		$query->select('uuu.name AS vendor_name')
			->leftJoin($db->quoteName('#__easyshop_users', 'a2') . ' ON a2.id = a.vendor_id')
			->leftJoin($db->quoteName('#__users', 'uuu') . ' ON uuu.id = a2.user_id');
		$this->standardFilter($db, $query);

		$vendorId = $this->getState('filter.vendor_id');

		if (is_numeric($vendorId))
		{
			$query->where('a.vendor_id = ' . (int) $vendorId);
		}

		return $query;
	}
}
