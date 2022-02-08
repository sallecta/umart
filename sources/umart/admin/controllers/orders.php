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
use ES\Controller\AdminController;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

class EasyshopControllerOrders extends AdminController
{
	public function export()
	{
		$app      = JFactory::getApplication();
		$redirect = $app->input->server->getString('HTTP_REFERER');

		if (!JUri::isInternal($redirect))
		{
			$redirect = JRoute::_('index.php?option=com_easyshop&view=orders', false);
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
			$order     = easyshop(Order::class);
			$utility   = easyshop(Utility::class);
			$fields    = easyshop('config', 'csv_fields', ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11']);
			$textMaps  = [
				'0'  => Text::_('COM_EASYSHOP_ORDER_CODE'),
				'1'  => Text::_('COM_EASYSHOP_DATE'),
				'2'  => Text::_('COM_EASYSHOP_ORDER_STATUS'),
				'3'  => Text::_('COM_EASYSHOP_PAYMENT_STATUS'),
				'4'  => Text::_('COM_EASYSHOP_TOTAL_PRICE'),
				'5'  => Text::_('COM_EASYSHOP_TAXES'),
				'6'  => Text::_('COM_EASYSHOP_SHIPPING'),
				'7'  => Text::_('COM_EASYSHOP_FEE'),
				'8'  => Text::_('COM_EASYSHOP_DISCOUNT'),
				'9'  => Text::_('COM_EASYSHOP_CUSTOMER'),
				'10' => Text::_('COM_EASYSHOP_BILLING_ADDRESS'),
				'11' => Text::_('COM_EASYSHOP_SHIPPING_ADDRESS'),
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

			$currency = easyshop(Currency::class);
			fputcsv($fp, ['']);

			foreach ($currencyAmount as $currencyId => $amount)
			{
				if ($currency->load($currencyId))
				{
					fputcsv($fp, [
						strtoupper(Text::sprintf('COM_EASYSHOP_TOTAL_IN_CURRENCY_FORMAT', strtoupper($currency->get('code')))),
						$currency->toFormat($amount),
					]);
				}
			}

			fclose($fp);
			$app->close();
		}
	}
}
