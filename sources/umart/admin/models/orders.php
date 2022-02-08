<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use ES\Classes\Order;
use ES\Classes\Utility;
use ES\Model\ListModel;

class EasyshopModelOrders extends ListModel
{
	protected $searchField = 'order_code';

	public function __construct(array $config)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = [
				'id', 'a.id',
				'order_code', 'a.order_code',
				'user_id', 'a.user_id',
				'user_email', 'a.user_email',
				'state', 'a.state',
				'created_date', 'a.created_date',
				'modified_date', 'a.modified_date',
				'shipping_id', 'a.shipping_id',
				'payment_id', 'a.payment_id',
				'discount_id', 'a.discount_id',
				'payment_txn_id', 'a.payment_txn_id',
				'payment_status', 'a.payment_status',
				'payment_date', 'a.payment_date',
				'total_paid', 'a.total_paid',
				'total_price', 'a.total_price',
				'total_shipping', 'a.total_shipping',
				'total_discount', 'a.total_discount',
				'total_taxes', 'a.total_taxes',
				'product_id', 'a2.product_id',
				'vendor_id', 'a2.parent_id',
				'published', 'note', 'a.note',
			];
		}
		parent::__construct($config);
	}

	public function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.payment_txn_id');
		$id .= ':' . $this->getState('filter.payment_status');
		$id .= ':' . $this->getState('filter.user_email');
		$id .= ':' . $this->getState('filter.from_date');
		$id .= ':' . $this->getState('filter.to_date');
		$id .= ':' . $this->getState('filter.currency_id');
		$id .= ':' . $this->getState('filter.user_id');
		$id .= ':' . $this->getState('filter.product_id');
		$id .= ':' . $this->getState('filter.vendor_id');
		$id .= ':' . $this->getState('filter.parent_id');

		return parent::getStoreId($id);
	}

	public function getItems()
	{
		if ($items = parent::getItems())
		{
			foreach ($items as &$item)
			{
				$order = easyshop(Order::class);
				$order->load($item->id);
				$order->author = $item->author;
				$order->editor = $item->editor;
				$item          = $order;
			}
		}

		return $items;
	}

	public function getFilterForm($data = [], $loadData = true)
	{
		if ($form = parent::getFilterForm($data, $loadData))
		{
			if (!easyshop('config', 'multi_currencies_mode', 0))
			{
				$form->removeField('currency_id', 'filter');
			}
		}

		return $form;
	}

	protected function populateState($ordering = null, $direction = null)
	{
		$value = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $value);

		$value = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $value);

		$value = $this->getUserStateFromRequest($this->context . '.filter.payment_txn_id', 'filter_payment_txn_id', '');
		$this->setState('filter.payment_txn_id', $value);

		$value = $this->getUserStateFromRequest($this->context . '.filter.payment_status', 'filter_payment_status', '');
		$this->setState('filter.payment_status', $value);

		$value = $this->getUserStateFromRequest($this->context . '.filter.user_email', 'filter_user_email', '');
		$this->setState('filter.user_email', $value);

		$value = $this->getUserStateFromRequest($this->context . '.filter.from_date', 'filter_from_date', '');
		$this->setState('filter.from_date', $value);

		$value = $this->getUserStateFromRequest($this->context . '.filter.to_date', 'filter_to_date', '');
		$this->setState('filter.to_date', $value);

		$value = $this->getUserStateFromRequest($this->context . '.filter.user_id', 'filter_user_id', '');
		$this->setState('filter.user_id', $value);

		$value = $this->getUserStateFromRequest($this->context . '.filter.currency_id', 'filter_currency_id', '');
		$this->setState('filter.currency_id', $value);

		$value = $this->getUserStateFromRequest($this->context . '.filter.product_id', 'filter_product_id', '');
		$this->setState('filter.product_id', $value);

		$vendorId = $this->getUserStateFromRequest($this->context . '.filter.vendor_id', 'filter_vendor_id', '');
		$this->setState('filter.vendor_id', $vendorId);

		$parentId = $this->getUserStateFromRequest($this->context . '.filter.parent_id', 'filter_parent_id', '');
		$this->setState('filter.parent_id', $parentId);

		parent::populateState('a.created_date', 'desc');
	}

	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = parent::getListQuery();
		$query->select($this->getState('list.select', 'a.id, u.name AS author, uu.name AS editor'))
			->from($db->quoteName('#__easyshop_orders', 'a'))
			->select('u.name AS author, uu.name AS editor')
			->leftJoin($db->quoteName('#__users', 'u') . ' ON u.id = a.created_by')
			->leftJoin($db->quoteName('#__users', 'uu') . ' ON uu.id = a.checked_out')
			->leftJoin($db->quoteName('#__easyshop_users', 'eu') . ' ON eu.id = a.user_id')
			->leftJoin($db->quoteName('#__users', 'uuu') . ' ON eu.user_id = uuu.id');
		$txnId = trim($this->getState('filter.payment_txn_id'));

		if (!empty($txnId))
		{
			$query->where('a.payment_txn_id = ' . $db->quote($txnId));
		}

		$paymentStatus = trim($this->getState('filter.payment_status'));

		if (is_numeric($paymentStatus))
		{
			$query->where('a.payment_status = ' . (int) $paymentStatus);
		}

		$userEmail = trim($this->getState('filter.user_email'));

		if (!empty($userEmail))
		{
			$query->where('a.payment_user_email = ' . $db->quote($userEmail));
		}

		$fromDate = $this->getState('filter.from_date');
		$toDate   = $this->getState('filter.to_date');

		if (!empty($fromDate) || !empty($toDate))
		{
			try
			{
				$utility = easyshop(Utility::class);

				if (!empty($fromDate))
				{
					$fromDate = $utility->getDate($fromDate);
					$fromDate->setTime(0, 0, 0);
					$fromDate = $db->quote($fromDate->toSql());
				}

				if (!empty($toDate))
				{
					$toDate = $utility->getDate($toDate);
					$toDate->setTime(23, 59, 59);
					$toDate = $db->quote($toDate->toSql());
				}

				if (!empty($fromDate) && empty($toDate))
				{
					$query->where('a.created_date >= ' . $fromDate);
				}
				elseif (!empty($fromDate) && !empty($toDate))
				{
					$query->where('a.created_date BETWEEN ' . $fromDate . ' AND ' . $toDate);
				}

			}
			catch (Exception $e)
			{

			}
		}

		$currencyId = $this->getState('filter.currency_id');

		if (is_numeric($currencyId))
		{
			$query->where('a.currency_id = ' . (int) $currencyId);
		}

		$userId = $this->getState('filter.user_id');

		if (is_numeric($userId))
		{
			$query->where('a.user_id = ' . (int) $userId);
		}

		$productId = $this->getState('filter.product_id');

		if (is_numeric($productId))
		{
			$query->innerJoin($db->quoteName('#__easyshop_order_products', 'a2') . ' ON a2.order_id = a.id')
				->where('a2.product_id = ' . (int) $productId);
		}

		$vendorId = $this->getState('filter.vendor_id');

		if (is_numeric($vendorId))
		{
			$query->where('a.vendor_id = ' . (int) $vendorId);
		}

		$parentId = $this->getState('filter.parent_id');

		if (is_numeric($parentId))
		{
			$query->where('a.parent_id = ' . (int) $parentId);
		}
		elseif (empty($vendorId))
		{
			$query->where('a.parent_id = 0');
		}

		$this->standardFilter($db, $query);

		return $query;
	}
}
