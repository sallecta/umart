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
use Joomla\CMS\Factory as CMSFactory;

class EasyshopModelLogs extends ListModel
{
	protected $searchField = 'summary';
	protected $ordering = 'a.created_date';
	protected $direction = 'desc';

	public function __construct(array $config)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = [
				'id', 'a.id',
				'context', 'a.context',
				'string_key', 'a.string_key',
				'sprintf_data', 'a.sprintf_data',
				'juser_id', 'a.juser_id',
				'ip', 'a.ip',
				'created_date', 'a.created_date',
				'referer', 'a.referer',
				'author', 'customerId',
			];
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = 'a.created_date', $direction = 'desc')
	{
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$date = $this->getUserStateFromRequest($this->context . '.filter.created_date', 'filter_created_date');
		$this->setState('filter.created_date', $date);

		$context = $this->getUserStateFromRequest($this->context . '.filter.context', 'filter_context', '', 'CMD');
		$this->setState('filter.context', $context);

		$customerId = $this->getUserStateFromRequest($this->context . '.filter.customer_id', 'filter_customer_id', 0, 'uint');
		$this->setState('filter.customer_id', $customerId);

		parent::populateState($ordering, $direction);
	}

	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.created_date');
		$id .= ':' . $this->getState('filter.context');
		$id .= ':' . $this->getState('filter.customer_id');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = parent::getListQuery();
		$query->select(
			$this->getState('list.select',
				'a.id, a.context, a.string_key, a.sprintf_data, a.juser_id, a.ip, a.created_date, a.previous_data, '
				. 'a.modified_data, a.user_agent, u.name AS author, u.username, a.referer'
			)
		)
			->from($db->quoteName('#__easyshop_logs', 'a'))
			->join('LEFT', $db->quoteName('#__users', 'u') . ' ON u.id = a.juser_id');
		$context = trim($this->getState('filter.context', ''));

		if (!empty($context))
		{
			$query->where('a.context = ' . $db->quote($context));
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
				$query->where('(a.string_key LIKE ' . $search . ' OR u.name LIKE ' . $search . ' OR u.username LIKE ' . $search . ')');
			}
		}

		$date = $this->getState('filter.created_date');

		if (!empty($date))
		{
			$date = CMSFactory::getDate()->toSql();
			$query->where('DATE(a.created_date) = DATE(' . $db->quote($date) . ')');
		}

		$customerId = (int) $this->getState('filter.customer_id', 0);

		if ($customerId > 0)
		{
			$query->join('INNER', $db->quoteName('#__easyshop_users', 'customer') . ' ON customer.user_id = a.juser_id')
				->where('customer.id = ' . $customerId);
		}

		$ordering  = $this->getState('list.ordering', $this->ordering);
		$direction = $this->getState('list.direction', $this->direction);
		$query->order($db->escape($ordering) . ' ' . $db->escape($direction));
		easyshop('app')->triggerEvent('onEasyshopPrepareListQuery', [$this->context, $query]);

		return $query;
	}
}
