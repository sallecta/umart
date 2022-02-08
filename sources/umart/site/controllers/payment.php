<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\Log;
use ES\Classes\Method;
use ES\Classes\Order;
use ES\Controller\BaseController;
use ES\Plugin\Payment;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

class EasyshopControllerPayment extends BaseController
{
	public function callBack()
	{
		try
		{
			/**
			 * @var Order  $order
			 * @var Method $method
			 * @var Log    $log
			 */
			$token = trim($this->app->input->get('token', '', 'string'));
			$log   = easyshop(Log::class);

			if (strlen($token) === 40)
			{
				$order = easyshop(Order::class);

				if ($order->load(['token' => $token]))
				{
					$method    = easyshop(Method::class);
					$paymentId = (int) $this->app->input->get('updatePaymentId', 0, 'uint');

					if ($paymentId > 0)
					{
						$order->set('payment_id', $paymentId);
					}
					else
					{
						$paymentId = (int) $order->get('payment_id', 0);
					}

					if ($paymentId && ($payment = $method->get($paymentId)))
					{
						PluginHelper::importPlugin('easyshoppayment', $payment->element);
						$handler = Payment::getHandler($payment->element);

						if ($handler instanceof Payment)
						{
							if ($handler->loadOrder($order))
							{
								$log->addEntry('com_easyshop.payment', 'COM_EASYSHOP_PAYMENT_ELEMENT_PROCESS_CALLBACK', [ucfirst($payment->name), $order->get('order_code'), $order->get('id')]);
								$handler->callBack();
							}
							else
							{
								$message = Text::sprintf('COM_EASYSHOP_PAYMENT_CALLBACK_ERR_LOAD_ORDER_FORMAT', $token);
								$log->addEntry('com_easyshop.payment', 'COM_EASYSHOP_PAYMENT_CALLBACK_FAIL', [$message]);
							}
						}
					}
				}

			}
		}
		catch (Exception $e)
		{
			$log->addEntry('com_easyshop.payment', 'COM_EASYSHOP_PAYMENT_CALLBACK_FAIL', [$e->getMessage()]);
		}

		$this->redirectBackPage();
	}
}
