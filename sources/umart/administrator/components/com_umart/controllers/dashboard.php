<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\Currency;
use Umart\Controller\BaseController;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;

class UmartControllerDashboard extends BaseController
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
			 * @var UmartModelDashboard $model
			 */
			$model         = plg_sytem_umart_main('model', 'Dashboard', UMART_COMPONENT_ADMINISTRATOR);
			$currencyClass = plg_sytem_umart_main(Currency::class);
			$startDate     = $this->input->get('startDate', 'now', 'string');
			$endDate       = $this->input->get('endDate', 'now', 'string');
			$currencyId    = (int) $this->input->getInt('currencyId', $currencyClass->getDefault()->get('id'));
			$renderer      = plg_sytem_umart_main('renderer');
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
			$view = $this->getView('Dashboard', 'html', 'UmartView');
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

		plg_sytem_umart_main('app')->close();
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
