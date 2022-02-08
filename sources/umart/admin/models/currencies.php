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

class EasyshopModelCurrencies extends ListModel
{
	protected $searchField = ['name', 'code', 'symbol'];

	public function __construct(array $config)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = [
				'id', 'a.id',
				'symbol', 'a.symbol',
				'code', 'a.code',
				'format', 'a.format',
				'name', 'a.name',
				'state', 'a.state',
				'rate', 'a.rate',
				'ordering', 'a.ordering',
				'is_default', 'a.is_default',
				'published'
			];
		}

		parent::__construct($config);
	}

	public function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');

		return parent::getStoreId($id);
	}

	protected function populateState($ordering = 'a.state', $direction = 'desc')
	{
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		parent::populateState($ordering, $direction);
	}

	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = parent::getListQuery();
		$query->select(
			$this->getState('list.select',
				'a.id, a.symbol, a.code, a.format, a.name, a.state, a.ordering, a.rate, a.is_default,
				 a.checked_out, a.checked_out_time, a.created_date, a.created_by'
			)
		);
		$query->from($db->quoteName('#__easyshop_currencies', 'a'));
		$query->select('u.name AS author')
			->leftJoin($db->quoteName('#__users', 'u') . ' ON u.id = a.created_by');
		$query->select('uu.name AS editor')
			->leftJoin($db->quoteName('#__users', 'uu') . ' ON uu.id = a.checked_out');
		$this->standardFilter($db, $query);

		return $query;
	}

}
