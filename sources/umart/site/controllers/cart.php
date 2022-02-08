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
use ES\Classes\Discount;
use ES\Classes\Media;
use ES\Classes\Product;
use ES\Classes\Utility;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;

class EasyshopControllerCart extends BaseController
{
	public function addItem()
	{
		/** @var Cart $cart */

		$cart     = easyshop(Cart::class);
		$pk       = (int) $this->input->getInt('productId');
		$quantity = (int) $this->input->getInt('quantity');
		$options  = (array) $this->input->get('options', [], 'array');
		$data     = ['redirect' => 0];

		try
		{
			$this->parseOptions($options);
			$item = $cart->addItem($pk, $quantity, $options, $quantity > 0);

			if ($item)
			{
				$this->parseResultData($data, $cart, $item);
			}
		}
		catch (RuntimeException $e)
		{
			$data = $e;
		}
		catch (InvalidArgumentException $e)
		{
			if ($product = easyshop(Product::class)->getItem($pk, false, false))
			{
				$data['redirect'] = $product->link;
			}
			else
			{
				$data = $e;
			}
		}

		echo new JsonResponse($data);

		easyshop('app')->close();
	}

	protected function parseOptions(&$options)
	{
		/** @var $customField CustomField */
		$customField = easyshop(CustomField::class, ['reflector' => 'com_easyshop.product.option']);
		$temp        = [];

		foreach ($options as $option)
		{
			preg_match('/product_option\[([0-9]+)\]/', $option['name'], $matches);
			$optId = (int) $matches[1];
			$field = $customField->findField($optId);

			if (!$field)
			{
				throw new RuntimeException(Text::sprintf('COM_EASYSHOP_ERROR_OPTION_NOT_FOUND_FORMAT', $optId));
			}

			$temp[$optId] = $option['value'];
		}

		$options = $temp;
	}

	protected function parseResultData(&$data, $cart, $item = [])
	{
		/** @var Utility $utility */
		$utility        = easyshop(Utility::class);
		$config         = easyshop('config');
		$renderer       = easyshop('renderer');
		$vendorData     = $cart->extractVendorData();
		$vendorActiveId = $cart->getVendorActive();
		$modal          = $config->get('cart_modal_type', 'detail');
		$shopInfo       = $config->get('shop_cart_info', '1');

		if (empty($item))
		{
			$modal = 'detail';
		}

		if ($modal === 'detail')
		{
			$cartOutputHTML = '';

			foreach ($vendorData as $vendorId => $extractData)
			{
				if ($extractData['count'])
				{
					$cartOutputHTML .= $renderer->render('cart.cart', [
						'extractData' => $extractData,
						'vendorId'    => $vendorId,
						'shopInfo'    => $shopInfo ? $utility->getShopInformation($vendorId) : '',
					]);
				}
			}

			if (empty($cartOutputHTML))
			{
				$cartOutputHTML = $renderer->render('cart.empty');
			}

			$data['html'] = $renderer->render('cart.modal.detail', [
				'cartOutputHTML' => $cartOutputHTML,
			]);
		}
		else
		{
			$extractData  = isset($vendorData[$vendorActiveId]) ? $vendorData[$vendorActiveId] : array_pop($vendorData);
			$data['html'] = $renderer->render('cart.modal.simple', [
				'extractData' => $extractData,
				'item'        => $item,
			]);
		}

		$data['summaryHTML'] = $renderer->render('checkout.summary');

		if (!empty($item['direct']))
		{
			$data['redirect'] = Route::_(EasyshopHelperRoute::getCartRoute('checkout'), false);
		}
	}

	public function update()
	{
		$pk       = (int) $this->input->getInt('productId');
		$quantity = (int) $this->input->getInt('quantity');
		$key      = $this->input->get('key');
		$type     = strtolower($this->input->getWord('updateType', 'update'));
		$message  = null;

		/** @var $cart Cart */
		$cart = easyshop(Cart::class);

		try
		{
			$data = [];

			if ($type == 'update')
			{
				$items   = $cart->getItems();
				$options = isset($items[$pk]['option_array'])
					? $items[$pk]['option_array']
					: $this->input->post->get('options', []);
				$cart->addItem($pk . ':' . $key, $quantity, $options, true);
				$message = Text::_('COM_EASYSHOP_CART_QUANTITY_UPDATED');
			}
			else
			{
				$cart->removeItem($pk . ':' . $key);
				$message = Text::_('COM_EASYSHOP_CART_PRODUCT_REMOVED');
			}

			$this->parseResultData($data, $cart);
		}
		catch (RuntimeException $e)
		{
			$data = $e;
		}

		echo new JsonResponse($data, $message);

		easyshop('app')->close();
	}

	public function calculate()
	{
		$options   = (array) $this->input->get('optionArray', [], 'array');
		$productId = (int) $this->input->getInt('productId');
		$quantity  = (int) $this->input->getInt('quantity');

		/**
		 * @var stdClass $product
		 * @var Product  $productClass
		 * @var Cart     $cart
		 * @var Currency $currency
		 * @var Media    $media
		 */

		try
		{
			$this->parseOptions($options);
			$productClass = easyshop(Product::class);
			$currency     = easyshop(Currency::class)->getActive();
			$cart         = easyshop(Cart::class);
			$product      = $productClass->getItem($productId);
			$price        = $product->price;
			$cart->parsePrice($price, $product->prices, $quantity);
			$data             = $cart->calculateOptions($options, $product, $quantity, $price);
			$data['priceRaw'] = $data['price'];
			$data['taxesRaw'] = $productClass->getTotalTaxes($product, $data['priceRaw']);
			$data['taxes']    = $currency->toFormat($data['taxesRaw'], true);

			if ($data['price'] < 0.01
				&& $price < 0.01
				&& easyshop('config', 'zero_as_free', 0)
			)
			{
				$data['price'] = Text::_('COM_EASYSHOP_FREE_PRODUCT');
			}
			else
			{
				$data['price'] = $currency->toFormat($data['priceRaw'], true);
			}

			if (!empty($data['options']))
			{
				foreach ($data['options'] as &$option)
				{
					if ($option['prefix'] > 0.00 || $option['prefix'] < 0.00)
					{
						$option['price'] = $currency->toFormat(abs($option['prefix']), true);

						if ($option['prefix'] > 0.00)
						{
							$option['price'] = '+' . $option['price'];
						}
						else
						{
							$option['price'] = '-' . $option['price'];
						}
					}
					else
					{
						$option['price'] = '';
					}
				}
			}

			if (!empty($data['images']))
			{
				$media = easyshop(Media::class);

				foreach ($data['images'] as &$image)
				{
					$image = $media->getFullImages($image);
				}
			}

			$response = $data;
		}
		catch (RuntimeException $e)
		{
			$response = $e;
		}

		echo new JsonResponse($response);
		easyshop('app')->close();
	}

	public function coupon()
	{
		/**
		 * @var Discount $discount
		 * @var Cart     $cart
		 * @var stdClass $coupon
		 */

		try
		{
			$code     = $this->input->getString('coupon');
			$discount = easyshop(Discount::class);
			$cart     = easyshop(Cart::class);
			$response = [
				'type'     => 'failed',
				'code'     => $code,
				'discount' => 0.00,
				'message'  => Text::sprintf('COM_EASYSHOP_WARNING_COUPON_INVALID_FORMAT', $code),
			];

			if ($coupon = $discount->checkCoupon($code))
			{
				$cart->setDiscount($coupon);
				$response['type']    = 'succeed';
				$response['message'] = Text::_('COM_EASYSHOP_MESSAGE_COUPON_APPLIED');
			}

			$this->parseResultData($response, $cart);
		}
		catch (RuntimeException $e)
		{
			$response = $e;
		}

		echo new JsonResponse($response);
		easyshop('app')->close();
	}

	public function removeCoupon()
	{
		try
		{
			/** @var $cart Cart */
			$cart = easyshop(Cart::class);
			$cart->removeDiscount($this->input->getUint('couponId', 0));
			$response = easyshop('renderer')->render('checkout.summary');
		}
		catch (RuntimeException $e)
		{
			$response = $e;
		}

		echo new JsonResponse($response);

		easyshop('app')->close();
	}

}
