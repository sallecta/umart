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

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\Registry\Registry;
use stdClass;

class Discount
{
	public function getOnOrders()
	{
		return $this->getTypes(2);
	}

	protected function getTypes($type)
	{
		static $types = [];

		if (!isset($types[$type]))
		{
			$types[$type] = [];

			if ($items = $this->load())
			{
				foreach ($items as $item)
				{
					$limit = (int) $item->limit;

					if ($type === 1 && $limit !== -1 && $limit < 1)
					{
						continue;
					}

					if ((int) $item->type === $type)
					{
						$types[$type][] = $item;
					}
				}
			}
		}

		return $types[$type];
	}

	public function load()
	{
		static $items = false;

		if (false === $items)
		{
			$db    = easyshop('db');
			$query = $db->getQuery(true)
				->select('a.id, a.name, a.type, a.coupon_code, a.limit, a.flat, a.percentage, '
					. 'a.start_date, a.end_date, a.description, a.user_groups, a.currencies, '
					. 'a.categories, a.include_sub_categories, a.zone_type, a.zone_countries, '
					. 'a.zone_states, a.products, a.order_min_amount, a.coupon_type, a.params, '
					. 'a.product_min_price, a.product_max_price, a.discount_max_price')
				->from($db->quoteName('#__easyshop_discounts', 'a'))
				->where('a.state = 1 AND ' . $db->quote(CMSFactory::getDate()->toSql()) . ' BETWEEN a.start_date AND a.end_date')
				->order('a.ordering ASC');
			$db->setQuery($query);

			if ($items = $db->loadObjectList())
			{
				foreach ($items as &$item)
				{
					$item->flat                   = abs((float) $item->flat);
					$item->percentage             = abs((int) $item->percentage);
					$item->include_sub_categories = (int) $item->include_sub_categories;

					if (!empty($item->user_groups))
					{
						$item->user_groups = (array) explode(',', trim($item->user_groups));
					}
					else
					{
						$item->user_groups = [];
					}

					if (!empty($item->currencies))
					{
						$item->currencies = (array) explode(',', trim($item->currencies));
					}
					else
					{
						$item->currencies = [];
					}

					if (!empty($item->categories))
					{
						$item->categories = (array) explode(',', trim($item->categories));
					}
					else
					{
						$item->categories = [];
					}

					if (!empty($item->products))
					{
						$item->products = (array) explode(',', trim($item->products));
					}
					else
					{
						$item->products = [];
					}

					if (!empty($item->zone_countries))
					{
						$item->zone_countries = (array) explode(',', trim($item->zone_countries));
					}
					else
					{
						$item->zone_countries = [];
					}

					if (!empty($item->zone_states))
					{
						$item->zone_states = (array) explode(',', trim($item->zone_states));
					}
					else
					{
						$item->zone_states = [];
					}

					$item->params = new Registry((string) $item->params);
				}
			}
		}

		return $items;
	}

	public function getCouponByCode($code)
	{
		foreach ($this->getCoupons() as $coupon)
		{
			if ($coupon->coupon_code === $code)
			{
				return $coupon;
			}
		}

		return false;
	}

	public function getCoupons()
	{
		return $this->getTypes(1);
	}

	public function applyOnProduct($product)
	{
		$discounts = $this->getOnProducts();

		if (count($discounts))
		{
			/**
			 * @var $currency      Currency
			 * @var $category      CategoryNode
			 * @var $utility       Utility
			 */

			$utility  = easyshop(Utility::class);
			$currency = easyshop(Currency::class)->getActive();

			foreach ($discounts as $discount)
			{
				$collectData = [
					'discount' => $discount,
					'utility'  => $utility,
					'product'  => $product,
					'currency' => $currency,
				];

				if ($amount = $this->checkOnProduct($collectData))
				{
					$oldPrice                = $product->price;
					$newPrice                = $product->price - $amount;
					$product->price          = $newPrice;
					$product->discount       = $amount;
					$product->oldPriceFormat = $currency->toFormat($oldPrice, true);
					$product->priceFormat    = $currency->toFormat($newPrice, true);
					$this->loadProductBadge($discount, $product);
					break;
				}
			}
		}
	}

	public function getOnProducts()
	{
		return $this->getTypes(0);
	}

	protected function checkOnProduct($collectData)
	{
		/**
		 * @var $currency      Currency
		 * @var $category      CategoryNode
		 * @var $utility       Utility
		 * @var $discount      stdClass
		 * @var $product       stdClass
		 */

		extract($collectData);
		$flat             = $discount->flat;
		$percentage       = $discount->percentage;
		$userGroups       = $discount->user_groups;
		$currencies       = $discount->currencies;
		$categories       = $discount->categories;
		$products         = $discount->products;
		$subCategories    = $discount->include_sub_categories;
		$productMinPrice  = (float) $discount->product_min_price;
		$productMaxPrice  = (float) $discount->product_max_price;
		$discountMaxPrice = (float) $discount->discount_max_price;

		if (($productMinPrice > 0.00 && (float) $product->price < $productMinPrice)
			|| ($productMaxPrice > 0.00 && (float) $product->price > $productMaxPrice)
		)
		{
			return false;
		}

		$pass = true;

		if (count($categories) && !in_array($product->category_id, $categories))
		{
			$pass = false;

			if ($subCategories)
			{
				foreach ($categories as $categoryId)
				{
					$category = Categories::getInstance('easyshop.product')->get($categoryId);

					if ($pass)
					{
						break;
					}

					if ($category instanceof CategoryNode && $category->hasChildren())
					{
						foreach ($category->getChildren(true) as $child)
						{
							if ((int) $child->id === (int) $product->category_id)
							{
								$pass = true;
								break;
							}
						}
					}
				}
			}
		}

		if ((count($currencies) && !in_array($currency->get('id'), $currencies))
			|| (count($userGroups) && !$utility->userAccess($userGroups))
			|| (count($products) && !in_array($product->id, $products))
		)
		{
			$pass = false;
		}

		if ($pass)
		{
			$amount = $flat;

			if ($percentage > 0)
			{
				$amount += ($percentage * (float) $product->price) / 100;
			}

			if ($discountMaxPrice > 0.00 && $amount > $discountMaxPrice)
			{
				$amount = $discountMaxPrice;
			}

			return $amount;
		}

		return false;
	}

	/**
	 * @param $discount
	 * @param $product
	 *
	 * @since 1.0.5
	 */
	protected function loadProductBadge($discount, $product)
	{
		static $renderer = null;

		if (null === $renderer)
		{
			$renderer = easyshop('renderer');
		}

		$badge = $discount->params->get('badge');

		if (in_array($badge, ['image', 'text'])
			&& ($value = $discount->params->get('badge_' . $badge))
		)
		{
			$displayData          = $discount->params->toArray();
			$displayData['value'] = $value;
			$product->badgeData   = $displayData;
		}
	}

	public function getAmountCouponOnProduct($discount, $product)
	{
		/**
		 * @var $currency      Currency
		 * @var $utility       Utility
		 * @var $cart          Cart
		 * @var $discount      stdClass
		 */

		// Check again
		$discount = $this->checkCoupon($discount->coupon_code);

		if (!$discount)
		{
			return false;
		}

		$utility     = easyshop(Utility::class);
		$currency    = easyshop(Currency::class)->getActive();
		$collectData = [
			'discount' => $discount,
			'utility'  => $utility,
			'product'  => $product,
			'currency' => $currency,
		];

		if ($amount = $this->checkOnProduct($collectData))
		{
			return $amount;
		}

		return false;
	}

	public function checkCoupon($code)
	{
		foreach ($this->getCoupons() as $coupon)
		{
			$limit = (int) $coupon->limit;

			if ($coupon->coupon_code === $code && ($limit === -1 || $limit > 0))
			{
				return $coupon;
			}
		}

		return false;
	}

	public function checkOnOrder($discount, $orderAmount)
	{
		/**
		 * @var $utility     Utility
		 * @var $cart        Cart
		 * @var $currency    Currency
		 * @var $customField CustomField
		 */

		$utility    = easyshop(Utility::class);
		$cart       = easyshop(Cart::class);
		$currency   = easyshop(Currency::class)->getActive();
		$userGroup  = $discount->user_groups;
		$currencies = $discount->currencies;
		$countries  = $discount->zone_countries;
		$states     = $discount->zone_states;
		$zoneType   = (int) $discount->zone_type;
		$currencyId = (int) $currency->get('id');

		if (($discount->order_min_amount > 0.00 && $orderAmount < $discount->order_min_amount)
			|| (count($userGroup) && !$utility->userAccess($userGroup))
			|| (count($currencies) && !in_array($currencyId, $currencies))
		)
		{
			return false;
		}

		if ($zoneType > 0)
		{
			$checkoutData = $cart->getCheckoutData();
			$address      = $zoneType === 1 ? 'shipping_address' : 'billing_address';

			if (isset($checkoutData[$address]))
			{
				$customField = easyshop(CustomField::class, [
					'reflector' => 'com_easyshop.user',
				]);

				$countryField = $customField->findFieldByName('user_country');
				$stateField   = $customField->findFieldByName('user_state');

				if (count($countries) && $countryField)
				{
					$fieldId = $countryField->id;

					if (!in_array(@$checkoutData[$address][$fieldId], $countries))
					{
						return false;
					}
				}

				if (count($states) && $stateField)
				{
					$fieldId = $stateField->id;

					if (!in_array(@$checkoutData[$address][$fieldId], $states))
					{
						return false;
					}
				}
			}
		}

		return $discount;
	}
}
