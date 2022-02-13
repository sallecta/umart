<?php
/**
 
 
 
 
 
 */

namespace Umart\Plugin\Payment;
defined('_JEXEC') or die;

use Omnipay\Common\Message\AbstractRequest;
use Umart\Plugin\Payment;
use Umart\Classes\Log;
use Umart\Classes\Email;

abstract class PaymentOmnipay extends Payment
{
	protected function response($response)
	{
		/** @var AbstractRequest $response */
		$data = $response->getData();
		$log  = new Log;
		$name = ucfirst($this->_name);

		if ($response->isSuccessful())
		{
			$orderState = (int) $this->payment->order_status;
			$amount     = $this->order->get('total_price');
			$extraData  = [
				'total_paid'     => $amount,
				'payment_data'   => json_encode($data),
				'payment_txn_id' => $response->getTransactionReference(),
			];

			if (in_array($orderState, [1, 2, 3, 4], true))
			{
				$extraData['state'] = $orderState;
			}

			$paid = $this->order->paid($extraData);

			if (false !== $paid)
			{
				(new Email)->sendOn('[ON_ORDER_CHANGE_PAYMENT]', $this->order->getLayoutData(), $this->order);
				$log->addEntry('com_umart.payment', 'COM_UMART_PAYMENT_PAID_AMOUNT', [$name, $this->currency->toFormat($amount)]);
				$message = trim($this->payment->params->get('payment_success_message'));

				if (!empty($message))
				{
					return $message;
				}
			}
		}
		elseif ($response->isRedirect())
		{
			return $this->getRenderer()->render('checkout.payment.form', [
				'response' => $response,
				'payment'  => $this->payment,
			]);
		}
		else
		{
			$message = $response->getMessage();
			$log->addEntry('com_umart.payment', 'COM_UMART_PAYMENT_ERROR_MESSAGE', [$name, $message]);
			$this->app->enqueueMessage($message, 'warning');
		}
	}
}
