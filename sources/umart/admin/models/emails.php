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

class EasyshopModelEmails extends ListModel
{
	protected $searchField = 'name';
	protected $ordering = 'a.ordering';
	protected $direction = 'asc';

	public function __construct(array $config)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = [
				'id', 'a.id',
				'name', 'a.name',
				'state', 'a.state',
				'ordering', 'a.ordering',
				'send_on', 'a.send_on',
				'language', 'a.language',
				'published',
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

		$sendOn = $this->getUserStateFromRequest($this->context . '.filter.send_on', 'filter_send_on', '');
		$this->setState('filter.send_on', $sendOn);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		$vendorId = $this->getUserStateFromRequest($this->context . '.filter.vendor_id', 'filter_vendor_id', '');
		$this->setState('filter.vendor_id', $vendorId);

		parent::populateState('a.ordering', 'asc');
	}

	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . serialize($this->getState('filter.send_on'));
		$id .= ':' . serialize($this->getState('filter.language'));
		$id .= ':' . $this->getState('filter.vendor_id');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = parent::getListQuery();
		$query->select(
			$this->getState('list.select',
				'a.id, a.name, a.state, a.send_from_name, a.send_from_email, a.checked_out,
				 a.send_subject, a.send_body, a.checked_out_time, a.created_date, a.created_by,
				 a.ordering, a.send_on, a.send_to_emails, a.order_status, a.order_payment, a.vendor_id'
			)
		);
		$query->from($db->quoteName('#__easyshop_emails', 'a'));
		$query->select('u.name AS author')
			->leftJoin($db->quoteName('#__users', 'u') . ' ON u.id = a.created_by');
		$query->select('uu.name AS editor')
			->leftJoin($db->quoteName('#__users', 'uu') . ' ON uu.id = a.checked_out');

		$sendOn = $this->getState('filter.send_on');

		if (!empty($sendOn))
		{
			settype($sendOn, 'array');

			foreach ($sendOn as &$send)
			{
				$send = $db->quote($send);
			}

			$query->where('a.send_on IN (' . implode(',', $sendOn) . ')');
		}

		$language = $this->getState('filter.language');

		if ($language)
		{
			if (is_array($language))
			{
				foreach ($language as &$lang)
				{
					$lang = $db->quote($lang);
				}

				$query->where('a.language IN (' . implode(',', $language) . ')');
			}
			else
			{
				$query->where('a.language = ' . $db->quote($language));
			}
		}

		$vendorId = $this->getState('filter.vendor_id');

		if (is_numeric($vendorId))
		{
			$query->where('a.vendor_id = ' . (int) $vendorId);
		}

		$this->standardFilter($db, $query);

		return $query;
	}
}
