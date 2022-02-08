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

class EasyshopModelUsers extends ListModel
{
	protected $searchField = 'uuu.name';

	public function __construct(array $config)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = [
				'id', 'a.id',
				'user_id', 'a.user_id',
				'vendor', 'a.vendor',
				'state', 'a.state',
				'scores', 'a.score',
				'level', 'a.level',
				'created_date', 'a.created_date',
				'secret_key', 'a.secret_key',
				'name', 'u.name',
				'username', 'uuu.username',
				'email', 'uuu.name',
				'totalOrders',
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

		$vendor = $this->getUserStateFromRequest($this->context . '.filter.vendor', 'filter_vendor', '');
		$this->setState('filter.vendor', $vendor);

		parent::populateState('a.id', 'desc');
	}

	public function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.vendor');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = parent::getListQuery();
		$query->select(
			$this->getState('list.select',
				'a.id, a.user_id, a.state, a.vendor, a.checked_out, a.checked_out_time, '
				. 'a.created_date, a.created_by, a.avatar'
			)
		);
		$query->from($db->quoteName('#__easyshop_users', 'a'));
		$subQuery = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->quoteName('#__easyshop_orders', 'a2'))
			->where('a2.payment_status = 1 AND a2.user_id = a.id')
			->group('a2.user_id');
		$query->select('(' . $subQuery->__toString() . ') AS totalOrders');

		$query->select('u.name AS author')
			->leftJoin($db->quoteName('#__users', 'u') . ' ON u.id = a.created_by');

		$query->select('uu.name AS editor')
			->leftJoin($db->quoteName('#__users', 'uu') . ' ON uu.id = a.checked_out');

		$query->select('uuu.name, uuu.username, uuu.email, uuu.params')
			->leftJoin($db->quoteName('#__users', 'uuu') . ' ON uuu.id = a.user_id')
			->where('uuu.block = 0');
		$input  = easyshop('app')->input;
		$vendor = $input->get('filter_vendor', $this->getState('filter.vendor'));

		if (is_numeric($vendor))
		{
			$query->where('a.vendor = ' . (int) $vendor);
		}

		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('a.state <> -2');
		}

		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('(uuu.name LIKE ' . $search
					. ' OR uuu.username LIKE ' . $search
					. ' OR uuu.email LIKE ' . $search
					. ')');
			}
		}

		$ordering  = $this->getState('list.ordering', $this->ordering);
		$direction = $this->getState('list.direction', $this->direction);
		$query->order($db->escape($ordering) . ' ' . $db->escape($direction));
		easyshop('app')->triggerEvent('onEasyshopPrepareListQuery', [$this->context, $query]);

		return $query;
	}

	public function getFilterForm($data = [], $loadData = true)
	{
		if ($form = parent::getFilterForm($data, $loadData))
		{
			$filterVendor = easyshop('app')->input->get('filter_vendor', null);

			if (is_numeric($filterVendor))
			{
				$form->setValue('vendor', 'filter', $filterVendor);
			}
		}

		return $form;
	}

}
