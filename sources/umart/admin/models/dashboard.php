<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\Currency;
use ES\Classes\Order;
use ES\Classes\Utility;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class EasyshopModelDashboard extends BaseDatabaseModel
{
	protected $currencyClass;

	public function getChartData($startDate, $endDate, $currencyId, $ignoreNoOrders = false)
	{
		$diff = (int) $startDate->diff($endDate)->format('%R%a');

		if ($diff <= 7)
		{
			$format = 'D, d M Y';
		}
		elseif ($diff <= 31)
		{
			$format = 'M, d Y';
		}
		else
		{
			$format = 'M Y';
		}

		$orders = [];
		list($db, $query) = $this->getOrderBaseQuery($currencyId);
		$query->where('DATE(a.created_date) BETWEEN DATE('
			. $db->quote($startDate->format('Y-m-d', true)) . ') AND DATE('
			. $db->quote($endDate->format('Y-m-d', true)) . ')'
		);
		$db->setQuery($query);

		if ($orderIds = $db->loadColumn())
		{
			/** @var $orderClass Order */

			$orderClass = easyshop(Order::class);
			$tz         = Factory::getUser()->getTimezone();

			foreach ($orderIds as $orderId)
			{
				try
				{
					if ($orderClass->load($orderId))
					{
						$d = Factory::getDate($orderClass->get('created_date'), 'UTC');
						$d->setTimezone($tz);
						$label = $d->format($format, true);

						if (!isset($orders[$label]))
						{
							$orders[$label] = [
								'orders' => [],
								'paid'   => 0.0000,
								'unpaid' => 0.0000,
								'refund' => 0.0000,
							];
						}

						$orders[$label]['orders'][] = clone $orderClass;

						if (!in_array($orderClass->get('state'), [ES_ORDER_TRASHED, ES_ORDER_CANCELLED]))
						{
							switch ((int) $orderClass->get('payment_status'))
							{
								case ES_PAYMENT_UNPAID:
									$orders[$label]['unpaid'] += $orderClass->get('total_price');
									break;

								case ES_PAYMENT_PAID:
									$orders[$label]['paid'] += $orderClass->get('total_paid');
									break;

								case ES_PAYMENT_REFUND:
									$orders[$label]['refund'] += $orderClass->get('total_paid');
									break;
							}
						}
					}
				}
				catch (Exception $e)
				{

				}
			}
		}

		if ($ignoreNoOrders)
		{
			$tmp = [];

			foreach ($orders as $label => $data)
			{
				if ($data['orders'])
				{
					$tmp[$label] = $data;
				}
			}

			$orders = $tmp;
		}

		return $orders;
	}

	protected function getOrderBaseQuery($currencyId)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('a.id')
			->from($db->quoteName('#__easyshop_orders', 'a'))
			->where('a.state <> -2 AND a.currency_id = ' . (int) $currencyId);
		$this->triggerQuery($query, 'ORDER');

		return [$db, $query];
	}

	protected function triggerQuery($query, $queryName)
	{
		$state = easyshop('state');
		$scope = $state->get('scope');
		$state->set('scope', 'DASHBOARD_MODEL_QUERY_' . strtoupper($queryName));
		easyshop('app')->triggerEvent('onEasyshopDashboardModelPrepareQuery', [$query]);
		$state->set('scope', $scope);
	}

	public function getLatestOrders()
	{
		$ordersModel = $this->getOrdersModel();
		$ordersModel->setState('list.limit', (int) easyshop('config', 'latest_order_num', 10));

		return $ordersModel->getItems();
	}

	public function getOrdersModel($start = 0, $limit = 0)
	{
		/** @var $orderModels EasyshopModelOrders */
		$ordersModel = easyshop('model', 'Orders', ES_COMPONENT_ADMINISTRATOR);
		$ordersModel->setState('filter.published', '');
		$ordersModel->setState('list.ordering', 'a.created_date');
		$ordersModel->setState('list.direction', 'DESC');
		$ordersModel->setState('filter.currency_id', $this->getCurrencyClass()->get('id'));
		$ordersModel->setState('filter.vendor_id', 0);
		$ordersModel->setState('list.start', $start);
		$ordersModel->setState('list.limit', $limit);

		return $ordersModel;
	}

	public function getCurrencyClass()
	{
		if (!isset($this->currencyClass))
		{
			$this->currencyClass = easyshop(Currency::class)->getDefault();
		}

		return $this->currencyClass;
	}

	public function setCurrencyClass(Currency $currencyClass = null)
	{
		$this->currencyClass = $currencyClass;
	}

	public function getOrdersThisMonth()
	{
		$date   = easyshop(Utility::class)->getDate();
		$toDate = $date->format('Y-m-d H:i:s', true);
		$m      = (int) $date->format('m', true);

		while ($m == (int) $date->format('m', true))
		{
			$date->sub(new DateInterval('P1D'));
		}

		$date->add(new DateInterval('P1D'));
		$fromDate    = $date->format('Y-m-d H:i:s', true);
		$ordersModel = $this->getOrdersModel();
		$ordersModel->setState('filter.from_date', $fromDate);
		$ordersModel->setState('filter.to_date', $toDate);
		$results = [
			'fromDate'   => $fromDate,
			'toDate'     => $toDate,
			'items'      => [],
			'saleItems'  => [],
			'totalPrice' => 0.0000,
			'totalPaid'  => 0.0000,
		];

		if ($items = $ordersModel->getItems())
		{
			$orderClass = easyshop(Order::class);

			foreach ($items as &$item)
			{
				$orderClass->load($item->id);
				$item                  = clone $orderClass;
				$results['totalPrice'] += (float) $item->total_price;
				$results['totalPaid']  += (float) $item->total_paid;

				if ($item->total_paid > 0.0000)
				{
					$results['saleItems'][] = $item;
				}
			}

			$results['items'] = $items;

		}

		return $results;
	}

	public function getBestProducts()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('a.id, a.name, a2.product_price AS price, SUM(a2.quantity) AS orderQuantity, a3.currency_id')
			->from($db->quoteName('#__easyshop_products', 'a'))
			->join('INNER', $db->quoteName('#__easyshop_order_products', 'a2') . ' ON a2.product_id = a.id')
			->join('INNER', $db->quoteName('#__easyshop_orders', 'a3') . ' ON a3.id = a2.order_id')
			->where('a3.state NOT IN(-2,5) AND a3.payment_status = 1')
			->group('a.id, a3.currency_id')
			->order('orderQuantity DESC');
		$this->triggerQuery($query, 'BEST_PRODUCT');
		$db->setQuery($query, 0, 9);

		return $db->loadObjectList();
	}

	public function getBestCustomers()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(a.user_id) AS orderQuantity, a3.name')
			->from($db->quoteName('#__easyshop_orders', 'a'))
			->join('INNER', $db->quoteName('#__easyshop_users', 'a2') . ' ON a2.id = a.user_id')
			->join('INNER', $db->quoteName('#__users', 'a3') . ' ON a3.id = a2.user_id')
			->where('a.state NOT IN(-2,5) AND a.payment_status = 1 AND a.user_id > 0')
			->group('a.user_id, a.currency_id')
			->order('orderQuantity DESC');
		$this->triggerQuery($query, 'BEST_CUSTOMER');
		$db->setQuery($query, 0, 9);

		return $db->loadObjectList();
	}
}
