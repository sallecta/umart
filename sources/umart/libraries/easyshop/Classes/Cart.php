<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ES\Classes;

use Exception;
use InvalidArgumentException;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use RuntimeException;
use stdClass;

defined('_JEXEC') or die;

class Cart
{
	protected $context = 'com_easyshop.cart';

	public function setDiscount($discount, $reset = true)
	{
		$allDiscounts = [];

		if (!is_array($discount))
		{
			$discount = array($discount);
		}

		$discounts = array_merge($this->getDiscounts(), $discount);

		foreach ($discounts as $discount)
		{
			$id = (int) $discount->id;

			if (!isset($allDiscounts[$id]))
			{
				$allDiscounts[$id] = $discount;
			}
		}

		if ($reset && ($items = $this->getItems()))
		{
			foreach ($items as $k => $array)
			{
				foreach ($array as $item)
				{
					try
					{
						$this->addItem($item['product']->id, $item['quantity'], $item['optionArray'], true);
					}
					catch (Exception $e)
					{
						$this->removeItem($item['product']->id);
					}
				}
			}
		}

		easyshop('app')->setUserState($this->context . '.discounts', $allDiscounts);
	}

	public function getDiscounts()
	{
		return easyshop('app')->getUserState($this->context . '.discounts', []);
	}

	public function getItems()
	{
		$items        = easyshop('app')->getUserState($this->context . '.items', []);
		$productClass = easyshop(Product::class);
		$newItems     = [];

		foreach ($items as $pk => $temps)
		{
			// Make sure product is enabled
			if ($productClass->isEnabled($pk))
			{
				$newItems[$pk] = $temps;
			}
		}

		$this->setItems($newItems);

		return $newItems;
	}

	public function setItems($items = [])
	{
		easyshop('app')->setUserState($this->context . '.items', $items);
	}

	public function addItem($pk = 0, $quantity = 1, array $options = [], $fixedQuantity = true)
	{
		/**
		 * @var $productClass Product
		 */

		if (strpos($pk, ':') !== false)
		{
			$parts = explode(':', $pk, 2);
			$pk    = (int) $parts[0];
			$key   = $parts[1];
		}
		else
		{
			$pk  = (int) $pk;
			$key = '';

			if (!empty($options))
			{
				foreach ($options as $optId => $optValue)
				{
					$key .= ',' . $optId . ':' . $optValue;
				}

				$key = md5(ltrim($key, ','));
			}
		}

		$productClass = easyshop(Product::class);

		if (!$productClass->isEnabled($pk))
		{
			$this->removeItem($pk);
			throw new RuntimeException(Text::sprintf('COM_EASYSHOP_PRODUCT_DISABLED', $pk));
		}

		$product = $productClass->getItem($pk, true);

		// @since 1.2.3, check permission
		$userClass = easyshop(User::class);
		$groups    = easyshop('config', 'groups_can_add_to_cart', []);
		$allowAdd  = (empty($groups) || $userClass->accessGroups($groups, true));

		if (!$product->params->get('product_detail_add_to_cart', 1) || !$allowAdd)
		{
			$this->removeItem($pk);
			throw new RuntimeException(Text::sprintf('COM_EASYSHOP_PRODUCT_NOT_ALLOW_ADD_TO_CART_FORMAT', $product->name));
		}

		$vendorId    = (int) $product->vendor_id;
		$price       = (float) $product->price;
		$quantity    = (int) $quantity;
		$minQuantity = (int) $product->params->get('product_detail_min_quantity', 1);
		$maxQuantity = (int) $product->params->get('product_detail_max_quantity', 0);
		$this->setVendorActive($vendorId);

		if ($quantity < 1)
		{
			$quantity = 1;
		}

		if ($minQuantity > $quantity)
		{
			$quantity = $minQuantity;
		}

		if ($maxQuantity > 0 && $quantity > $maxQuantity)
		{
			$quantity = $maxQuantity;
		}

		$item = [
			'vendorId'    => $vendorId,
			'options'     => [],
			'optionArray' => [],
		];

		$items = $this->getItems();

		if (isset($items[$pk][$key]['optionArray']))
		{
			if (!$fixedQuantity)
			{
				$quantity += $items[$pk][$key]['quantity'];
			}

			if (empty($options))
			{
				$options = $items[$pk][$key]['optionArray'];
			}
		}

		if (isset($items[$pk][$key]['coupon']))
		{
			$item['coupon'] = $items[$pk][$key]['coupon'];
		}

		$this->parsePrice($price, $product->prices, $quantity);
		$item['quantity'] = $quantity;
		$item['product']  = $product;
		$item['price']    = $price;

		if (empty($options))
		{
			if (!empty($product->options))
			{
				throw new InvalidArgumentException(Text::sprintf('COM_EASYSHOP_ERROR_PRODUCT_HAS_OPTIONS_FORMAT', $product->name));
			}
		}
		else
		{
			$calculate = $this->calculateOptions($options, $product, $quantity, $price);

			if (!$calculate instanceof RuntimeException)
			{
				$item['optionArray'] = $options;
				$item['options']     = !empty($calculate['options']) ? $calculate['options'] : [];
				$item['price']       = $calculate['price'];

				if (!empty($calculate['images']))
				{
					/** @var $media Media */
					$media = easyshop(Media::class);

					$item['image'] = $media->getFullImages($calculate['images'][0]);
				}
			}
		}

		if (!isset($items[$pk][$key]))
		{
			$items[$pk][$key] = [];
		}

		// @since 1.1.1 direct checkout (Buy now)
		$direct = $product->params->get('product_detail_direct', 0);

		if ($direct)
		{
			$this->setDirect([
				$pk => [$key => $item],
			]);
		}
		else
		{
			if ($directItem = $this->setDirect(null))
			{
				$directPks = array_keys($directItem);
				$directPk  = $directPks[0];

				if ($directPk != $pk && isset($items[$directPk]))
				{
					unset($items[$directPk]);
				}
			}
		}

		$item['taxes']    = $productClass->getTotalTaxes($product, $item['price']);
		$items[$pk][$key] = array_merge($items[$pk][$key], $item);
		$this->setItems($items);
		$item['direct'] = $direct;

		return $item;
	}

	public function removeItem($pk)
	{
		if (strpos($pk, ':') !== false)
		{
			$parts = explode(':', $pk, 2);
			$pk    = (int) $parts[0];
			$key   = $parts[1];
		}
		else
		{
			$pk  = (int) $pk;
			$key = '';
		}

		$items = $this->getItems();

		if (isset($items[$pk]))
		{
			$newItems = [];

			if ('' === $key)
			{
				foreach ($items as $k => $temps)
				{
					if ($pk != $k)
					{
						$newItems[$k] = $temps;
					}
				}

				$items = $newItems;
			}
			else
			{
				foreach ($items[$pk] as $k => $temps)
				{
					if ($key != $k)
					{
						$newItems[$k] = $temps;
					}
				}

				$items[$pk] = $newItems;
			}
		}

		if ($directItem = $this->getDirect())
		{
			$directPks = array_keys($directItem);
			$directPk  = $directPks[0];

			if ($directPk == $pk)
			{
				$this->setDirect(null);
			}
		}

		$this->setItems($items);
	}

	/**
	 * @since 1.1.1
	 */
	public function getDirect()
	{
		return easyshop('app')->getUserState($this->context . '.direct');
	}

	/**
	 * @param array|null $directItem
	 *
	 * @since 1.1.1
	 */
	public function setDirect($directItem = null)
	{
		$direct = $this->getDirect();
		easyshop('app')->setUserState($this->context . '.direct', $directItem);

		return $direct;
	}

	/**
	 * @param integer $vendorId
	 *
	 * @return mixed
	 * @since version
	 */

	public function setVendorActive($vendorId)
	{
		easyshop('app')->setUserState($this->context . '.vendorActiveId', $vendorId);
	}

	public function parsePrice(&$retailPrice, $priceArray, $quantity)
	{
		/** @var $currency Currency */
		$currency      = easyshop(Currency::class)->getActive();
		$multiCurrency = $currency->isMultiMode();

		if (count($priceArray))
		{
			$currencyActiveId = (int) $currency->get('id');
			$nullDate         = easyshop('db')->getNullDate();
			$nowDateTime      = CMSFactory::getDate()->toUnix();

			foreach ($priceArray as $price)
			{
				$priceCurrencyId = (int) $price->currency_id;
				$validFromDate   = $price->valid_from_date;
				$validToDate     = $price->valid_to_date;

				if (!empty($validFromDate)
					&& !empty($validToDate)
					&& $validFromDate !== $nullDate
					&& $validToDate !== $nullDate
				)
				{
					try
					{
						$validFromDate = CMSFactory::getDate($validFromDate)->toUnix();
						$validToDate   = CMSFactory::getDate($validToDate)->toUnix();
						$isValid       = $nowDateTime >= $validFromDate && $nowDateTime <= $validToDate;

						if (!$isValid)
						{
							continue;
						}
					}
					catch (Exception $e)
					{

					}
				}

				if ((!$multiCurrency || !$priceCurrencyId || $currencyActiveId == $priceCurrencyId)
					&& $quantity >= (int) $price->min_quantity
				)
				{
					$retailPrice = (float) $price->price_value;
				}
			}
		}
	}

	/**
	 * @param $optionArray array request option array. Ex: [[1 => "8GB"], [...]]...
	 * @param $product     stdClass product
	 * @param $quantity    int quantity of product to calculate
	 * @param $price       float price of product to calculate
	 *
	 * @return array|Exception|RuntimeException
	 * @since 1.0.0
	 */
	public function calculateOptions($optionArray, $product, $quantity = 1, $price = null)
	{
		try
		{
			/**
			 * @var Currency    $currency
			 * @var CustomField $optionField
			 */

			$quantity = (int) $quantity < 1 ? 1 : (int) $quantity;

			if (empty($product->id))
			{
				throw new RuntimeException(Text::_('COM_EASYSHOP_ERROR_PRODUCT_NOT_ENABLED'));
			}

			$currency      = easyshop(Currency::class)->getActive();
			$optionField   = easyshop(CustomField::class, ['reflector' => 'com_easyshop.product.option']);
			$currencyId    = (int) $currency->getActiveId();
			$multiCurrency = $currency->isMultiMode();
			$optionValues  = $product->option_fields;

			if (null === $price)
			{
				$price = (float) $product->price;
			}

			$response = [];
			$images   = [];

			foreach ($optionArray as $optId => $value)
			{
				$oField = $optionField->findField($optId);

				if (!$oField)
				{
					throw new RuntimeException(Text::sprintf('COM_EASYSHOP_ERROR_OPTION_NOT_FOUND_FORMAT', $optId));
				}

				if (isset($optionValues[$optId]))
				{
					$values = @json_decode($optionValues[$optId]['value'], true);

					if (is_array($values) && json_last_error() == JSON_ERROR_NONE)
					{
						if (isset($values[$value]))
						{
							$options = (array) $values[$value];
						}
						else
						{
							$options = (array) $values;
						}

						$prefix = 0.00;

						foreach ($options as $option)
						{
							if (isset($option['price'])
								&& (!$multiCurrency || empty($option['currency']) || (int) $option['currency'] == $currencyId)
								&& $quantity >= (int) $option['min_quantity']
								&& (float) $option['price'] > 0.00
								&& !empty($option['action'])
							)
							{
								switch ($option['action'])
								{
									case '+':
										$prefix = $option['price'];
										break;

									case '-':
										$prefix = 0 - $option['price'];
										break;

									case 'x':
										$prefix = ($price * $option['price']) - $price;
										break;

									case '/':

										if ($option['price'] > 0)
										{
											$prefix = ($price / $option['price']) - $price;
										}

										break;
								}

							}

							if (!empty($option['images']))
							{
								$images = array_merge($images, $option['images']);
							}
						}

						$text = $value;

						if (in_array($oField->type, ['dropdown', 'list', 'radio', 'colors', 'inline']))
						{
							$params = new Registry($oField->params);

							foreach ($params->get('options', []) as $param)
							{
								if (@$param->value == $value)
								{
									$text = @$param->text;
									break;
								}
							}

						}
						elseif ($oField->type == 'checkbox')
						{
							$text = Text::_(trim($oField->name));
						}

						$response['options'][$optId] = [
							'name'   => $optionValues[$optId]['name'],
							'text'   => $text,
							'value'  => $value,
							'prefix' => $prefix,
						];

						$price += $prefix;
					}
				}
			}

			$response['price']  = $price;
			$response['images'] = ArrayHelper::arrayUnique($images);
		}
		catch (RuntimeException $e)
		{
			$response = $e;
		}

		return $response;
	}

	/**
	 * @param $pk
	 *
	 * @return stdClass|false
	 * @since 1.1.0
	 */
	public function getItem($pk)
	{
		if (strpos($pk, ':') !== false)
		{
			$parts = explode(':', $pk, 2);
			$pk    = (int) $parts[0];
			$key   = $parts[1];
		}
		else
		{
			$pk  = (int) $pk;
			$key = '';
		}

		$items = $this->getItems();

		if (isset($items[$pk][$key]))
		{
			$item = $items[$pk][$key];
		}
		elseif (isset($items[$pk]['']))
		{
			$item = $items[$pk][''];
		}

		if (isset($item))
		{
			$discount = 0.00;

			if (!empty($item['coupon']))
			{
				foreach ($item['coupon'] as $coupon)
				{
					$discount += $coupon->amount;
				}
			}

			$item['discount'] = $discount;

			return $item;
		}

		return false;
	}

	public function setItem($item)
	{
		$items = $this->getItems();

		if (isset($items[$item['product']->id]))
		{
			$items[$item['product']->id] = $item;

			$this->setItems($items);
		}
	}

	public function destroy()
	{
		$this->setCheckoutData([]);
		$this->clear();
	}

	public function setCheckoutData(array $data, $merge = false)
	{
		if ($merge)
		{
			$data = array_merge($this->getCheckoutData(), $data);
		}

		easyshop('app')->setUserState($this->context . '.checkoutData', $data);

		return $data;
	}

	public function getCheckoutData()
	{
		return easyshop('app')->getUserState($this->context . '.checkoutData', []);
	}

	public function clear()
	{
		$this->setItems();
		$this->clearDiscounts();
	}

	/**
	 * @since 1.1.0
	 */
	public function clearDiscounts()
	{
		easyshop('app')->setUserState($this->context . '.discounts', []);
	}

	/**
	 * @return float
	 * @since      1.0.0
	 * @deprecated 2.0.0 Use method extractData instead
	 */
	public function getCountItems()
	{
		$extractData = $this->extractData();

		return $extractData['count'];
	}

	public function extractData($vendorItems = null)
	{
		/**
		 * @var Currency $currency
		 * @var Renderer $renderer
		 * @var Product  $productClass
		 * @var Discount $discountClass
		 */

		$direct                = $this->getDirect();
		$items                 = $direct ? $direct : (null === $vendorItems ? $this->getItems() : $vendorItems);
		$discountClass         = easyshop(Discount::class);
		$productClass          = easyshop(Product::class);
		$AllDiscounts          = $this->getDiscounts();
		$orderDiscounts        = $discountClass->getOnOrders();
		$productDiscountAmount = 0.00;
		$orderDiscountAmount   = 0.00;
		$totalTaxes            = 0.00;
		$subTotal              = 0.00;
		$count                 = 0;
		$extractItems          = [];
		$productDiscounts      = [];
		$appliedDiscounts      = [];

		foreach ($AllDiscounts as $discount)
		{
			if ((int) $discount->type === 1)
			{
				if ((int) $discount->coupon_type === 0)
				{
					$productDiscounts[] = $discount;
				}
				else
				{
					$orderDiscounts[] = $discount;
				}
			}
		}

		$productDiscounts = ArrayHelper::arrayUnique($productDiscounts);
		$orderDiscounts   = ArrayHelper::arrayUnique($orderDiscounts);

		foreach ($items as $pId => $temps)
		{
			foreach ($temps as $key => $item)
			{
				$count++;
				$item['discountAmount'] = 0.00;

				foreach ($productDiscounts as $coupon)
				{
					$amount = $discountClass->getAmountCouponOnProduct($coupon, $item['product']);

					if (false !== $amount)
					{
						$coupon->amount         = $amount;
						$item['discountAmount'] += $coupon->amount;
						$appliedDiscounts[]     = $coupon;
					}
				}

				$item['key']           = $key;
				$item['salePrice']     = $item['price'] - $item['discountAmount'];
				$item['saleTaxes']     = $productClass->getTotalTaxes($item['product'], $item['salePrice']);
				$item['subTotal']      = $item['salePrice'] * $item['quantity'];
				$subTotal              += $item['subTotal'];
				$totalTaxes            += $item['saleTaxes'] * $item['quantity'];
				$productDiscountAmount += $item['discountAmount'] * $item['quantity'];
				$extractItems[]        = $item;
			}
		}

		$state      = easyshop('state');
		$totalShip  = 0.00;
		$paymentFee = 0.00;

		if ($payments = $this->getPaymentMethods())
		{
			$paymentActive = $this->getPaymentMethods(true);

			foreach ($payments as $payment)
			{
				$payment->fee = (float) $payment->flat_fee + ((int) $payment->percentage_fee * $subTotal) / 100;

				if ($paymentActive && $paymentActive->id == $payment->id)
				{
					$paymentFee = $payment->fee;

					if ($paymentActive->taxes)
					{
						$totalTaxes += easyshop(Tax::class)->calculate($paymentActive->taxes, $paymentFee);
					}
				}
			}
		}

		$grandTotal = $subTotal + $totalTaxes + $paymentFee;
		$state->set('cart.subTotal', $subTotal);
		$state->set('cart.totalTaxes', $totalTaxes);
		$state->set('cart.paymentFee', $paymentFee);
		$shippingActive = $this->getShippingMethods(true);

		if ($shippingActive)
		{
			$totalShip  = $shippingActive->total;
			$grandTotal += $totalShip;

			if ($shippingActive->taxes)
			{
				$taxFee     = easyshop(Tax::class)->calculate($shippingActive->taxes, $totalShip);
				$totalTaxes += $taxFee;
				$grandTotal += $taxFee;
			}
		}

		foreach ($orderDiscounts as $discount)
		{
			if ($discountClass->checkOnOrder($discount, $subTotal))
			{
				$orderDiscountAmount += (float) $discount->flat + ((int) $discount->percentage * $subTotal) / 100;
				$discountMaxPrice    = (float) $discount->discount_max_price;

				if ($discountMaxPrice > 0.00 && $orderDiscountAmount > $discountMaxPrice)
				{
					$orderDiscountAmount = $discountMaxPrice;
				}

				$discount->amount   = $orderDiscountAmount;
				$appliedDiscounts[] = $discount;
			}
		}

		$checkoutData          = $this->getCheckoutData();
		$checkoutFieldsDetails = [];

		if (!empty($checkoutData['checkoutFieldsDetails']))
		{
			foreach ($checkoutData['checkoutFieldsDetails'] as $fieldDetail)
			{
				$grandTotal              += $fieldDetail['price'] + $fieldDetail['tax'];
				$totalTaxes              += $fieldDetail['tax'];
				$checkoutFieldsDetails[] = $fieldDetail;
			}
		}

		$grandTotal -= $orderDiscountAmount;

		return [
			'count'                 => $count,
			'items'                 => $extractItems,
			'totalTaxes'            => $totalTaxes,
			'totalShip'             => $totalShip,
			'orderDiscount'         => $orderDiscountAmount,
			'productDiscount'       => $productDiscountAmount,
			'subTotal'              => $subTotal,
			'grandTotal'            => $grandTotal,
			'paymentFee'            => $paymentFee,
			'checkoutFieldsDetails' => $checkoutFieldsDetails,
			'discounts'             => ArrayHelper::arrayUnique($appliedDiscounts),
		];
	}

	public function getPaymentMethods($active = false)
	{
		static $payments = null;

		if (null === $payments)
		{
			PluginHelper::importPlugin('easyshoppayment');
			easyshop('app')->triggerEvent('onEasyshopPaymentRegister', []);
			$payments = easyshop(Method::class)->addPaymentMethod();
		}

		if ($active)
		{
			$data = $this->getCheckoutData();

			if (isset($data['payment_id']) && isset($payments[$data['payment_id']]))
			{
				return $payments[$data['payment_id']];
			}

			return false;
		}

		return $payments;
	}

	public function getShippingMethods($active = false)
	{
		static $shippingMethods = null;

		if (null === $shippingMethods)
		{
			PluginHelper::importPlugin('easyshopshipping');
			easyshop('app')->triggerEvent('onEasyshopShippingRegister', []);
			$shippingMethods = easyshop(Method::class)->addShippingMethod();
		}

		if ($active)
		{
			$data = $this->getCheckoutData();

			if (isset($data['shipping_id']) && isset($shippingMethods[$data['shipping_id']]))
			{
				return $shippingMethods[$data['shipping_id']];
			}
			else
			{
				return false;
			}
		}

		return $shippingMethods;
	}

	/**
	 * @return float
	 * @since      1.0.0
	 * @deprecated 2.0.0 Use method extractData instead
	 */
	public function getSubTotal()
	{
		$extractData = $this->extractData();

		return $extractData['subTotal'];
	}

	/**
	 * @return float
	 * @since      1.0.0
	 * @deprecated 2.0.0 Use method extractData instead
	 */
	public function getTotalTaxes()
	{
		$extractData = $this->extractData();

		return $extractData['totalTaxes'];
	}

	/**
	 * @param $discount stdClass|integer
	 *
	 * @return array
	 * @since 1.1.0
	 */
	public function removeDiscount($discount)
	{
		$app       = easyshop('app');
		$context   = $this->context . '.discounts';
		$discounts = [];

		if (is_object($discount))
		{
			$id = $discount->id;
		}
		elseif (is_numeric($discount))
		{
			$id = $discount;
		}
		else
		{
			$id = 0;
		}

		foreach ($app->getUserState($context, []) as $discountObject)
		{
			if ($discountObject->id != $id)
			{
				$discounts[] = $discountObject;
			}
		}

		$app->setUserState($context, ArrayHelper::arrayUnique($discounts));

		return $discounts;
	}

	/**
	 * Split products for each vendor
	 *
	 * @param boolean $returnActive
	 * @param boolean $reload
	 *
	 * @return array
	 * @since 1.1.4
	 */

	public function extractVendorData($returnActive = false, $reload = false)
	{
		static $extractData = null;
		$vendorActive = $this->getVendorActive();

		if (null === $extractData || $reload)
		{
			$extractData = [];
			$vendorItems = [];
			$isMultiCart = false;
			easyshop('app')->triggerEvent('onEasyshopMultiCartConfirm', [&$isMultiCart]);

			if (true === $isMultiCart)
			{
				foreach ($this->getItems() as $productId => $items)
				{
					foreach ($items as $key => $item)
					{
						$vendorItems[$item['vendorId']][$productId][$key] = $item;
					}
				}

				foreach ($vendorItems as $vendorId => $itemsList)
				{
					if ($data = $this->extractData($itemsList))
					{
						$extractData[$vendorId] = $data;
					}
				}
			}
			else
			{
				$extractData[0] = $this->extractData();
			}
		}

		if ($returnActive)
		{
			if (isset($extractData[$vendorActive]))
			{
				return $extractData[$vendorActive];
			}

			return $extractData ? array_shift($extractData) : [];
		}

		return $extractData;
	}

	/**
	 * @return integer
	 * @since 1.1.4
	 */

	public function getVendorActive()
	{
		return (int) easyshop('app')->getUserState($this->context . '.vendorActiveId', 0);
	}
}
