<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\Utility;
use Umart\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;

class UmartControllerNotification extends BaseController
{
	public function fetch()
	{
		try
		{
			$renderer = plg_sytem_umart_main('renderer');
			$response = [
				'order'      => [
					'count' => 0,
					'html'  => '<p class="uk-modal-body">' . JText::_('COM_UMART_NO_NEW_ORDERS_FOUND_MSG') . '</p>',
				],
				'log'        => [
					'count' => 0,
					'html'  => '',
				],
				'updateInfo' => [
					'version' => '',
					'message' => '',
				],
			];

			if ($orders = $this->fetchOrders())
			{
				$response['order'] = [
					'count'  => count($orders),
					'orders' => $orders,
					'html'   => $renderer->render('notification.orders', ['orders' => $orders]),
				];
			}

			if ($logs = $this->fetchLogs())
			{
				$response['log'] = $logs;
			}

			$updateInfo = plg_sytem_umart_main(Utility::class)->findUpdate(true);

			if (isset($updateInfo['version']))
			{
				$updateInfo['message']  = JText::sprintf('COM_UMART_UPDATE_FORMAT', $updateInfo['version']);
				$response['updateInfo'] = $updateInfo;
			}
		}
		catch (RuntimeException $e)
		{
			$response = $e;
		}

		echo new JsonResponse($response);
		plg_sytem_umart_main('app')->close();
	}

	protected function fetchOrders()
	{
		$db    = plg_sytem_umart_main('db');
		$query = $db->getQuery(true)
			->select('a.id, a.order_code, a.state, a.payment_status, a.created_date, a.total_price, a.currency_id')
			->from($db->quoteName('#__umart_orders', 'a'))
			->where('a.viewed = 0')
			->order('a.id DESC');
		$db->setQuery($query, 0, (int) plg_sytem_umart_main('config', 'notification_orders_number', 10));

		return $db->loadObjectList();
	}

	protected function fetchLogs()
	{
		/**
		 * @var UmartModelLog $logsModel
		 * @since 1.1.9
		 */

		$app       = plg_sytem_umart_main('app');
		$logsModel = plg_sytem_umart_main('Model', 'Logs', UMART_COMPONENT_ADMINISTRATOR, ['ignore_request' => true]);
		$logsModel->setState('list.start', 0);
		$logsModel->setState('list.limit', 10);
		$logsModel->setState('list.ordering', 'a.id');
		$logsModel->setState('list.direction', 'DESC');
		$fetchedLogs = $logsModel->getItems();
		$stateLogs   = $app->getUserState('com_umart.fetch.logs', ['logs' => [], 'count' => 0]);
		$toAssoc     = function ($logs) {
			$results = [];

			foreach ($logs as $log)
			{
				$results[$log->id] = $log;
			}

			return $results;
		};

		$fetchedLogs = $toAssoc($fetchedLogs);

		if (empty($stateLogs['logs']))
		{
			$stateLogs['logs'] = $fetchedLogs;
		}
		else
		{
			$stateLogs['logs'] = $toAssoc($stateLogs['logs']);
		}

		$diffLogIds = array_diff(array_keys($fetchedLogs), array_keys($stateLogs['logs']));

		ksort($stateLogs['logs']);
		$stateLogs['logs'] = array_values(array_reverse($stateLogs['logs']));

		if (!empty($diffLogIds))
		{
			foreach ($diffLogIds as $diffLogId)
			{
				array_unshift($stateLogs['logs'], $fetchedLogs[$diffLogId]);
			}

			$stateLogs['count'] += count($diffLogIds);
		}

		if (count($stateLogs['logs']) > 10)
		{
			array_splice($stateLogs['logs'], 10);
		}

		$app->setUserState('com_umart.fetch.logs', $stateLogs);

		return [
			'count' => $stateLogs['count'],
			'html'  => plg_sytem_umart_main('renderer')->render('notification.logs', [
				'logs' => $stateLogs['logs'],
			]),
		];
	}
}
