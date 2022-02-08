<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use ES\Classes\Email;
use ES\Classes\Order;
use ES\Classes\User;
use ES\Plugin\Payment;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

class PlgEasyshopPaymentOffline extends Payment
{
	public function __construct($subject, array $config = [])
	{
		parent::__construct($subject, $config);
		(new Email)->register('EASYSHOPPAYMENT_OFFLINE_EMAIL_CARD', 'PLG_EASYSHOPPAYMENT_OFFLINE_ON_EMAIL_CARD');
	}

	public function execute()
	{
		if (empty($this->payment->card)
			|| empty($this->payment->card['number'])
			|| $this->payment->params->get('offline_type') !== 'collect_card'
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
		easyshop(Email::class)->sendOn('[EASYSHOPPAYMENT_OFFLINE_EMAIL_CARD]', $replaceData);
	}

	public function onEasyshoppaymentBeforeSave($payment, &$data)
	{
		if ($payment->element != $this->_name)
		{
			return;
		}

		if ($data['params']['offline_type'] == 'collect_card')
		{
			$data['params']['is_card'] = 1;
		}
		else
		{
			$data['params']['is_card'] = 0;
		}
	}

	public function onEasyshoppaymentOrderArea(Order $order, stdClass $payment)
	{
		if (easyshop('site')
			|| $payment->element !== 'offline'
			|| $payment->params->get('offline_type') !== 'collect_card'
			|| !$payment->data->get('number')
		)
		{
			return;
		}

		$payment->orderArea = $this->getRenderer()->render('offline.order.area', [
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
				->update($this->db->quoteName('#__easyshop_orders'))
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
				$type = $payment->params->get('offline_type');

				if ($type == 'bank_transfer')
				{
					$payment->image = $rootUrl . '/plugins/easyshoppayment/' . $payment->element . '/bank-transfer.png';
				}
				elseif ($type == 'cod')
				{
					$payment->image = $rootUrl . '/plugins/easyshoppayment/' . $payment->element . '/cash-on-delivery.png';
				}
				else
				{
					$payment->image = $rootUrl . '/plugins/easyshoppayment/' . $payment->element . '/card.png';
				}
			}
		}

		return $payment;
	}
}
