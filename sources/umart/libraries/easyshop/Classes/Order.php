<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ES\Classes;
defined('_JEXEC') or die;

use JDatabaseDriver;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use RuntimeException;

class Order
{
	protected $address = null;
	protected $products = null;
	protected $checkoutFields = null;
	protected $customerName = null;
	protected $table = null;
	protected $fieldsPriceDetails = null;
	protected $subTotal = 0.00;

	/**
	 * @var Currency $currency
	 * @since 1.0.0
	 */
	protected $currency = null;

	public function __construct()
	{
		Table::addIncludePath(ES_COMPONENT_ADMINISTRATOR . '/tables');
		$this->table = Table::getInstance('Order', 'EasyshopTable');
	}

	public function getCheckoutFields($orderId = 0)
	{
		/** @var CustomField $customField */
		$customField = easyshop(
			CustomField::class,
			[
				'reflector' => 'com_easyshop.checkout',
			]
		);

		return $customField->getValues($orderId ?: $this->getId(), true);
	}

	public function getId()
	{
		$orderId = (int) $this->get('id', 0);

		if (!$orderId)
		{
			throw new RuntimeException(Text::sprintf('COM_EASYSHOP_ERROR_ORDER_NOT_FOUND', $orderId));
		}

		return $orderId;
	}

	public function get($name, $default = null)
	{
		$value = $this->__get($name);

		return $value === null ? $default : $value;
	}

	public function __get($name)
	{
		switch ($name)
		{
			case 'address':
			case 'products':
			case 'checkoutFields':
			case 'fieldsPriceDetails':

				if (null === $this->{$name})
				{
					$callBack = [
						'address'            => 'getAddress',
						'products'           => 'getProductDetails',
						'checkoutFields'     => 'getCheckoutFields',
						'fieldsPriceDetails' => 'getFieldsPriceDetails',
					];

					$this->{$name} = call_user_func_array([$this, $callBack[$name]], [$this->getId()]) ?: [];
				}

				break;

			case 'customerName':

				if (null === $this->customerName)
				{
					$orderId = $this->getId();

					if (empty($this->address['billing']))
					{
						$this->address = $this->getAddress($orderId);
					}

					$billing     = $this->address['billing'];
					$customField = easyshop(CustomField::class, [
						'reflector'    => 'com_easyshop.user',
						'reflector_id' => $orderId,
					]);

					$fieldName = $customField->findFieldByName('user_name');

					if ($fieldName && isset($billing[$fieldName->id]))
					{
						$this->customerName = $billing[$fieldName->id]->display;
					}
				}

				break;

			case 'currency':

				if ($currencyId = $this->get('currency_id', 0))
				{
					return easyshop(Currency::class)->load($currencyId);
				}

				return easyshop(Currency::class)->getDefault();
		}

		if (property_exists($this->table, $name))
		{
			return $this->table->{$name};
		}

		return property_exists($this, $name) ? $this->{$name} : null;
	}

	public function getAddress($orderId = 0)
	{
		if (!$orderId)
		{
			$orderId = $this->getId();
		}
		/** @var $customField CustomField */
		$customField = easyshop(CustomField::class, [
			'reflector' => 'com_easyshop.order.billing_address',
		]);
		$billing     = $customField->getValues($orderId, true);
		$customField = easyshop(CustomField::class, [
			'reflector' => 'com_easyshop.order.shipping_address',
		]);
		$shipping    = $customField->getValues($orderId, true);

		return [
			'billing'  => $billing,
			'shipping' => $shipping,
		];
	}

	public function getFieldsPriceDetails($orderId = 0)
	{
		$db    = easyshop('db');
		$query = $db->getQuery(true)
			->select('a.fieldId, a.label, a.price, a.tax, (a.price + a.tax) AS totalAmount')
			->from($db->quoteName('#__easyshop_order_field_price_xref', 'a'))
			->where('a.orderId = ' . $db->quote($orderId ?: $this->getId()));

		return $db->setQuery($query)->loadObjectList('fieldId') ?: [];
	}

	public function save(&$data)
	{
		try
		{
			$isNew = empty($data['id']);

			if ($isNew)
			{
				$this->table->set('id', 0);
			}

			$this->table->reset();
			$this->table->bind($data);

			if ($result = $this->table->store())
			{
				$orderId = (int) $this->table->get('id');
				$this->saveAddress('billing', $data['billing_address']);
				$this->saveAddress('shipping', $data['shipping_address']);
				$db = easyshop('db');

				if (!empty($data['checkoutFields']))
				{
					if (!empty($data['checkoutFieldsDetails']))
					{
						$fieldsValues = [];

						foreach ($data['checkoutFieldsDetails'] as $fieldId => $fieldDetail)
						{
							if (isset($data['checkoutFields'][$fieldId]))
							{
								$fieldsValues[] = $orderId . ',' . (int) $fieldId . ',' . $db->quote($fieldDetail['label']) . ',' . (float) $fieldDetail['price'] . ',' . (float) $fieldDetail['tax'];
							}
						}

						if ($fieldsValues)
						{
							$this->saveFieldPriceXref($fieldsValues);
						}
					}

					$this->saveCheckoutFields($data['checkoutFields']);
				}

				$productQuery = $db->getQuery(true)
					->insert($db->quoteName('#__easyshop_order_products'))
					->columns(
						[
							'order_id',
							'product_id',
							'product_name',
							'product_taxes',
							'product_price',
							'product_discount_incl',
							'product_shipping',
							'quantity',
						]
					);
				$optionQuery  = $db->getQuery(true)
					->insert($db->quoteName('#__easyshop_order_product_options'))
					->columns(
						[
							'order_product_id',
							'option_id',
							'option_name',
							'option_text',
							'option_value',
							'option_price',
						]
					);

				foreach ($data['items'] as $item)
				{
					$shipping = 0.00;
					$productQuery->clear('values')
						->values(
							$orderId . ','
							. (int) $item['product']->id . ','
							. $db->quote($item['product']->name) . ','
							. $this->currency->convert((float) $item['saleTaxes']) . ','
							. $this->currency->convert((float) $item['salePrice']) . ','
							. $this->currency->convert((float) $item['discountAmount']) . ','
							. $shipping . ','
							. (int) $item['quantity']
						);

					if ($db->setQuery($productQuery)->execute() && !empty($item['options']))
					{
						$orderProductId = (int) $db->insertid();

						foreach ($item['options'] as $optionId => $option)
						{
							$optionQuery->clear('values')
								->values($orderProductId . ','
									. (int) $optionId . ','
									. $db->quote($option['name']) . ','
									. $db->quote($option['text']) . ','
									. $db->quote($option['value']) . ','
									. $this->currency->convert((float) $option['prefix'])
								);
							$db->setQuery($optionQuery)
								->execute();
						}
					}
				}

				if (!empty($data['discounts']))
				{
					$discountIds = [];

					foreach ($data['discounts'] as $discount)
					{
						if ((int) $discount->type === 1 && !in_array((int) $discount->id, $discountIds))
						{
							$discountIds[] = (int) $discount->id;
						}
					}

					if ($discountIds)
					{
						$query = $db->getQuery(true)
							->delete($db->quoteName('#__easyshop_order_coupons'))
							->where($db->quoteName('order_id') . ' = ' . $orderId);
						$db->setQuery($query)
							->execute();

						$query->clear()
							->insert($db->quoteName('#__easyshop_order_coupons'))
							->columns($db->quoteName(['order_id', 'coupon_id', 'handled']));

						foreach ($discountIds as $discountId)
						{
							$query->values($orderId . ', ' . $discountId . ', 0');
						}

						$db->setQuery($query)
							->execute();
					}
				}

				$data['order_id']   = $orderId;
				$data['order_code'] = $this->table->get('order_code');

				foreach ($this->table->getProperties() as $field => $value)
				{
					$this->{$field} = $value;
				}

				$this->reset();
			}
			else
			{
				throw new RuntimeException(implode(PHP_EOL, $this->table->getErrors()));
			}
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage());
		}

		$app = easyshop('app');
		$app->triggerEvent('onEasyshopAfterSave', ['com_easyshop.order', $this->table, $isNew, $data]);
		$app->triggerEvent('onEasyshopOrderAfterSave', [$this, $isNew]);

		return $result;
	}

	public function saveAddress($type, $address)
	{
		/** @var CustomField $customField */
		$orderId     = $this->getId();
		$customField = easyshop(CustomField::class,
			[
				'reflector'    => 'com_easyshop.order.' . $type . '_address',
				'reflector_id' => $orderId,
			]
		);

		$customField->save(
			[
				'customfields' => $address,
			]
		);
	}

	public function saveFieldPriceXref(array $fieldsValues)
	{
		$orderId = $this->getId();
		$db      = easyshop('db');

		// Wipe old values
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__easyshop_order_field_price_xref'))
			->where($db->quoteName('orderId') . ' = ' . $db->quote($orderId));
		$db->setQuery($query)
			->execute();

		if ($fieldsValues)
		{
			// Insert values
			$query->clear()
				->insert($db->quoteName('#__easyshop_order_field_price_xref'))
				->columns($db->quoteName(['orderId', 'fieldId', 'label', 'price', 'tax']))
				->values($fieldsValues);
			$db->setQuery($query)
				->execute();
		}

		return $this;

	}

	public function saveCheckoutFields($fieldsData)
	{
		/** @var CustomField $customField */
		$orderId     = $this->getId();
		$customField = easyshop(
			CustomField::class,
			[
				'reflector'    => 'com_easyshop.checkout',
				'reflector_id' => $orderId,
			]
		);

		$customField->save(['customfields' => $fieldsData]);
	}

	public function reset()
	{
		$this->address            = null;
		$this->products           = null;
		$this->checkoutFields     = null;
		$this->customerName       = null;
		$this->currency           = null;
		$this->fieldsPriceDetails = null;
		$this->subTotal           = 0.00;
	}

	public function removeProduct($orderProductId = 0, $orderId = 0)
	{
		$orderProductId = (int) $orderProductId;
		$orderId        = (int) $orderId;
		$db             = easyshop('db');
		$query          = $db->getQuery(true);

		if ($orderId < 1)
		{
			$query->select('a.order_id')
				->from($db->quoteName('#__easyshop_order_products', 'a'))
				->where('a.id = ' . $orderProductId);
			$db->setQuery($query);
			$orderId = $db->loadResult();
		}

		$query->clear()
			->delete($db->quoteName('#__easyshop_order_products'))
			->where($db->quoteName('id') . ' = ' . $orderProductId);
		$db->setQuery($query)
			->execute();

		$query->clear()
			->delete($db->quoteName('#__easyshop_order_product_options'))
			->where($db->quoteName('order_product_id') . ' = ' . $orderProductId);
		$db->setQuery($query)
			->execute();

		if ($orderId)
		{
			$this->load($orderId);
		}

		return $this;
	}

	public function load($orderKey)
	{
		$result = false;

		if (is_numeric($orderKey))
		{
			$orderKey = (int) $orderKey;

			if ($orderKey < 1)
			{
				return false;
			}
		}
		elseif (!is_array($orderKey))
		{
			return false;
		}

		if ($this->table->load($orderKey))
		{
			foreach ($this->table->getProperties() as $field => $value)
			{
				$this->{$field} = $value;
			}

			$result = true;

			// Reset protected properties which would be been callback by the magic method
			$this->reset();
		}

		return $result;
	}

	public function getProductDetails($orderId = 0)
	{
		if (!$orderId)
		{
			$orderId = $this->getId();
		}

		$db    = easyshop('db');
		$query = $db->getQuery(true)
			->select('a.id AS order_product_id, a.product_id, a.product_name, a.product_taxes, a.product_price, '
				. 'a.product_shipping, a.quantity, a.product_discount_incl, a2.vendor_id, a2.sku')
			->leftJoin($db->quoteName('#__easyshop_products', 'a2') . ' ON a2.id = a.product_id')
			->from($db->quoteName('#__easyshop_order_products', 'a'))
			->where('a.order_id = ' . (int) $orderId);
		$db->setQuery($query);
		$totalPrice = 0.00;
		$totalTaxes = 0.00;

		if ($products = $db->loadObjectList())
		{
			$subTotal = 0.00;

			foreach ($products as $product)
			{
				$product->quantity      = (int) $product->quantity;
				$product->product_price = (float) $product->product_price;
				$product->product_taxes = (float) $product->product_taxes;
				$product->total_price   = $product->product_price * $product->quantity;
				$subTotal               += $product->total_price;
				$totalTaxes             += $product->product_taxes * $product->quantity;
				$query->clear()
					->select('a.id, a.order_product_id, a.option_id, a.option_name, a.option_text, a.option_value, a.option_price')
					->from($db->quoteName('#__easyshop_order_product_options', 'a'))
					->where('a.order_product_id = ' . (int) $product->order_product_id);
				$db->setQuery($query);

				if ($options = $db->loadObjectList())
				{
					$product->options = $options;
				}
			}

			$this->subTotal = $subTotal;
			$totalPrice     = $subTotal + $totalTaxes;
		}

		$totalPrice    += (float) $this->get('extra_cost', 0.00);
		$totalTaxes    += (float) $this->get('extra_cost_taxes', 0.00);
		$totalShip     = (float) $this->get('total_shipping', 0.00);
		$totalFee      = (float) $this->get('total_fee', 0.00);
		$totalDiscount = (float) $this->get('total_discount', 0.00);
		$totalPrice    += $totalShip + $totalFee;
		$totalPrice    -= $totalDiscount;
		$methodMaps    = [
			'payment_id'  => $totalFee,
			'shipping_id' => $totalShip,
		];

		/**
		 * @var Method $methodClass
		 * @var Tax    $taxClass
		 */
		$methodClass = easyshop(Method::class);
		$taxClass    = easyshop(Tax::class);

		foreach (['payment_id', 'shipping_id'] as $methodKey)
		{
			$methodId = $this->get($methodKey, 0);

			if ($methodId && ($method = $methodClass->get($methodId)))
			{
				$taxAmount  = $taxClass->calculate($method->taxes, $methodMaps[$methodKey]);
				$totalTaxes += $taxAmount;
				$totalPrice += $taxAmount;
			}
		}

		// @since 1.3.6
		if ($fieldsPriceDetails = $this->get('fieldsPriceDetails'))
		{
			foreach ($fieldsPriceDetails as $fieldDetail)
			{
				$totalPrice += (float) $fieldDetail->totalAmount;
				$totalTaxes += (float) $fieldDetail->tax;
			}
		}

		// Update Price and Taxes (maybe)
		if ($totalPrice != (float) $this->get('total_price', 0.00))
		{
			$this->table->load($orderId);
			$this->set('total_price', $totalPrice, true);
			$this->set('total_taxes', $totalTaxes, true);
			$this->table->store();
		}

		return $products;
	}

	/**
	 * @param string  $name
	 * @param mixed   $value
	 * @param boolean $force
	 *
	 * @return mixed
	 * @since 1.2.8
	 */

	public function set($name, $value, $force = false)
	{
		$previous = null;

		if ($force || property_exists($this, $name))
		{
			$previous      = $this->{$name};
			$this->{$name} = $value;
		}

		if (property_exists($this->table, $name))
		{
			$this->table->set($name, $value);
		}

		return $previous;
	}

	public function addProduct($productData, $options = [])
	{
		/**
		 * @var JDatabaseDriver $db
		 * @var Log             $log
		 */
		$db         = easyshop('db');
		$dataObject = (object) $productData;

		if ((int) $dataObject->id > 0)
		{
			$result = $db->updateObject('#__easyshop_order_products', $dataObject, 'id');
		}
		else
		{
			$result = $db->insertObject('#__easyshop_order_products', $dataObject, 'id');
		}

		if ($result && $options)
		{
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__easyshop_order_product_options'))
				->where($db->quoteName('order_product_id') . ' = ' . (int) $dataObject->id);
			$db->setQuery($query)
				->execute();

			foreach ($options as $option)
			{
				if (is_array($option) || is_object($option))
				{
					if (is_array($option))
					{
						$option = (object) $option;
					}

					$option->order_product_id = (int) $dataObject->id;
					$db->insertObject('#__easyshop_order_product_options', $option, 'id');
				}

			}
		}

		$this->load($dataObject->order_id);
		$this->updateProductStock();

		return $this;
	}

	public function updateProductStock()
	{
		$config      = easyshop('config');
		$orderStatus = $config->get('stock_order_update', []);
		$paid        = $config->get('stock_paid_update', 1);

		if (empty($orderStatus)
			|| empty($this->products)
			|| !in_array($this->state, $orderStatus)
			|| ($paid && (int) $this->payment_status !== 1)
		)
		{
			return;
		}

		/** @var Log $logClass */
		Table::addIncludePath(ES_COMPONENT_ADMINISTRATOR . '/tables');
		$productTable = Table::getInstance('Product', 'EasyshopTable');
		$logClass     = easyshop(Log::class);

		foreach ($this->products as $product)
		{
			if ($productTable->load($product->product_id))
			{
				$currentStock = (int) $productTable->stock;

				if ($currentStock !== -1)
				{
					$stock = $currentStock - (int) $product->quantity;
					$productTable->set('stock', $stock > 0 ? $stock : 0);

					if ($productTable->store())
					{
						$logClass->addEntry('com_easyshop.product', 'COM_EASYSHOP_PRODUCT_STOCK_UPDATED_FORMAT', [$productTable->name, $productTable->id, $stock], $currentStock, $stock);
					}
				}
			}
		}
	}

	public function getLayoutData($layoutId = null, $renderer = null)
	{
		/**
		 * @var Renderer $renderer
		 * @var Utility  $utility
		 * @var Currency $currency
		 */

		if (!$renderer instanceof Renderer)
		{
			$renderer = easyshop('renderer');
		}

		$orderId  = $this->getId();
		$utility  = easyshop(Utility::class);
		$currency = easyshop(Currency::class);
		$currency->load($this->get('currency_id'));
		$shippingId  = $this->get('shipping_id', 0);
		$paymentId   = $this->get('payment_id', 0);
		$displayData = [
			'order'         => $this,
			'currency'      => $currency,
			'utility'       => $utility,
			'orderStatus'   => $this->getOrderStatus(),
			'paymentStatus' => $this->getPaymentStatus(),
			'address'       => $this->get('address'),
			'config'        => easyshop('config'),
			'payment'       => null,
			'shipping'      => null,
		];
		$replaceData = [
			'{ORDER_ID}'         => $orderId,
			'{ORDER_CODE}'       => $this->get('order_code'),
			'{CUSTOMER_NAME}'    => $this->__get('customerName'),
			'{CUSTOMER_EMAIL}'   => $this->get('user_email'),
			'{NOTE}'             => $this->get('note'),
			'{BILLING_ADDRESS}'  => $utility->formatAddress($displayData['address']['billing']),
			'{SHIPPING_ADDRESS}' => $utility->formatAddress($displayData['address']['shipping']),
			'{CART_BODY}'        => $renderer->render('email.order-cart-body-html', $displayData),
			'{USER_NAME}'        => CMSFactory::getUser()->name,
			'{ORDER_STATUS}'     => $displayData['orderStatus'][$this->get('state')],
			'{PAYMENT_STATUS}'   => $displayData['paymentStatus'][$this->get('payment_status')],
			'{SHIPPING_METHOD}'  => '',
			'{PAYMENT_METHOD}'   => '',
		];

		if ($shippingId || $paymentId)
		{
			$methodClass = easyshop(Method::class);

			if ($shippingId && ($shipping = $methodClass->get($shippingId)))
			{
				$displayData['shipping']          = $shipping;
				$replaceData['{SHIPPING_METHOD}'] = $shipping->name;
			}

			if ($paymentId && ($payment = $methodClass->get($paymentId)))
			{
				$displayData['payment']          = $payment;
				$replaceData['{PAYMENT_METHOD}'] = $payment->name;
			}
		}

		// @since 1.2.8
		$checkoutFields = $this->__get('checkoutFields');

		if (!empty($checkoutFields))
		{
			foreach ($checkoutFields as $checkoutField)
			{
				$replaceData['{' . $checkoutField->field_name . '}'] = $checkoutField->display;
			}
		}

		// @since 1.3.0
		foreach ($displayData['address']['billing'] as $billingField)
		{
			$replaceData['{billing_' . $billingField->field_name . '}'] = $billingField->display;
		}

		foreach ($displayData['address']['shipping'] as $shippingField)
		{
			$replaceData['{shipping_' . $shippingField->field_name . '}'] = $shippingField->display;
		}

		if ($layoutId)
		{
			$html = $renderer->render($layoutId, $displayData);

			foreach ($replaceData as $key => $value)
			{
				if (stripos($html, $key) !== false)
				{
					$html = str_ireplace($key, $value, $html);
				}
			}

			return $html;
		}

		return $replaceData;
	}

	public function getOrderStatus()
	{
		return [
			ES_ORDER_CREATED   => Text::_('COM_EASYSHOP_ORDER_CREATED'),
			ES_ORDER_CONFIRMED => Text::_('COM_EASYSHOP_ORDER_CONFIRM'),
			ES_ORDER_PROCESSED => Text::_('COM_EASYSHOP_ORDER_PROCESSED'),
			ES_ORDER_SHIPPED   => Text::_('COM_EASYSHOP_ORDER_SHIPPED'),
			ES_ORDER_SUCCEED   => Text::_('COM_EASYSHOP_ORDER_SUCCEED'),
			ES_ORDER_CANCELLED => Text::_('COM_EASYSHOP_ORDER_CANCELLED'),
			ES_ORDER_ARCHIVED  => Text::_('COM_EASYSHOP_ORDER_ARCHIVED'),
			ES_ORDER_TRASHED   => Text::_('COM_EASYSHOP_ORDER_TRASH'),
		];
	}

	public function getPaymentStatus()
	{
		return [
			ES_PAYMENT_UNPAID => Text::_('COM_EASYSHOP_PAYMENT_UNPAID'),
			ES_PAYMENT_PAID   => Text::_('COM_EASYSHOP_PAYMENT_PAID'),
			ES_PAYMENT_REFUND => Text::_('COM_EASYSHOP_PAYMENT_REFUND'),
		];
	}

	public function paid(array $extraData = [])
	{
		if ($this->updatePayment(1, $extraData))
		{
			easyshop('app')->triggerEvent('onEasyshopOrderPaid', [$this]);
			easyshop(Log::class)->addEntry('com_easyshop.order', 'COM_EASYSHOP_LOG_ORDER_PAID', [$this->get('order_code')]);
			$this->updateProductStock();
		}

		return $this;
	}

	public function updatePayment($status, array $extraData = [])
	{
		$this->getId();

		if (!in_array((string) $status, array_keys($this->getPaymentStatus())))
		{
			return false;
		}

		$orderTable                  = $this->getTable();
		$extraData['payment_status'] = $status;

		foreach ($extraData as $name => $value)
		{
			$orderTable->set($name, $value);
			$this->{$name} = $value;
		}

		if ($orderTable->store())
		{
			return $this;
		}

		return false;
	}

	public function getTable()
	{
		return $this->table;
	}

	public function unpaid(array $extraData = [])
	{
		if ($this->updatePayment(0, $extraData))
		{
			easyshop('app')->triggerEvent('onEasyshopOrderUnpaid', [$this]);
			easyshop(Log::class)->addEntry('com_easyshop.order', 'COM_EASYSHOP_LOG_ORDER_UNPAID', [$this->get('order_code')]);
		}

		return $this;
	}

	public function refund(array $extraData = [])
	{
		if ($this->updatePayment(2, $extraData))
		{
			easyshop('app')->triggerEvent('onEasyshopOrderRefund', [$this]);
			easyshop(Log::class)->addEntry('com_easyshop.order', 'COM_EASYSHOP_LOG_ORDER_REFUND', [$this->get('order_code')]);
		}

		return $this;
	}

	/**
	 * Get order status text
	 * @since 1.0.1
	 */
	public function getStatusText()
	{
		$orderStatus = $this->getOrderStatus();

		return isset($this->state) ? $orderStatus[$this->state] : null;
	}

	/**
	 * Get order payment status text
	 * @since 1.0.1
	 */
	public function getPaymentText()
	{
		$orderStatus = $this->getPaymentStatus();

		return isset($this->payment_status) ? $orderStatus[$this->payment_status] : null;
	}

	/**
	 * @param integer $orderId
	 * @param integer $orderStatus
	 * @param integer $paymentStatus
	 *
	 * @return mixed
	 * @since 1.1.0
	 */
	public function updateCouponLimit($orderId, $orderStatus, $paymentStatus)
	{
		$orderId = (int) $orderId;
		$db      = easyshop('db');
		$query   = $db->getQuery(true)
			->select('a.coupon_id, a2.limit, a2.coupon_code, a2.params')
			->from($db->quoteName('#__easyshop_order_coupons', 'a'))
			->innerJoin($db->quoteName('#__easyshop_discounts', 'a2') . ' ON a2.id = a.coupon_id')
			->where('a2.limit <> -1 AND a2.limit > 0 AND a.handled = 0 AND a.order_id = ' . $orderId);
		$db->setQuery($query);

		if ($rows = $db->loadObjectList())
		{
			$thisStatus = (int) $orderStatus;
			$thisPaid   = (int) $paymentStatus == 1;
			$logClass   = easyshop(Log::class);

			foreach ($rows as $row)
			{
				$params = new Registry;
				$params->loadString((string) $row->params);
				$orderStatus = $params->get('handle_on_order_status', []);
				$orderPaid   = $params->get('handle_on_order_paid', 0);

				if (($orderStatus && !in_array($thisStatus, $orderStatus)) || ($orderPaid && !$thisPaid))
				{
					continue;
				}

				$oldLimit = (int) $row->limit;
				$couponId = (int) $row->coupon_id;
				$newLimit = $oldLimit - 1;

				if ($newLimit < 0)
				{
					$newLimit = 0;
				}

				$query->clear()
					->update($db->quoteName('#__easyshop_discounts'))
					->set($db->quoteName('limit') . ' = ' . $newLimit)
					->where($db->quoteName('id') . ' = ' . $couponId);
				$db->setQuery($query)
					->execute();

				$query->clear()
					->update($db->quoteName('#__easyshop_order_coupons'))
					->set($db->quoteName('handled') . ' = 1')
					->where($db->quoteName('order_id') . ' = ' . $orderId)
					->where($db->quoteName('coupon_id') . ' = ' . $couponId);
				$db->setQuery($query)
					->execute();

				$logClass->addEntry('com_easyshop.discount', 'COM_EASYSHOP_COUPON_UPDATE_LIMIT_FORMAT', [$row->coupon_code, $oldLimit, $newLimit], $oldLimit, $newLimit);
			}
		}
	}

	/**
	 * @param Currency $currency
	 *
	 * @since 1.1.0
	 */
	public function setCurrency(Currency $currency)
	{
		$this->currency = $currency;
	}
}
