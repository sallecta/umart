<?php
/**
 *  @package     com_easyshop
 *  @version     1.0.5
 *  @Author      JoomTech Team
* @copyright   Copyright (C) 2015 - 2019 www.joomtech.net All Rights Reserved.
 *  @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;
use ES\Model\ListModel;

class EasyshopModelCustomfields extends ListModel
{
	protected $searchField = 'name';

	public function __construct(array $config)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = [
				'id', 'a.id',
				'name', 'a.name',
				'alias', 'a.alias',
				'state', 'a.state',
				'ordering', 'a.ordering',
				'group_title', 'published',
				'checkout_field', 'a.checkout_field',
				'group_id'
			];
		}
		parent::__construct($config);
	}

	protected function populateState($ordering = null, $direction = null)
	{
		$reflector = easyshop('app')->input->getCmd('reflector', 'com_easyshop');
		$this->setState('filter.reflector', $reflector);

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$group = $this->getUserStateFromRequest($this->context . '.filter.' . $reflector . '.group_id', 'filter_group_id', '');
		$this->setState('filter.group_id', $group);

		$vendorId = $this->getUserStateFromRequest($this->context . '.filter.vendor_id', 'filter_vendor_id', '');
		$this->setState('filter.vendor_id', $vendorId);

		parent::populateState('a.ordering', 'asc');
	}

	public function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.group_id');
		$id .= ':' . $this->getState('filter.reflector');
		$id .= ':' . $this->getState('filter.vendor_id');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = parent::getListQuery();

		$query->select(
			$this->getState('list.select', 'a.id, a.reflector, a.state, a.name, a.alias, a.ordering, a.checked_out, '
				. 'a.group_id, a.checked_out_time, a.created_date, a.created_by, a.protected, a.checkout_field, a.default_value'
			)
		);

		$query->from($db->quoteName('#__easyshop_customfields', 'a'));
		$query->select('u.name AS author')
			->leftJoin($db->quoteName('#__users', 'u') . ' ON u.id = a.created_by');
		$query->select('uu.name AS editor')
			->leftJoin($db->quoteName('#__users', 'uu') . ' ON uu.id = a.checked_out');

		$query->select('g.title AS group_title')
			->leftJoin($db->quoteName('#__categories', 'g') . ' ON g.id = a.group_id');

		$reflector = $this->getState('filter.reflector');

		$query->where('a.reflector = ' . $db->quote($reflector));

		$group = $this->getState('filter.group_id');

		if (is_numeric($group))
		{
			$query->where('a.group_id = ' . (int) $group);
		}

		$vendorId = $this->getState('filter.vendor_id');

		if (is_numeric($vendorId))
		{
			$query->where('a.vendor_id = ' . (int) $vendorId);
		}

		$this->standardFilter($db, $query);

		return $query;
	}

	public function getFilterForm($data = [], $loadData = true)
	{
		/** @var $form JForm */
		$form      = parent::getFilterForm($data, $loadData);
		$reflector = $this->getState('filter.reflector');

		if (strpos($reflector, 'com_easyshop.product') !== 0)
		{
			$form->removeField('group_id', 'filter');
			$this->setState('filter.group_id', '');
		}
		else
		{
			$form->setFieldAttribute('group_id', 'extension', $reflector, 'filter');
		}

		return $form;
	}

}
