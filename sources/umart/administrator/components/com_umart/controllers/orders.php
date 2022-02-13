<?php

/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\Currency;
use Umart\Classes\Order;
use Umart\Classes\Utility;
use Umart\Controller\AdminController;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

class UmartControllerOrders extends AdminController
{
	public function export()
	{
		$app      = JFactory::getApplication();
		$redirect = $app->input->server->getString('HTTP_REFERER');

		if (!JUri::isInternal($redirect))
		{
			$redirect = JRoute::_('index.php?option=com_umart&view=orders', false);
		}

		if (!JSession::checkToken('post'))
		{
			$app->enqueueMessage(Text::_('JINVALID_TOKEN_NOTICE'), 'warning');
			$app->redirect($redirect);
		}

		$pks = ArrayHelper::toInteger($app->input->get('cid', [], 'array'));

		if ($pks)
		{
			/**
			 * @var $order     Order
			 * @var $utility   Utility
			 * @var $currency  Currency
			 */
			$order     = plg_sytem_umart_main(Order::class);
			$utility   = plg_sytem_umart_main(Utility::class);
			$fields    = plg_sytem_umart_main('config', 'csv_fields', ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11']);
			$textMaps  = [
				'0'  => Text::_('COM_UMART_ORDER_CODE'),
				'1'  => Text::_('COM_UMART_DATE'),
				'2'  => Text::_('COM_UMART_ORDER_STATUS'),
				'3'  => Text::_('COM_UMART_PAYMENT_STATUS'),
				'4'  => Text::_('COM_UMART_TOTAL_PRICE'),
				'5'  => Text::_('COM_UMART_TAXES'),
				'6'  => Text::_('COM_UMART_SHIPPING'),
				'7'  => Text::_('COM_UMART_FEE'),
				'8'  => Text::_('COM_UMART_DISCOUNT'),
				'9'  => Text::_('COM_UMART_CUSTOMER'),
				'10' => Text::_('COM_UMART_BILLING_ADDRESS'),
				'11' => Text::_('COM_UMART_SHIPPING_ADDRESS'),
			];
			$priceMaps = [
				'4' => 'total_price',
				'5' => 'total_taxes',
				'6' => 'total_shipping',
				'7' => 'total_fee',
				'8' => 'total_discount',
			];
			$app->setHeader('Content-Type', 'text/csv; charset=utf-8');
			$app->setHeader('Content-Disposition', 'attachment;filename=orders-' . date('Y-m-d') . '.csv');
			$app->sendHeaders();
			$fp = fopen('php://output', 'w');
			fputcsv($fp, array_map(function ($i) use ($textMaps) {
				return $textMaps[$i];
			}, $fields));

			$oStatus        = $order->getOrderStatus();
			$pStatus        = $order->getPaymentStatus();
			$currencyAmount = [];
			$count          = 0;

			foreach ($pks as $pk)
			{
				if ($order->load($pk))
				{
					$address    = $order->getAddress();
					$currency   = $order->get('currency');
					$currencyId = $currency->get('id');
					$amount     = $order->get('total_price', 0.00);
					$data       = [];
					$count++;

					if (isset($currencyAmount[$currencyId]))
					{
						$currencyAmount[$currencyId] += $amount;
					}
					else
					{
						$currencyAmount[$currencyId] = $amount;
					}

					foreach ($fields as $field)
					{
						switch ($field)
						{
							case '0':
								$data[] = '#' . $order->get('order_code');
								break;

							case '1':
								$data[] = $utility->displayDate($order->get('created_date'));
								break;

							case '2':
								$data[] = $oStatus[$order->get('state')];
								break;

							case '3':
								$data[] = $pStatus[$order->get('payment_status')];
								break;

							case '4':
							case '5':
							case '6':
							case '7':
							case '8':
								$data[] = $currency->toFormat($order->get($priceMaps[$field], 0.00));
								break;

							case '9':
								$data[] = $order->customerName . '. ' . $order->get('user_email');
								break;

							case '10':
								$data[] = $utility->formatAddress($address['billing']);
								break;

							case '11':
								$data[] = $utility->formatAddress($address['shipping']);
								break;
						}
					}

					fputcsv($fp, $data);
				}
			}

			$currency = plg_sytem_umart_main(Currency::class);
			fputcsv($fp, ['']);

			foreach ($currencyAmount as $currencyId => $amount)
			{
				if ($currency->load($currencyId))
				{
					fputcsv($fp, [
						strtoupper(Text::sprintf('COM_UMART_TOTAL_IN_CURRENCY_FORMAT', strtoupper($currency->get('code')))),
						$currency->toFormat($amount),
					]);
				}
			}

			fclose($fp);
			$app->close();
		}
	}
}
