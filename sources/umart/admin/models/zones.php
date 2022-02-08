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
use Joomla\Utilities\ArrayHelper;

class EasyshopModelZones extends ListModel
{
	protected $searchField = ['name', 'name_english', 'code_2', 'code_3'];

	public function __construct(array $config)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = [
				'id', 'a.id',
				'name', 'a.name',
				'name_english', 'a.name_english',
				'code_2', 'a.code_2',
				'code_3', 'a.code_3',
				'state', 'a.state',
				'type', 'a.type',
				'ordering', 'a.ordering',
				'published', 'country_id',
				'state_id',
			];
		}

		parent::__construct($config);
	}

	public function getFilterForm($data = [], $loadData = true)
	{
		if ($form = parent::getFilterForm($data, $loadData))
		{
			$zoneType = strtolower($this->getState('filter.type', 'country'));

			if (in_array($zoneType, ['state', 'subzone']))
			{
				if ($zoneType === 'state')
				{
					$form->removeField('state_id', 'filter');
				}
				else
				{
					$countryId = $this->getState('filter.country_id', '');
					$form->setFieldAttribute('state_id', 'parent_id', $countryId, 'filter');
				}
			}
			else
			{
				$form->removeField('country_id', 'filter');
				$form->removeField('state_id', 'filter');
			}
		}

		return $form;
	}

	protected function populateState($ordering = null, $direction = null)
	{
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$countryId = $this->getUserStateFromRequest($this->context . '.filter.country_id', 'filter_country_id', '');
		$this->setState('filter.country_id', $countryId);

		$stateId = $this->getUserStateFromRequest($this->context . '.filter.state_id', 'filter_state_id', '');
		$this->setState('filter.state_id', $stateId);

		$zoneType = $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type', 'country', 'word');

		if (empty($zoneType))
		{
			$zoneType = 'country';
			easyshop('app')->setUserState($this->context . '.filter.type', 'country');
		}

		$this->setState('filter.type', $zoneType);

		parent::populateState('a.name_english', 'asc');
	}

	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.country_id');
		$id .= ':' . $this->getState('filter.state_id');
		$id .= ':' . $this->getState('filter.type');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = parent::getListQuery();
		$query->select(
			$this->getState('list.select',
				'a.id, a.name, a.name_english, a.code_2, a.code_3, a.state, a.ordering, a.type, '
				. 'a.checked_out, a.checked_out_time, a.created_date, a.created_by, a.parent_id'
			)
		);
		$query->from($db->quoteName('#__easyshop_zones', 'a'));
		$query->select('u.name AS author')
			->leftJoin($db->quoteName('#__users', 'u') . ' ON u.id = a.created_by');
		$query->select('uu.name AS editor')
			->leftJoin($db->quoteName('#__users', 'uu') . ' ON uu.id = a.checked_out');
		$zoneType = strtolower($this->getState('filter.type', 'country'));
		$query->where('a.type = ' . $db->quote($zoneType));

		if (in_array($zoneType, ['state', 'subzone']))
		{
			if ($zoneType === 'state')
			{
				$countryId = $this->getState('filter.country_id');

				if (is_numeric($countryId) || (is_array($countryId) && !empty($countryId)))
				{
					if (is_numeric($countryId))
					{
						$countryId = [$countryId];
					}

					$query->where('a.parent_id IN (' . implode(',', ArrayHelper::toInteger($countryId)) . ')');
				}
			}
			else
			{
				$stateId = $this->getState('filter.state_id');

				if (is_numeric($stateId) || (is_array($stateId) && !empty($stateId)))
				{
					if (is_numeric($stateId))
					{
						$stateId = [$stateId];
					}

					$query->where('a.parent_id IN (' . implode(',', ArrayHelper::toInteger($stateId)) . ')');
				}
			}
		}

		$this->standardFilter($db, $query);

		return $query;
	}
}
