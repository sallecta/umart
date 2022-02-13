<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\Currency;
use Umart\Classes\Order;
use Umart\Classes\Utility;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class UmartModelDashboard extends BaseDatabaseModel
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

			$orderClass = plg_sytem_umart_main(Order::class);
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

						if (!in_array($orderClass->get('state'), [UMART_ORDER_TRASHED, UMART_ORDER_CANCELLED]))
						{
							switch ((int) $orderClass->get('payment_status'))
							{
								case UMART_PAYMENT_UNPAID:
									$orders[$label]['unpaid'] += $orderClass->get('total_price');
									break;

								case UMART_PAYMENT_PAID:
									$orders[$label]['paid'] += $orderClass->get('total_paid');
									break;

								case UMART_PAYMENT_REFUND:
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
			->from($db->quoteName('#__umart_orders', 'a'))
			->where('a.state <> -2 AND a.currency_id = ' . (int) $currencyId);
		$this->triggerQuery($query, 'ORDER');

		return [$db, $query];
	}

	protected function triggerQuery($query, $queryName)
	{
		$state = plg_sytem_umart_main('state');
		$scope = $state->get('scope');
		$state->set('scope', 'DASHBOARD_MODEL_QUERY_' . strtoupper($queryName));
		plg_sytem_umart_main('app')->triggerEvent('onUmartDashboardModelPrepareQuery', [$query]);
		$state->set('scope', $scope);
	}

	public function getLatestOrders()
	{
		$ordersModel = $this->getOrdersModel();
		$ordersModel->setState('list.limit', (int) plg_sytem_umart_main('config', 'latest_order_num', 10));

		return $ordersModel->getItems();
	}

	public function getOrdersModel($start = 0, $limit = 0)
	{
		/** @var $orderModels UmartModelOrders */
		$ordersModel = plg_sytem_umart_main('model', 'Orders', UMART_COMPONENT_ADMINISTRATOR);
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
			$this->currencyClass = plg_sytem_umart_main(Currency::class)->getDefault();
		}

		return $this->currencyClass;
	}

	public function setCurrencyClass(Currency $currencyClass = null)
	{
		$this->currencyClass = $currencyClass;
	}

	public function getOrdersThisMonth()
	{
		$date   = plg_sytem_umart_main(Utility::class)->getDate();
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
			$orderClass = plg_sytem_umart_main(Order::class);

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
			->from($db->quoteName('#__umart_products', 'a'))
			->join('INNER', $db->quoteName('#__umart_order_products', 'a2') . ' ON a2.product_id = a.id')
			->join('INNER', $db->quoteName('#__umart_orders', 'a3') . ' ON a3.id = a2.order_id')
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
			->from($db->quoteName('#__umart_orders', 'a'))
			->join('INNER', $db->quoteName('#__umart_users', 'a2') . ' ON a2.id = a.user_id')
			->join('INNER', $db->quoteName('#__users', 'a3') . ' ON a3.id = a2.user_id')
			->where('a.state NOT IN(-2,5) AND a.payment_status = 1 AND a.user_id > 0')
			->group('a.user_id, a.currency_id')
			->order('orderQuantity DESC');
		$this->triggerQuery($query, 'BEST_CUSTOMER');
		$db->setQuery($query, 0, 9);

		return $db->loadObjectList();
	}
}
