<?php
/**
 
 
 
 
 
 */

defined('_JEXEC') or die;

use Umart\Classes\Email;
use Umart\Classes\Order;
use Umart\Classes\User;
use Umart\Plugin\Payment;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

class PlgUmartPaymentOffline extends Payment
{
	public function __construct($subject, array $config = [])
	{
		parent::__construct($subject, $config);
		(new Email)->register('UMART_PAYMENT_OFFLINE_EMAIL_CARD', 'PLG_UMART_PAYMENT_OFFLINE_ON_EMAIL_CARD');
	}

	public function execute()
	{
		if (empty($this->payment->card)
			|| empty($this->payment->card['number'])
			|| $this->payment->params->get('umart_payment_offline_type') !== 'collect_card'
		)
		{
			return;
		}

		$registry = new Registry;
		$registry->loadArray((array) $this->payment->card);
		$lftPart         = substr($this->payment->card['number'], 0, floor(strlen($this->payment->card['number']) / 2));
		$rgtPart         = preg_replace('/' . $lftPart . '/', '', $this->payment->card['number'], 1);
		$cardNumber      = str_repeat('*', strlen($lftPart)) . $rgtPart;
		$cardStoreNumber = $lftPart . str_repeat('*', strlen($rgtPart));
		$registry->set('number', $cardStoreNumber);
		$registry->set('cvv', str_repeat('*', strlen($this->payment->card['cvv'])));
		$table = $this->order->getTable();
		$table->set('payment_data', (string) $registry->toString());
		$table->store();
		$replaceData = array_merge($this->order->getLayoutData(), [
			'{CARD_HOLDER_NAME}'  => $this->payment->card['holderName'],
			'{CARD_NUMBER}'       => $cardNumber,
			'{CARD_CVV}'          => $this->payment->card['cvv'],
			'{CARD_EXPIRY_MONTH}' => $this->payment->card['expiryMonth'],
			'{CARD_EXPIRY_YEAR}'  => $this->payment->card['expiryYear'],
		]);
		umart(Email::class)->sendOn('[UMART_PAYMENT_OFFLINE_EMAIL_CARD]', $replaceData);
	}

	public function onUmartpaymentBeforeSave($payment, &$data)
	{
		if ($payment->element != $this->_name)
		{
			return;
		}

		if ($data['params']['umart_payment_offline_type'] == 'collect_card')
		{
			$data['params']['is_card'] = 1;
		}
		else
		{
			$data['params']['is_card'] = 0;
		}
	}

	public function onUmartpaymentOrderArea(Order $order, stdClass $payment)
	{
		if (umart('site')
			|| $payment->element !== 'umart_payment_offline'
			|| $payment->params->get('umart_payment_offline_type') !== 'collect_card'
			|| !$payment->data->get('number')
		)
		{
			return;
		}

		$payment->orderArea = $this->getRenderer()->render('umart_payment_offline.order.area', [
			'order'   => $order,
			'payment' => $payment,
		]);
	}

	public function onAjaxOffline()
	{
		$user = new User;

		if (!$user->core('admin'))
		{
			$user->stop();
		}

		if ($this->app->input->get('request') === 'deleteCardData')
		{
			$query = $this->db->getQuery(true)
				->update($this->db->quoteName('#__umart_orders'))
				->set($this->db->quoteName('payment_data') . ' = ' . $this->db->quote('{}'))
				->where($this->db->quoteName('id') . ' = ' . (int) $this->app->input->getUint('orderId', 0));
			$this->db->setQuery($query)
				->execute();
		}
	}

	protected function getPayment($paymentId, $paymentData = [])
	{
		$payment = parent::getPayment($paymentId, $paymentData);

		if ($payment)
		{
			$rootUrl = Uri::root(true);

			if (empty($payment->image))
			{
				$type = $payment->params->get('umart_payment_offline_type');

				if ($type == 'bank_transfer')
				{
					$payment->image = $rootUrl . '/plugins/umart/' . $payment->element . '/bank-transfer.png';
				}
				elseif ($type == 'cod')
				{
					$payment->image = $rootUrl . '/plugins/umart/' . $payment->element . '/cash-on-delivery.png';
				}
				else
				{
					$payment->image = $rootUrl . '/plugins/umart/' . $payment->element . '/card.png';
				}
			}
		}

		return $payment;
	}
}
