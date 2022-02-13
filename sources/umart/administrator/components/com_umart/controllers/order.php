<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\Addon;
use Umart\Classes\Currency;
use Umart\Classes\CustomField;
use Umart\Classes\Log;
use Umart\Classes\Order;
use Umart\Classes\Product;
use Umart\Classes\Renderer;
use Umart\Classes\Tax;
use Umart\Classes\User;
use Umart\Classes\Utility;
use Umart\Controller\FormController;
use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

class UmartControllerOrder extends FormController
{
	public function editPanel()
	{
		/**
		 * @var $model        UmartModelOrder
		 * @var $order        Order
		 * @var $utility      Utility
		 * @var $customField  CustomField
		 * @var $renderer     Renderer
		 * @var $currency     Currency
		 * @var $log          Log
		 */
		$doc               = plg_sytem_umart_main('doc');
		$doc->_scripts     = [];
		$doc->_script      = [];
		$doc->_styleSheets = [];
		$doc->_style       = [];
		$orderId           = (int) $this->input->post->getInt('orderId');
		$type              = $this->input->post->getWord('type');
		$panel             = strtolower($this->input->post->getString('panel'));
		$order             = plg_sytem_umart_main(Order::class);
		$renderer          = plg_sytem_umart_main('renderer');
		$model             = $this->getModel('Order', 'UmartModel');
		$renderer->setPaths([
			JPATH_THEMES . '/' . plg_sytem_umart_main('app')->getTemplate() . '/html/com_umart/layouts',
			UMART_COMPONENT_SITE . '/layouts',
			UMART_COMPONENT_ADMINISTRATOR . '/layouts',
		]);

		try
		{
			if (!parent::allowEdit())
			{
				throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'));
			}

			if (!$order->load($orderId))
			{
				throw new RuntimeException(Text::_('COM_UMART_ERROR_ORDER_NOT_FOUND'));
			}

			if (!in_array($type, ['edit', 'save']))
			{
				throw new RuntimeException(Text::_('COM_UMART_ERROR_INVALID_REQUEST'));
			}

			$model->setState('order.id', $orderId);
			$table = $order->getTable();
			$form  = $model->getForm([], true);

			if ($type == 'edit')
			{
				$html = '';

				switch ($panel)
				{
					case 'general':
					case 'payment':
						$html .= $renderer->render('form.fields', [
							'fields' => $form->getFieldset($panel),
						]);
						break;

					case 'billing':
					case 'shipping':
					case 'checkout':
						$html .= $renderer->render('form.fields', [
							'fields' => $form->getGroup($panel),
						]);
						break;
				}

				$response['html'] = $html;
			}
			else
			{
				$utility  = plg_sytem_umart_main(Utility::class);
				$data     = $this->input->post->get('data', [], 'array');
				$data     = array_merge($form->getData()->toArray(), $utility->serializeToArrayData($data));
				$log      = plg_sytem_umart_main(Log::class);
				$username = CMSFactory::getUser()->username;

				if (in_array($panel, ['billing', 'shipping', 'checkout']))
				{
					$data = $this->validateData($data, $model, $form, $panel);

					if ($panel == 'checkout')
					{
						$previousData = $order->getCheckoutFields();
						$order->saveCheckoutFields($data[$panel]);
						$modifiedData = $order->getCheckoutFields();
						$log->addEntry('com_umart.order', 'COM_UMART_LOG_ORDER_CHANGE_CHECKOUT_FIELD', [$order->order_code, $username], $previousData, $modifiedData);
					}
					else
					{
						$previousData = $order->getAddress();
						$order->saveAddress($panel, $data[$panel]);
						$modifiedData = $order->getAddress();
						$previousData = $utility->formatAddress($previousData[$panel]);
						$modifiedData = $utility->formatAddress($modifiedData[$panel]);
						$log->addEntry('com_umart.order', 'COM_UMART_LOG_ORDER_CHANGE_ADDRESS', [$panel, $order->order_code, $username], $previousData, $modifiedData);
					}
				}
				elseif (in_array($panel, ['general', 'payment']))
				{
					$validData    = [];
					$data         = $this->validateData($data, $model, $form);
					$previousData = $table->getProperties();

					foreach ($form->getFieldset($panel) as $field)
					{
						$name = $field->getAttribute('name');

						if (isset($data[$name]))
						{
							$validData[$name] = $data[$name];
						}
					}

					$table->bind($validData, ['order_code']);

					if (!$table->store())
					{
						throw new RuntimeException($table->getError());
					}

					// Reload order
					$order->load($orderId);
					$order->getProductDetails();
					$modifiedData = $order->getTable()->getProperties();
					$log->addEntry('com_umart.order', 'COM_UMART_LOG_ORDER_CHANGE', [$panel, $order->order_code, $username], $previousData, $modifiedData);

					// Update coupon
					$order->updateCouponLimit($orderId, $table->state, $table->payment_status);
				}

				$this->loadResponseHTML($response, $orderId);
			}

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

		plg_sytem_umart_main('app')->close();
	}

	protected function validateData($data, $model, $form, $group = null)
	{
		/** @var  FormModel $model */
		$validData = $model->validate($form, $data, $group);

		if (false === $validData)
		{
			$errors = $model->getErrors();

			foreach ($errors as $error)
			{
				if ($error instanceof Exception)
				{
					throw new RuntimeException($error->getMessage());
				}
				else
				{
					throw new RuntimeException($error);
				}
			}
		}

		return $validData;
	}

	protected function loadResponseHTML(&$response, $id, $model = null)
	{
		$view = $this->getView('Order', 'html', 'UmartView');

		if (!($model instanceof UmartModelOrder))
		{
			$model = $this->getModel('Order', 'UmartModel');
			$model->setState('order.id', $id);
		}

		$view->setModel($model, true);
		$view->setLayout('edit');
		$view->addTemplatePath(UMART_COMPONENT_ADMINISTRATOR . '/templates/default/order');
		ob_start();
		$view->display();
		$response['html'] = ob_get_clean();
	}

	public function removeProduct()
	{
		/**
		 * @var $order Order
		 * @var $log   Log
		 */
		$order          = plg_sytem_umart_main(Order::class);
		$log            = plg_sytem_umart_main(Log::class);
		$orderProductId = (int) $this->input->post->getInt('orderProductId', 0);

		try
		{
			if (!plg_sytem_umart_main(User::class)->core('delete'))
			{
				throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'));
			}

			$db    = plg_sytem_umart_main('db');
			$query = $db->getQuery(true)
				->select('a.order_id, a.product_name')
				->from($db->quoteName('#__umart_order_products', 'a'))
				->where('a.id = ' . $orderProductId);
			$db->setQuery($query);
			$row = $db->loadObject();

			if (!$row || !$order->load($row->order_id))
			{
				throw new RuntimeException(Text::_('COM_UMART_ERROR_ORDER_NOT_FOUND'));
			}

			$previousData = json_encode($order->products);
			$order        = $order->removeProduct((int) $this->input->post->getInt('orderProductId', 0), $row->order_id);
			$this->loadResponseHTML($response, $order->id);
			$modifiedData = json_encode($order->products);
			$user         = CMSFactory::getUser();
			$userName     = $user->name . ' (' . $user->username . ')';
			$log->addEntry('com_umart.order', 'COM_UMART_LOG_USER_REMOVE_PRODUCT_OF_ORDER', [$userName, $row->product_name, $order->order_code], $previousData, $modifiedData);
		}
		catch (RuntimeException $e)
		{
			$response = $e;
		}

		echo new JsonResponse($response);

		plg_sytem_umart_main('app')->close();
	}

	public function updateFieldPrice()
	{
		/**
		 * @var Order       $order
		 * @var CustomField $customField
		 * @var Tax         $taxClass
		 */

		try
		{
			if (!parent::allowEdit())
			{
				throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'));
			}

			$order       = plg_sytem_umart_main(Order::class);
			$customField = plg_sytem_umart_main(CustomField::class, ['reflector' => 'com_umart.checkout']);
			$orderId     = (int) $this->input->post->get('orderId', 0, 'uint');
			$fieldId     = (int) $this->input->post->get('fieldId', 0, 'uint');
			$price       = (float) $this->input->post->get('price', 0.00, 'float');

			if ($order->load($orderId)
				&& ($fieldsDetails = $order->get('fieldsPriceDetails', []))
				&& isset($fieldsDetails[$fieldId])
				&& ($field = $customField->findField($fieldId))
			)
			{
				$db           = CMSFactory::getDbo();
				$previousData = (float) $fieldsDetails[$fieldId]->price;
				$fieldParams  = new Registry($field->params);
				$tax          = plg_sytem_umart_main(Tax::class)->calculate($fieldParams->get('taxes', []), $price);
				$query        = $db->getQuery(true)
					->update($db->quoteName('#__umart_order_field_price_xref'))
					->set($db->quoteName('price') . ' = ' . $price)
					->set($db->quoteName('tax') . ' = ' . $tax)
					->where($db->quoteName('orderId') . ' = ' . $orderId)
					->where($db->quoteName('fieldId') . ' = ' . $fieldId);
				$db->setQuery($query)
					->execute();

				if ($previousData != $price)
				{
					// Reload order
					$order->load($orderId);
					$order->getProductDetails();
					$user     = CMSFactory::getUser();
					$userName = $user->name . ' (' . $user->username . ')';
					plg_sytem_umart_main(Log::class)->addEntry('com_umart.order', 'COM_UMART_LOG_USER_UPDATE_ORDER_FIELD_PRICE', [$userName, $previousData, $order->currency->toFormat($price), $order->order_code], $previousData, $price);
				}
			}

			$this->loadResponseHTML($response, $orderId);
		}
		catch (RuntimeException $e)
		{
			$response = $e;
		}

		echo new JsonResponse($response);

		plg_sytem_umart_main('app')->close();
	}

	public function addProduct()
	{
		/**
		 * @var $order            Order
		 * @var $log              Log
		 * @var $layoutHelper     Renderer
		 * @var $currency         Currency
		 * @var $utility          Utility
		 * @var $customField      CustomField
		 */

		$orderId        = (int) $this->input->post->getInt('orderId', 0);
		$productId      = (int) $this->input->post->getInt('productId', 0);
		$orderProductId = (int) $this->input->post->getInt('orderProductId', 0);
		$quantity       = (int) $this->input->post->getInt('quantity', 1);
		$price          = (float) $this->input->post->getFloat('price', 0.00);
		$taxes          = (float) $this->input->post->getFloat('taxes', 0.00);
		$name           = trim($this->input->post->getString('name', ''));
		$optStr         = trim($this->input->post->getString('options', ''));
		$options        = [];

		if (!empty($optStr))
		{
			parse_str($optStr, $optionArray);

			if (!empty($optionArray['product_option']))
			{
				$customField = plg_sytem_umart_main(CustomField::class, [
					'reflector' => 'com_umart.product.option',
				]);

				foreach ($optionArray['product_option'] as $optionId => $value)
				{
					$optionId = (int) $optionId;

					if ($field = $customField->findField($optionId))
					{
						if (empty($optionArray['product_option_price'][$optionId]))
						{
							$optionPrice = 0.00;
						}
						else
						{
							$optionPrice = (float) floatval($optionArray['product_option_price'][$optionId]);
						}

						$value = is_array($value) ? '[' . implode('][', $value) . ']' : trim($value);
						$text  = $value;

						if (in_array($field->type, ['dropdown', 'list', 'radio']))
						{
							$params = new Registry($field->params);

							foreach ($params->get('options', []) as $param)
							{
								if (@$param->value == $value)
								{
									$text = @$param->text;
									break;
								}
							}

						}
						elseif ($field->type == 'checkbox')
						{
							$text = Text::_(trim($field->name));
						}

						$options[] = [
							'option_id'    => $optionId,
							'option_name'  => $field->name,
							'option_text'  => $text,
							'option_value' => $value,
							'option_price' => $optionPrice,
						];
					}
				}
			}
		}

		try
		{
			if (!parent::allowAdd())
			{
				throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'));
			}

			$log   = plg_sytem_umart_main(Log::class);
			$order = plg_sytem_umart_main(Order::class);
			$order->load($orderId);
			$previousData = json_encode($order->products);
			$order->addProduct([
				'id'               => $orderProductId > 0 ? $orderProductId : 0,
				'order_id'         => $orderId,
				'product_id'       => $productId,
				'product_name'     => $name,
				'product_price'    => $price,
				'product_taxes'    => $taxes,
				'product_shipping' => 0.00,
				'quantity'         => $quantity,
			], $options);
			$modifiedData = json_encode($order->products);
			$this->loadResponseHTML($response, $orderId);
			$user     = CMSFactory::getUser();
			$userName = $user->name . ' (' . $user->username . ')';
			$log->addEntry('com_umart.order', 'COM_UMART_LOG_USER_ADD_PRODUCT_INTO_ORDER', [$userName, $name, $order->order_code], $previousData, $modifiedData);

		}
		catch (RuntimeException $e)
		{
			$response = $e;
		}

		echo new JsonResponse($response);

		plg_sytem_umart_main('app')->close();
	}

	public function loadProduct()
	{
		/**
		 * @var $order             Order
		 * @var $productClass      Product
		 * @var $currencyClass     Currency
		 */

		$productId = (int) $this->input->post->getInt('productId', 0);
		$orderId   = (int) $this->input->post->getInt('orderId', 0);
		$type      = trim($this->input->post->getWord('type', 'add'));
		$order     = plg_sytem_umart_main(Order::class);

		try
		{
			if (!$order->load($orderId))
			{
				throw new RuntimeException(Text::sprintf('COM_UMART_ERROR_ORDER_NOT_FOUND', $orderId));
			}

			$productClass  = plg_sytem_umart_main(Product::class);
			$currencyClass = plg_sytem_umart_main(Currency::class);

			if ($type === 'edit')
			{
				foreach ($order->products as $orderProduct)
				{
					if ((int) $orderProduct->order_product_id === $productId)
					{
						if ($product = $productClass->getItem($orderProduct->product_id))
						{
							if (!empty($orderProduct->options))
							{
								$cart = [];

								foreach ($orderProduct->options as $option)
								{
									$cart['options'][$option->option_id]['value']  = $option->option_value;
									$cart['options'][$option->option_id]['prefix'] = $option->option_price;
								}

								$product->cart = $cart;
							}

							$productClass->loadOptions($product, 'order.product.option');
							$response = plg_sytem_umart_main('renderer')->render('order.product.row', [
								'id'             => $orderProduct->product_id,
								'name'           => $orderProduct->product_name,
								'price'          => $orderProduct->product_price,
								'taxes'          => $orderProduct->product_taxes,
								'options'        => $product->options,
								'quantity'       => $orderProduct->quantity,
								'orderProductId' => $orderProduct->order_product_id,
							]);
						}

						break;
					}
				}
			}
			else
			{
				$currencyClass->load($order->currency_id);

				if ($product = $productClass->getItem($productId))
				{
					$productClass->loadOptions($product, 'order.product.option');
					$response = plg_sytem_umart_main('renderer')->render('order.product.row', [
						'id'             => $product->id,
						'name'           => $product->name,
						'price'          => $product->price,
						'taxes'          => $productClass->getTotalTaxes($product),
						'options'        => $product->options,
						'quantity'       => 1,
						'orderProductId' => 0,
					]);
				}
			}


			if (!isset($response))
			{
				throw new RuntimeException(Text::_('COM_UMART_PRODUCT_NOT_FOUND'));
			}
		}
		catch (RuntimeException $e)
		{
			$response = $e;
		}

		echo new JsonResponse($response);

		plg_sytem_umart_main('app')->close();

	}

	public function createNew()
	{
		$this->checkToken();

		if (!parent::allowAdd())
		{
			throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		/**
		 * @var $currency    Currency
		 * @var $order       Order
		 * @var $user        User
		 */

		$currency   = plg_sytem_umart_main(Currency::class);
		$data       = $this->input->get('jform', [], 'array');
		$currencyId = isset($data['currency_id']) ? (int) $data['currency_id'] : 0;

		if (!$currency->isMultiMode() || !$currencyId)
		{
			$currencyId = $currency->getDefault()->get('id');
		}

		$model = $this->getModel('Order', 'UmartModel');
		$form  = $model->getForm([], false);

		try
		{
			if (!$form->validate($data, 'billing'))
			{
				$errors  = $form->getErrors();
				$message = [];

				for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
				{
					if ($errors[$i] instanceof Exception)
					{
						$message[] = $errors[$i]->getMessage();
					}
					else
					{
						$message[] = $errors[$i];
					}
				}

				throw new RuntimeException(implode(PHP_EOL, $message));
			}

			if (!$form->validate($data, 'shipping'))
			{
				$errors  = $form->getErrors();
				$message = [];

				for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
				{
					if ($errors[$i] instanceof Exception)
					{
						$message[] = $errors[$i]->getMessage();
					}
					else
					{
						$message[] = $errors[$i];
					}
				}

				throw new RuntimeException(implode(PHP_EOL, $message));
			}

			$table = Table::getInstance('Order', 'UmartTable');
			$table->set('currency_id', $currencyId);
			$user = plg_sytem_umart_main(User::class);

			if ($user->load($data['user_id']))
			{
				$table->set('user_email', $user->email);
				$table->set('user_id', $user->id);
			}

			if (!$table->store())
			{
				throw new RuntimeException(Text::_('COM_UMART_ERROR_CREATE_ORDER_FAIL'));
			}

			$orderId  = $table->get('id');
			$order    = plg_sytem_umart_main(Order::class);
			$filter   = $form->filter($data, 'billing');
			$billing  = $filter['billing'];
			$filter   = $form->filter($data, 'shipping');
			$shipping = $filter['shipping'];
			$order->load($orderId);
			$order->saveAddress('billing', $billing);
			$order->saveAddress('shipping', $shipping);
			plg_sytem_umart_main(Log::class)->addEntry('com_umart.order', 'COM_UMART_LOG_ORDER_CREATED', [$order->order_code, $user->name . '(' . $user->username . ')']);
			$redirectURL = Route::_('index.php?option=com_umart&view=order&layout=edit&id=' . $orderId, false);
		}
		catch (RuntimeException $e)
		{
			$redirectURL = Route::_('index.php?option=com_umart&view=orders', false);
			$this->setMessage($e->getMessage(), 'error');
		}

		$this->setRedirect($redirectURL)
			->redirect();
	}

	public function loadEmailTemplate()
	{
		/** @var Order $orderClass */
		$orderClass = plg_sytem_umart_main(Order::class);
		$emailTable = Table::getInstance('Email', 'UmartTable');
		$emailId    = (int) $this->input->post->getInt('emailId', 0);
		$orderId    = (int) $this->input->post->getInt('orderId', 0);

		try
		{
			if (!$emailId || !$emailTable->load($emailId))
			{
				throw new RuntimeException(Text::sprintf('COM_UMART_EMAIL_ID_NOT_LOAD_MESSAGE', $emailId));
			}

			$orderClass->load($orderId);
			$response    = [
				'send_from_name'  => $emailTable->send_from_name,
				'send_from_email' => $emailTable->send_from_email,
				'send_to_emails'  => $emailTable->send_to_emails,
				'send_subject'    => $emailTable->send_subject,
				'send_body'       => $emailTable->send_body,
				'addon'           => '',
			];
			$replaceData = $orderClass->getLayoutData();

			foreach ($replaceData as $key => $value)
			{
				if (stripos($response['send_to_emails'], '{CUSTOMER_EMAIL}') !== false)
				{
					$response['send_to_emails'] = str_ireplace('{CUSTOMER_EMAIL}', $replaceData['{CUSTOMER_EMAIL}'], $response['send_to_emails']);
				}

				if (stripos($response['send_body'], $key) !== false)
				{
					$response['send_body'] = str_ireplace($key, $value, $response['send_body']);
				}

				if (stripos($response['send_subject'], $key) !== false)
				{
					$response['send_subject'] = str_ireplace($key, $value, $response['send_subject']);
				}
			}

			Form::addFieldPath(UMART_COMPONENT_ADMINISTRATOR . '/models/fields');
			Form::addRulePath(UMART_COMPONENT_ADMINISTRATOR . '/models/rules');
			$addOns = plg_sytem_umart_main(Addon::class)->getAddons('email', $emailId);

			foreach ($addOns as $element => $addOnForm)
			{
				$groups = $addOnForm->getGroup('');

				if (count($groups))
				{
					$response['addon'] .= '<h4 class="uk-h5 uk-heading-bullet uk-margin-remove">'
						. Text::_('PLG_UMART_' . strtoupper($element) . '_ADDON_LABEL') . '</h4>';

					foreach ($groups as $field)
					{
						$response['addon'] .= $field->renderField();
					}
				}
			}

		}
		catch (RuntimeException $e)
		{
			$response = $e;
		}

		echo new JsonResponse($response);

		plg_sytem_umart_main('app')->close();
	}

	public function sendEmail()
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$data    = $this->input->post->get('jform', [], 'array');
		$orderId = $this->input->getInt('id');
		$order   = plg_sytem_umart_main(Order::class);

		try
		{
			if (empty($data['send_from_name'])
				|| empty($data['send_from_email'])
				|| empty($data['send_subject'])
				|| empty($data['send_body'])
				|| empty($data['send_to_emails'])
				|| !filter_var($data['send_from_email'], FILTER_VALIDATE_EMAIL)
				|| !$order->load($orderId)
			)
			{
				throw new RuntimeException(Text::_('COM_UMART_INVALID_DATA_MESSAGE'));
			}

			$recipients = [];
			$emails     = preg_split('/\r\n|\n|;/', trim($data['send_to_emails']));

			foreach ($emails as $email)
			{
				if (filter_var($email, FILTER_VALIDATE_EMAIL))
				{
					$recipients[] = $email;
				}
			}

			if (!count($recipients))
			{
				throw new RuntimeException(Text::_('COM_UMART_RECIPIENTS_COULD_NOT_EMPTY_MESSAGE'));
			}

			$mailer = CMSFactory::getMailer();
			$mailer->setFrom($data['send_from_email'], $data['send_from_name']);
			$mailer->addRecipient($recipients);
			$mailer->setSubject($data['send_subject']);
			$mailer->setBody($data['send_body']);
			$mailer->isHtml(true);
			$email          = (object) $data;
			$email->send_on = '[ON_DIRECT_ORDER]';
			$app            = plg_sytem_umart_main('app');
			$app->triggerEvent('onUmartBeforeSendEmail', [$mailer, $email, $order]);

			if ($mailer->Send())
			{
				$app->triggerEvent('onUmartAfterSendEmail', [$mailer, $email, $order]);
			}

			$this->setMessage(Text::sprintf('COM_UMART_SEND_EMAIL_SUCCESS_MESSAGE', join(';', $recipients)));
		}
		catch (RuntimeException $e)
		{
			$this->setMessage($e->getMessage(), 'error');
		}

		$this->setRedirect(Route::_('index.php?option=com_umart&view=order&layout=edit&id=' . $orderId, false));
	}

	public function loadUserFieldData()
	{
		try
		{
			if (!Session::checkToken())
			{
				throw new RuntimeException(Text::_('JINVALID_TOKEN'));
			}

			$doc               = plg_sytem_umart_main('doc');
			$doc->_scripts     = [];
			$doc->_script      = [];
			$doc->_styleSheets = [];
			$doc->_style       = [];

			/** @var $customField CustomField */
			$customField = plg_sytem_umart_main(CustomField::class, [
				'reflector' => 'com_umart.user',
			]);
			$model       = $this->getModel('User', 'UmartModel');
			$userId      = (int) $this->input->getInt('userId', 0);

			if ($userId < 0)
			{
				throw new RuntimeException(Text::_('COM_UMART_USER_NOT_FOUND'));
			}

			$model->setState('user.id', $userId);
			$form     = $model->getForm();
			$response = [
				'billing'  => '',
				'shipping' => '',
			];

			foreach ($form->getGroup('customfields') as $field)
			{
				$name  = $field->getAttribute('name');
				$type  = strtolower($field->__get('type'));
				$class = $field->__get('class');

				switch ($type)
				{
					case 'text':
					case 'email':
						$field->__set('class', 'uk-input ' . $class);
						break;

					case 'textarea':
						$field->__set('class', 'uk-textarea ' . $class);
						break;
				}

				if (($userField = $customField->findField($name))
					&& $userField->checkout_field
				)
				{

					$field->id           .= '_billing';
					$response['billing'] .= str_replace('jform[customfields][' . $name . ']', 'jform[billing][' . $name . ']', $field->renderField());

					$field->id            = str_replace('_billing', '_shipping', $field->id);
					$response['shipping'] .= str_replace('jform[customfields][' . $name . ']', 'jform[shipping][' . $name . ']', $field->renderField());
				}
			}

			$response['_scripts']     = $doc->_scripts;
			$response['_script']      = $doc->_script;
			$response['_styleSheets'] = $doc->_styleSheets;
			$response['_style']       = $doc->_style;

		}
		catch (RuntimeException $e)
		{
			$response = $e;
		}

		echo new JResponseJson($response);

		plg_sytem_umart_main('app')->close();
	}

	public function edit($key = null, $urlVar = null)
	{
		if ($edit = parent::edit($key, $urlVar))
		{
			$id    = (int) $this->input->getInt('id', 0);
			$table = Table::getInstance('Order', 'UmartTable');

			if ($id > 0 && $table->load($id))
			{
				$table->set('viewed', 1);
				$table->store();
			}
		}

		return $edit;
	}
}
