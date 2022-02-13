<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\Log;
use Umart\Classes\Method;
use Umart\Classes\Order;
use Umart\Controller\BaseController;
use Umart\Plugin\Payment;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

class UmartControllerPayment extends BaseController
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
			$log   = plg_sytem_umart_main(Log::class);

			if (strlen($token) === 40)
			{
				$order = plg_sytem_umart_main(Order::class);

				if ($order->load(['token' => $token]))
				{
					$method    = plg_sytem_umart_main(Method::class);
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
						PluginHelper::importPlugin('umart_payment', $payment->element);
						$handler = Payment::getHandler($payment->element);

						if ($handler instanceof Payment)
						{
							if ($handler->loadOrder($order))
							{
								$log->addEntry('com_umart.payment', 'COM_UMART_PAYMENT_ELEMENT_PROCESS_CALLBACK', [ucfirst($payment->name), $order->get('order_code'), $order->get('id')]);
								$handler->callBack();
							}
							else
							{
								$message = Text::sprintf('COM_UMART_PAYMENT_CALLBACK_ERR_LOAD_ORDER_FORMAT', $token);
								$log->addEntry('com_umart.payment', 'COM_UMART_PAYMENT_CALLBACK_FAIL', [$message]);
							}
						}
					}
				}

			}
		}
		catch (Exception $e)
		{
			$log->addEntry('com_umart.payment', 'COM_UMART_PAYMENT_CALLBACK_FAIL', [$e->getMessage()]);
		}

		$this->redirectBackPage();
	}
}
