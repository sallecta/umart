<?php
/**
 
 
 
 
 
 */

namespace Umart\Plugin;
defined('_JEXEC') or die;

use Umart\Classes\Currency;
use Umart\Classes\Method;
use Umart\Classes\Order;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use stdClass;

abstract class Payment extends PluginLegacy
{
	protected static $handlers = [];
	/**
	 * @var $currencies array List of allow currencies code
	 * @since 1.0.0
	 */
	protected $currencies = [];
	/**
	 * @var $currency Currency
	 * @since 1.0.0
	 */
	protected $currency = null;
	/**
	 * @var $order Order
	 * @since 1.0.0
	 */
	protected $order;
	/**
	 * @var $payment stdClass
	 * @since 1.0.0
	 */
	protected $payment;
	protected $payments = [];

	public function __construct($subject, array $config = [])
	{
		parent::__construct($subject, $config);
		$this->currency        = plg_sytem_umart_main(Currency::class)->getActive();
		$this->payments        = plg_sytem_umart_main(Method::class)->getPaymentMethods();
		$name                  = strtolower($this->_name);
		self::$handlers[$name] = $this;
	}

	public static function getHandler($element)
	{
		$name = strtolower($element);

		return isset(self::$handlers[$name]) ? self::$handlers[$name] : false;
	}

	abstract public function execute();

	public function loadOrder($order, $paymentData = [])
	{
		if (!($order instanceof Order))
		{
			// This is a call back
			$orderId = (int) $order;
			$order   = plg_sytem_umart_main(Order::class);

			if (!$order->load($orderId))
			{
				return false;
			}
		}

		if (!($order instanceof Order))
		{
			// Check again
			return false;
		}

		$this->payment = $this->getPayment($order->get('payment_id'), $paymentData);

		if (!$this->payment)
		{
			return false;
		}

		$this->currency->load($order->get('currency_id'));
		$this->order = $order;

		if (!($this->order->currency instanceof Currency))
		{
			$this->order->setCurrency($this->currency);
		}

		return true;
	}

	protected function getPayment($paymentId, $paymentData = [])
	{
		if (!isset($this->payments[$paymentId]))
		{
			return false;
		}

		$payment         = $this->payments[$paymentId];
		$payment->params = new Registry((string) $payment->params);

		if (!$payment->params->exists('card_show_holder_name'))
		{
			$payment->params->set('card_show_holder_name', 0);
		}

		if (empty($payment->image))
		{
			if (is_file(JPATH_PLUGINS . '/umart/' . $payment->element . '/logo.png'))
			{
				$payment->image = Uri::root(true) . '/plugins/umart/' . $payment->element . '/logo.png';
			}
		}
		else
		{
			$payment->image = UMART_MEDIA_URL . '/' . $payment->image;
		}

		if ($payment->params->exists('is_card') && $payment->params->get('is_card'))
		{
			$payment->cardForm = $this->getRenderer()->render('checkout.card.form', ['payment' => $payment]);
		}

		if ($returnUrl = $payment->params->get('return_url'))
		{
			$returnUrl = str_ireplace('{rootUrl}/', Uri::root(), $returnUrl);

			if (!preg_match('/^(https?\:\/\/)/i', $returnUrl))
			{
				$returnUrl = Uri::root() . trim(preg_replace('/^\/+/', '', $returnUrl));
			}

			$payment->params->set('return_url', $returnUrl);
		}

		if ($cancelUrl = $payment->params->get('cancel_url'))
		{
			$cancelUrl = str_ireplace('{rootUrl}/', Uri::root(), $cancelUrl);

			if (!preg_match('/^(https?\:\/\/)/i', $cancelUrl))
			{
				$cancelUrl = Uri::root() . trim(preg_replace('/^\/+/', '', $cancelUrl));
			}

			$payment->params->set('cancel_url', $cancelUrl);
		}

		if (!is_array($paymentData))
		{
			$paymentData = [];
		}

		if (empty($paymentData) && plg_sytem_umart_main('checkoutStep') === 'finishing')
		{
			$postFormData = $this->app->input->get('jform', [], 'array');

			if (isset($postFormData['paymentData'][$payment->id]))
			{
				$paymentData = $postFormData['paymentData'][$payment->id];
			}
		}

		$payment->data         = $paymentData;
		$payment->extraDisplay = trim($this->loadExtraDisplay($payment));

		return $payment;
	}

	protected function loadExtraDisplay($payment)
	{
		return '';
	}

	public function callBack()
	{

	}

	public function onUmartPaymentRegister()
	{
		static $registers = [];
		$name = strtolower($this->_name);

		if (array_key_exists($name, $registers))
		{
			return;
		}

		$registers[$name] = true;
		$paymentList      = $this->getList();

		if (false === $paymentList
			|| (!empty($this->currencies)
				&& !in_array($this->currency->get('code'), $this->currencies, true))
		)
		{
			return;
		}

		$methodClass = plg_sytem_umart_main(Method::class);

		foreach ($paymentList as $item)
		{
			if (strcasecmp($item->element, $this->_name) === 0
				&& ($payment = $this->getPayment($item->id))
			)
			{
				$methodClass->addPaymentMethod($payment);
			}
		}
	}

	protected function getList()
	{
		return $this->payments;
	}

	public function getCurrencies()
	{
		return $this->currencies;
	}

	public function validate(stdClass $payment)
	{
		return true;
	}

	public function registerCustomerPaymentForm(Order $order, stdClass $payment)
	{
		return null;
	}

	protected function getNotifyUrl($params = [])
	{
		$notifyUrl = Uri::root() . 'index.php?option=com_umart&task=payment.callBack&token=' . $this->order->get('token');

		if (count($params))
		{
			$notifyUrl .= '&' . http_build_query($params);
		}

		return $notifyUrl;
	}

	protected function getCallbackUrl($callBackStringParam = '')
	{
		return Uri::root() . 'component/umart/pm-callback-tok' . $this->order->get('token') . '/' . $callBackStringParam;
	}

	protected function getAddressField($type, $name, $property = 'value')
	{
		if (!($this->order instanceof Order))
		{
			return null;
		}

		$address = $this->order->get('address');

		if (!empty($address[$type]))
		{
			foreach ($address[$type] as $id => $field)
			{
				if ($name == $field->field_name)
				{
					return isset($field->{$property}) ? $field->{$property} : null;
				}
			}
		}

		return null;
	}
}
