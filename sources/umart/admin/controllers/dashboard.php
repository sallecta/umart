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
use ES\Controller\BaseController;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;

class EasyshopControllerDashboard extends BaseController
{
	public function loadChartData()
	{
		try
		{
			if (!Session::checkToken())
			{
				throw new RuntimeException(JText::_('JINVALID_TOKEN'));
			}
			/**
			 * @var EasyshopModelDashboard $model
			 */
			$model         = easyshop('model', 'Dashboard', ES_COMPONENT_ADMINISTRATOR);
			$currencyClass = easyshop(Currency::class);
			$startDate     = $this->input->get('startDate', 'now', 'string');
			$endDate       = $this->input->get('endDate', 'now', 'string');
			$currencyId    = (int) $this->input->getInt('currencyId', $currencyClass->getDefault()->get('id'));
			$renderer      = easyshop('renderer');
			$tz            = CMSFactory::getUser()->getTimeZone();

			try
			{
				$startDate = CMSFactory::getDate($startDate, $tz);
				$endDate   = CMSFactory::getDate($endDate, $tz);
			}
			catch (Exception $e)
			{
				$startDate = CMSFactory::getDate('now', $tz);
				$endDate   = CMSFactory::getDate('now', $tz);
			}

			$startDate->setTime($startDate->getOffsetFromGmt(true), 0, 0);
			$endDate->setTime($endDate->getOffsetFromGmt(true), 0, 0);
			$startDate->setTimezone(new DateTimeZone('UTC'));
			$endDate->setTimezone(new DateTimeZone('UTC'));
			$currencyClass->load($currencyId);
			$view = $this->getView('Dashboard', 'html', 'EasyshopView');
			$model->setCurrencyClass($currencyClass);
			$view->setModel($model, true);
			$chartData   = $model->getChartData($startDate, $endDate, $currencyId);
			$displayData = array_merge($view->getDisplayData(), $this->getDisplayData($chartData));
			$response    = [
				'tiles'           => $renderer->render('dashboard.tile.tile', $displayData),
				'latestOrderHTML' => $renderer->render('order.summary', [
					'orders' => $displayData['latestOrders'],
				]),
				'chart'           => $renderer->render('dashboard.chart.chart', $displayData),
			];
		}
		catch (RuntimeException $e)
		{
			$response = $e;
		}

		echo new JsonResponse($response);

		easyshop('app')->close();
	}

	protected function getDisplayData($chartData)
	{
		$displayData = [
			'labels'         => json_encode(array_keys($chartData)),
			'paidAmountJS'   => [],
			'unpaidAmountJS' => [],
			'refundAmountJS' => [],
			'paidAmount'     => 0.0000,
			'unpaidAmount'   => 0.0000,
			'refundAmount'   => 0.0000,
			'paidRate'       => 0.0000,
			'unpaidRate'     => 0.0000,
			'refundRate'     => 0.0000,
		];

		$sumTotal = 0.0000;

		foreach ($chartData as $day => $item)
		{
			$displayData['paidAmountJS'][]   = $item['paid'];
			$displayData['unpaidAmountJS'][] = $item['unpaid'];
			$displayData['refundAmountJS'][] = $item['refund'];
			$displayData['paidAmount']       += $item['paid'];
			$displayData['unpaidAmount']     += $item['unpaid'];
			$displayData['refundAmount']     += $item['refund'];
			$sumTotal                        += $item['paid'] + $item['unpaid'] + $item['refund'];
		}

		if ($sumTotal > 0.0000)
		{
			$displayData['paidRate']   = round($displayData['paidAmount'] / $sumTotal, 4);
			$displayData['unpaidRate'] = round($displayData['unpaidAmount'] / $sumTotal, 4);
			$displayData['refundRate'] = round($displayData['refundAmount'] / $sumTotal, 4);
		}

		return $displayData;
	}
}
