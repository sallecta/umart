<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\Cart;
use ES\Classes\Currency;
use ES\Classes\CustomField;
use ES\Classes\Email;
use ES\Classes\Method;
use ES\Classes\Order;
use ES\Classes\Tax;
use ES\Classes\Utility;
use ES\Controller\BaseController;
use ES\Plugin\Payment;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;

class EasyshopControllerCheckout extends BaseController
{
	/**
	 * @var Cart
	 * @since 1.0.0
	 */
	protected $cart;

	public function __construct(array $config)
	{
		parent::__construct($config);
		$this->cart = easyshop(Cart::class);

		// B/c for template override
		$this->registerTask('finished', 'finish');
		Form::addFieldPath(ES_COMPONENT_ADMINISTRATOR . '/models/fields');
		Form::addRulePath(ES_COMPONENT_ADMINISTRATOR . '/models/rules');
	}

	public function saveAddress()
	{
		try
		{
			$form         = $this->getCustomerForm();
			$checkoutData = ['layout' => 'confirm'];

			if ($form instanceof RuntimeException)
			{
				throw $form;
			}

			if ($form->getGroup('registration'))
			{
				$checkoutData['customerInfo'] = (array) $form->getData()->get('registration', []);
			}

			$this->calculateCheckoutFieldsPrice(false, ['billing_address', 'shipping_address', 'checkoutFields']);
			$this->cart->setCheckoutData($checkoutData, true);
			$response = [
				'pushState' => [
					'url'   => Route::_(EasyshopHelperRoute::getCartRoute('confirm'), false),
					'title' => Text::_('COM_EASYSHOP_PAGE_CHECKOUT_CONFIRM_TITLE'),
				],
				'html'      => $this->loadLayout('confirm'),
			];
		}
		catch (RuntimeException $e)
		{
			$response = $e;
		}

		echo new JsonResponse($response);
		$this->app->close();
	}

	protected function getCustomerForm($bindData = null)
	{
		Form::addFormPath(ES_COMPONENT_SITE . '/models/forms');
		$form = new Form('com_easyshop.customer', ['control' => 'jform']);
		$form->load('<form></form>'); // Stupid Joomla 4

		if (!CMSFactory::getUser()->id)
		{
			$config        = easyshop('config');
			$notAllowGuest = !$config->get('guest_checkout', 1);
			$form->loadFile('guest');

			if (null === $bindData)
			{
				$bindData = $form->filter($this->app->input->get('jform', [], 'array'), 'registration');
			}

			$form->bind($bindData);
			$register = $form->getValue('register', 'registration', 0);

			if (!$register)
			{
				$form->removeField('password1', 'registration');
				$form->removeField('password2', 'registration');
			}

			if ($config->get('enable_registration', 1))
			{
				if ($notAllowGuest && !$register)
				{
					return new RuntimeException(Text::_('COM_EASYSHOP_MUST_LOGIN_BEFORE_CHECKOUT_MSG'));
				}
			}
			elseif ($notAllowGuest)
			{
				return new RuntimeException(Text::_('COM_EASYSHOP_MUST_LOGIN_BEFORE_CHECKOUT_MSG'));
			}

			if ($form->validate($bindData, 'registration'))
			{
				if ($register)
				{
					$db       = CMSFactory::getDbo();
					$email    = $form->getValue('email', 'registration');
					$identity = $db->quote($email);
					$query    = $db->getQuery(true)
						->select('u.id')
						->from($db->quoteName('#__users', 'u'))
						->where('(u.username = ' . $identity . ' OR u.email = ' . $identity . ')');

					if ($db->setQuery($query)->loadResult())
					{
						return new RuntimeException(Text::sprintf('COM_EASYSHOP_USER_EMAIL_EXISTS_MSG', $email));
					}
				}
			}
			else
			{
				$messages = [];

				foreach ($form->getErrors() as $error)
				{
					if ($error instanceof Exception)
					{
						$messages[] = $error->getMessage();
					}
					else
					{
						$messages[] = (string) $error;
					}
				}

				return new RuntimeException(implode('::', $messages));
			}
		}

		return $form;
	}

	public function calculateCheckoutFieldsPrice($responseData = true, $groups = ['checkoutFields'])
	{
		try
		{
			if (!Session::checkToken('get'))
			{
				throw new RuntimeException(Text::_('JINVALID_TOKEN'));
			}

			$data                  = $this->validate($groups, true);
			$checkoutFieldsDetails = [];

			if (!empty($data['checkoutFields']))
			{
				/**
				 * @var CustomField $fieldClass
				 * @var Tax         $taxClass
				 */
				$taxClass   = easyshop(Tax::class);
				$fieldClass = easyshop(CustomField::class, ['reflector' => 'com_easyshop.checkout']);

				foreach ($data['checkoutFields'] as $fieldId => $fieldValue)
				{
					$checkoutFieldDetail = [
						'label' => null,
						'price' => 0.00,
						'tax'   => 0.00,
					];

					if (!empty($fieldValue) && ($field = $fieldClass->findField($fieldId)))
					{
						$params          = new Registry($field->params);
						$pricingLabel    = trim($params->get('pricingLabel', ''));
						$pricingPatterns = $params->get('pricingPattern', []);

						if ($pricingLabel && $pricingPatterns)
						{
							foreach ($pricingPatterns as $pricingPattern)
							{
								$pricingPattern = (array) $pricingPattern;
								$pattern        = trim(isset($pricingPattern['value']) ? $pricingPattern['value'] : '');
								$price          = floatval(isset($pricingPattern['text']) ? $pricingPattern['text'] : '0.00');

								if ($pattern && $price != 0)
								{
									$tax = $taxClass->calculate($params->get('taxes', []), $price);

									if ($fieldValue == $pattern || $pattern === '*')
									{
										$checkoutFieldDetail['label'] = $pricingLabel;
										$checkoutFieldDetail['price'] = $price;
										$checkoutFieldDetail['tax']   = $tax;
									}
									elseif (strcasecmp($field->type, 'FlatPicker') === 0
										&& 'single' === $params->get('mode', 'single')
										&& preg_match('/^[0-9]+-[0-9]+$/', $pattern))
									{
										list($fromDays, $toDays) = explode('-', $pattern, 2);
										$nowDate   = CMSFactory::getDate('now', 'UTC');
										$fieldDate = CMSFactory::getDate($fieldValue, 'UTC');
										$nowDate->setTime(0, 0, 0);
										$fieldDate->setTime(0, 0, 0);
										$diff = (int) $nowDate->diff($fieldDate)->format('%R%a');

										if ((int) $fromDays <= $diff && (int) $toDays >= $diff)
										{
											$checkoutFieldDetail['label'] = $pricingLabel;
											$checkoutFieldDetail['price'] = $price;
											$checkoutFieldDetail['tax']   = $tax;
										}
									}
								}

								if ($checkoutFieldDetail['label'])
								{
									$checkoutFieldsDetails[$fieldId] = $checkoutFieldDetail;
									break;
								}
							}
						}
					}
				}
			}

			$data['checkoutFieldsDetails'] = $checkoutFieldsDetails;
			$this->cart->setCheckoutData($data, true);
		}
		catch (RuntimeException $e)
		{
			if ($responseData)
			{
				echo new JsonResponse($e);
				$this->app->close();
			}

			throw $e;
		}

		if ($responseData)
		{
			echo new JsonResponse(
				[
					'fieldsPrice' => $checkoutFieldsDetails,
					'html'        => $this->loadLayout('checkout'),
				]
			);

			$this->app->close();
		}
	}

	protected function validate($groups = null, $throw = false)
	{
		/** @var Form $form */
		$form     = $this->getForm();
		$postData = $form->filter($this->app->input->get('jform', [], 'array'));
		$data     = array_merge($this->cart->getCheckoutData(), $postData);
		$task     = $this->getTask();

		if ($task === 'saveAddress')
		{
			if (empty($postData['address_different']) || easyshop('config', 'disable_shipping_address', 0))
			{
				$data['shipping_address']  = $data['billing_address'];
				$data['address_different'] = 0;
			}
		}
		elseif ($task === 'finish' && empty($postData['confirm']['terms_and_conditions']))
		{
			$data['confirm']['terms_and_conditions'] = 0;
		}

		$this->cart->setCheckoutData($data, true);
		$return = [];

		if (null === $groups)
		{
			$return[] = $form->validate($data);
		}
		else
		{
			foreach ($groups as $group)
			{
				if ($form->getGroup($group))
				{
					$return[] = $form->validate($data, $group);
				}
			}
		}

		if (in_array(false, $return, true))
		{
			$errors = $form->getErrors();

			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$message = $errors[$i]->getMessage();
				}
				else
				{
					$message = $errors[$i];
				}

				if ($throw)
				{
					throw new RuntimeException($message);
				}

				$this->app->enqueueMessage($message, 'warning');
			}

			return false;
		}

		try
		{
			easyshop('app')->triggerEvent('onEasyshopAfterValidateCheckoutData', [&$data]);
		}
		catch (RuntimeException $e)
		{
			if ($throw)
			{
				throw $e;
			}
			else
			{
				$this->app->enqueueMessage($e->getMessage(), 'warning');
			}

			return false;
		}

		return $data;
	}

	protected function getForm()
	{
		/**
		 * @var Form                  $form
		 * @var EasyshopModelCheckout $model
		 */
		static $form = null;

		if (null === $form)
		{
			$model = easyshop('model', 'Checkout');
			$form  = $model->getForm(false);
		}

		if (!$form)
		{
			$this->app->enqueueMessage($model->getError(), 'error');

			return false;
		}

		return $form;
	}

	protected function loadLayout($layout = 'checkout')
	{
		$controller = BaseController::getInstance('Easyshop', ['base_path' => ES_COMPONENT_SITE]);
		$view       = $controller->getView('Cart', 'html', 'EasyshopView', ['base_path' => ES_COMPONENT_SITE . '/views']);
		$view->setLayout($layout);
		ob_end_clean();
		ob_start();
		$view->display();

		return ob_get_clean();
	}

	public function editAddress()
	{
		try
		{
			if (!Session::checkToken('get'))
			{
				throw new RuntimeException(Text::_('JINVALID_TOKEN'));
			}

			$this->cart->setCheckoutData(['layout' => 'checkout'], true);
			$doc      = easyshop('doc');
			$response = [
				'pushState' => [
					'url'   => Route::_(EasyshopHelperRoute::getCartRoute('checkout'), false),
					'title' => Text::_('COM_EASYSHOP_PAGE_CHECKOUT_ADDRESS_TITLE'),
				],
				'html'      => $this->loadLayout(),
			];

			$response['_scripts']     = $doc->_scripts;
			$response['_script']      = $doc->_script;
			$response['_styleSheets'] = $doc->_styleSheets;
			$response['_style']       = $doc->_style;

		}
		catch (RuntimeException $e)
		{
			$response = $e;
		}

		echo new JsonResponse($response);
		$this->app->close();
	}

	public function guest()
	{
		$this->checkToken('post');
		$return = base64_decode($this->app->input->post->getBase64('return'));
		$form   = $this->app->input->post->get('jform', [], 'array');

		if (easyshop('config', 'guest_checkout', 1))
		{
			if (empty($form['guest']['email']) || !filter_var($form['guest']['email'], FILTER_VALIDATE_EMAIL))
			{
				$this->app->enqueueMessage(Text::_('COM_EASYSHOP_ERROR_INVALID_EMAIL'), 'error');
			}
			else
			{
				$data = ['guest' => $form['guest']];
				$this->cart->setCheckoutData($data, true);
			}

			if (!empty($data['guest']['email']))
			{
				$return = Route::_(EasyshopHelperRoute::getCartRoute('checkout'));
			}
		}

		$this->app->redirect($return);
	}

	public function finish()
	{
		/**
		 * @var $app            CMSApplication
		 * @var $form           Form
		 * @var $currency       Currency
		 * @var $model          EasyshopModelCheckout
		 * @var $order          Order
		 * @var $emailClass     Email
		 * @var $customField    CustomField
		 * @var $paymentHandler Payment
		 * @var $utility        Utility
		 */
		$this->checkToken();
		$order          = easyshop(Order::class);
		$currency       = easyshop(Currency::class)->getActive();
		$user           = CMSFactory::getUser();
		$app            = easyshop('app');
		$redirect       = Route::_(EasyshopHelperRoute::getCartRoute('confirm'), false);
		$extractData    = null;
		$paymentHandler = null;

		try
		{
			$groups = ['billing_address', 'shipping_address', 'checkoutFields', 'confirm'];
			$data   = $this->validate($groups, true);
			$form   = $this->getForm();

			foreach ($groups as $groupField)
			{
				if ($fields = $form->getGroup($groupField))
				{
					foreach ($fields as $field)
					{
						$fieldName     = $field->getAttribute('name');
						$validateRegex = $field->getAttribute('validate_regex_pattern');
						$fieldValue    = isset($data[$groupField][$fieldName]) ? $data[$groupField][$fieldName] : '';

						if (!empty($validateRegex) && !@preg_match('/' . $validateRegex . '/', $fieldValue))
						{
							$regexMessage = $field->getAttribute('validate_regex_message');
							throw new RuntimeException($regexMessage ?: Text::_('COM_EASYSHOP_INPUT_INVALID_REGEX'));
						}
					}
				}
			}

			if (easyshop('config', 'terms_and_conditions', 1) && empty($data['confirm']['terms_and_conditions']))
			{
				throw new RuntimeException(Text::_('COM_EASYSHOP_TERMS_AND_CONDITIONS_CONFIRM_WARNING'));
			}

			if (empty($data['billing_address']) || empty($data['shipping_address']))
			{
				throw new RuntimeException(Text::_('COM_EASYSHOP_ERROR_INVALID_ADDRESS'));
			}

			easyshop('state')->set('checkoutStep', 'finishing');

			if (null === $extractData)
			{
				$extractData = $this->cart->extractData();
			}

			if ((int) $extractData['count'] < 1)
			{
				throw new RuntimeException(Text::_('COM_EASYSHOP_YOUR_CART_EMPTY'));
			}

			$paymentMethods  = $this->cart->getPaymentMethods();
			$shippingMethods = $this->cart->getShippingMethods();
			$app->triggerEvent('onEasyshopCheckoutPrepareCartData', [&$extractData]);

			if (!($payment = $this->cart->getPaymentMethods(true)) && $paymentMethods)
			{
				throw new RuntimeException(Text::_('COM_EASYSHOP_ERROR_NO_PAYMENT'));
			}

			if (is_object($payment))
			{
				/** @var stdClass $payment */
				$payment->data = [];

				if (!empty($payment->cardForm))
				{
					$postData      = $app->input->get('jform', [], 'array');
					$payment->card = [];
					$card          = [
						'holderName'  => 'card_holder_name_' . $payment->id,
						'number'      => 'card_number_' . $payment->id,
						'expiryMonth' => 'card_expiry_month_' . $payment->id,
						'expiryYear'  => 'card_expiry_year_' . $payment->id,
						'cvv'         => 'card_cvv_' . $payment->id,
					];

					foreach ($card as $name => $request)
					{
						$payment->card[$name] = isset($postData[$request]) ? $postData[$request] : '';
					}
				}

				PluginHelper::importPlugin('easyshoppayment', $payment->element);
				$paymentHandler = Payment::getHandler($payment->element);

				if (!($paymentHandler instanceof Payment))
				{
					throw new RuntimeException(Text::_('COM_EASYSHOP_ERROR_INVALID_PAYMENT_HANDLER'));
				}

				$currencies   = $paymentHandler->getCurrencies();
				$currencyCode = strtoupper($currency->get('code', ''));

				if ($currencies && !in_array($currencyCode, array_map('strtoupper', $currencies), true))
				{
					throw new RuntimeException(Text::sprintf('COM_EASYSHOP_ERROR_INVALID_PAYMENT_CURRENCY_FORMAT', $payment->name, $currencyCode));
				}

				$payment->data   = isset($jform['paymentData'][$payment->id]) ? $jform['paymentData'][$payment->id] : [];
				$clonePayment    = clone $payment;
				$paymentValidate = $paymentHandler->validate($clonePayment);

				if (false === $paymentValidate)
				{
					throw new RuntimeException(Text::_('COM_EASYSHOP_ERROR_PAYMENT_VALIDATE_FAIL'));
				}
			}

			if (!($shipping = $this->cart->getShippingMethods(true)) && $shippingMethods)
			{
				throw new RuntimeException(Text::_('COM_EASYSHOP_ERROR_NO_SHIPPING'));
			}

			$order->setCurrency($currency);
			$utility   = easyshop(Utility::class);
			$orderData = [
				'shipping_id'           => $shipping ? (int) $shipping->id : 0,
				'payment_id'            => $payment ? (int) $payment->id : 0,
				'billing_address'       => $data['billing_address'],
				'shipping_address'      => $data['shipping_address'],
				'checkoutFields'        => isset($data['checkoutFields']) ? $data['checkoutFields'] : [],
				'checkoutFieldsDetails' => isset($data['checkoutFieldsDetails']) ? $data['checkoutFieldsDetails'] : [],
				'state'                 => 0, // 0 => new, 1 => confirm, 2 => processed, 3 => shipped, 4 => succeed, 5 => cancelled, -2 => trash. Will be applied by admin or payment handler
				'payment_status'        => 0,  // 0 => unpaid, 1 => paid, 2 => refund. Will be applied by payment plugin or payment handler
				'total_shipping'        => $shipping ? $currency->convert($shipping->total) : 0.00,
				'total_paid'            => 0.00,// Will be applied by payment plugin or admin in the back-end
				'currency_id'           => $currency->get('id'),
				'items'                 => $extractData['items'],
				'total_discount'        => $currency->convert($extractData['orderDiscount']),
				'total_price'           => $currency->convert($extractData['grandTotal']),
				'total_taxes'           => $currency->convert($extractData['totalTaxes']),
				'total_fee'             => $currency->convert($extractData['paymentFee']),
				'discounts'             => $extractData['discounts'],
				'language'              => CMSFactory::getLanguage()->getTag(),
				'ip'                    => $utility->getClientIp(),
			];

			if (isset($data['note']))
			{
				$orderData['note'] = InputFilter::getInstance()->clean($data['note'], 'STRING');
			}

			Table::addIncludePath(ES_COMPONENT_ADMINISTRATOR . '/tables');
			$userTable = Table::getInstance('User', 'EasyshopTable');

			if ($user->id)
			{
				if ($userTable->load(['user_id' => $user->id]))
				{
					$orderData['user_id'] = $userTable->get('id');
				}

				$orderData['user_email'] = $user->get('email');
			}
			else
			{
				$customerInfo = (array) $data['customerInfo'];
				$customerForm = $this->getCustomerForm(['registration' => $customerInfo]);

				if ($customerForm instanceof RuntimeException)
				{
					throw $customerForm;
				}

				$orderData['user_email'] = $customerForm->getValue('email', 'registration');

				if (!filter_var($orderData['user_email'], FILTER_VALIDATE_EMAIL))
				{
					throw new RuntimeException(Text::_('COM_EASYSHOP_ERROR_USER_EMAIL'));
				}

				if ($customerForm->getValue('register', 'registration'))
				{
					$registerUserData = [
						'email1'       => $customerInfo['email'],
						'username'     => $customerInfo['email'],
						'password1'    => $customerInfo['password1'],
						'customfields' => $data['billing_address'],
					];
					$this->getModel('Customer', 'EasyshopModel')->register($registerUserData, true);

					if (!empty($registerUserData['id']) && $userTable->load(['user_id' => $registerUserData['id']]))
					{
						$orderData['user_id'] = $userTable->get('id');
						$app->login(
							[
								'username' => $registerUserData['username'],
								'password' => $registerUserData['password1'],
							]
						);
					}
				}
			}

			$app->triggerEvent('onEasyshopCheckoutPrepareOrderData', [$order, &$orderData]);

			if (!$order->save($orderData))
			{
				if (!empty($registerUserData['id']))
				{
					// Remove user
					User::getInstance($registerUserData['id'])->delete();
				}

				throw new RuntimeException(Text::_('COM_EASYSHOP_ORDER_SAVE_FAIL_MESSAGE'));
			}
			// Fire the event before payment
			$message = trim(implode(PHP_EOL, $app->triggerEvent('onEasyshopOrderCreated', [$order])));

			if ($paymentHandler instanceof Payment)
			{
				if ($paymentHandler->loadOrder($order, $payment->data))
				{
					try
					{
						$paymentReturn = $paymentHandler->execute();

						if (!empty($paymentReturn) && is_string($paymentReturn))
						{
							$message .= trim($paymentReturn);
						}
					}
					catch (Exception $paymentException)
					{
						$app->enqueueMessage($paymentException->getMessage(), 'warning');
					}
				}
			}

			// Email sending
			$emailClass = easyshop(Email::class);
			$emailClass->sendOn('[ON_NEW_ORDER]', $order->getLayoutData(), $order);
			$data['finalData'] = easyshop('renderer')->render('checkout.message.success',
				[
					'order'   => $order,
					'message' => $message,
				]
			);

			// Update coupon limit
			$order->updateCouponLimit($order->id, $order->state, $order->payment_status);

			if (empty($data['finalData']))
			{
				$data['finalData'] = '<div></div>';
				$app->enqueueMessage(Text::sprintf('COM_EASYSHOP_CHECKOUT_SUCCESS_MESSAGE', $orderData['order_code']), 'success');
			}

			// Fire the event after render success message and redirect to the finished page.
			$app->triggerEvent('onEasyshopOrderFinished', [$order, &$data['finalData']]);
			$this->cart->setCheckoutData($data);

		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'warning');
		}

		$app->redirect($redirect);
	}

	public function addShipping()
	{
		$this->addMethod('shipping');
	}

	protected function addMethod($type)
	{
		/** @var $methodClass Method */
		$methodClass = easyshop(Method::class);
		$methodId    = (int) $this->input->post->getInt($type . 'Id', 0);
		$found       = false;
		$func        = 'get' . ucfirst($type) . 'Methods';

		try
		{
			foreach ($methodClass->$func() as $method)
			{
				if ((int) $method->id === $methodId)
				{
					$found = true;
					break;
				}
			}

			if (!$found)
			{
				throw new RuntimeException(Text::_('COM_EASYSHOP_ERROR_INVALID_' . strtoupper($type) . '_ID'));
			}

			$this->cart->setCheckoutData([$type . '_id' => $methodId], true);
			$response = $this->loadLayout('confirm');
		}
		catch (RuntimeException $e)
		{
			$response = $e;
		}

		echo new JsonResponse($response);
		$this->app->close();
	}

	public function addPayment()
	{
		$this->addMethod('payment');
	}
}
