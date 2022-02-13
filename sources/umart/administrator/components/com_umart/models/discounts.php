<?php
/**
 
 
 
 
 
 */

defined('_JEXEC') or die;
use Umart\Model\ListModel;

class UmartModelDiscounts extends ListModel
{
	protected $searchField = 'name';

	public function __construct(array $config)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = [
				'id', 'a.id',
				'name', 'a.name',
				'code', 'a.code',
				'state', 'a.state',
				'type', 'a.type',
				'ordering', 'a.ordering',
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

		$type = $this->getUserStateFromRequest($this->context . '.filter.type', 'discount_type', '', 'string');
		$this->setState('filter.discount_type', $type);

		$vendorId = $this->getUserStateFromRequest($this->context . '.filter.vendor_id', 'filter_vendor_id', '');
		$this->setState('filter.vendor_id', $vendorId);

		parent::populateState('a.name', 'asc');
	}

	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.discount_type');
		$id .= ':' . $this->getState('filter.vendor_id');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = parent::getListQuery();
		$query->select(
			$this->getState('list.select',
				'a.id, a.name, a.coupon_code, a.state, a.ordering, a.type, a.checked_out,
				 a.checked_out_time, a.created_date, a.created_by, a.flat, a.percentage,
				 a.limit, a.start_date, a.end_date, a.vendor_id'
			)
		);
		$query->from($db->quoteName('#__umart_discounts', 'a'));
		$query->select('u.name AS author')
			->leftJoin($db->quoteName('#__users', 'u') . ' ON u.id = a.created_by');
		$query->select('uu.name AS editor')
			->leftJoin($db->quoteName('#__users', 'uu') . ' ON uu.id = a.checked_out');

		$type = $this->getState('filter.discount_type');

		if (is_numeric($type))
		{
			$query->where('a.type = ' . (int) $type);
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
