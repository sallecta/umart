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
use ES\Classes\Utility;
use ES\View\BaseView;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

class EasyshopViewCart extends BaseView
{
	/**
	 * @var Cart
	 * @since 1.0.0
	 */
	protected $cart;
	protected $currency;
	protected $paymentMethods;
	protected $shippingMethods;
	protected $checkoutForm;
	protected $customerForm;
	protected $address = [
		'billing'  => '',
		'shipping' => '',
	];
	protected $cartData = [];
	protected $paymentActiveId = 0;
	protected $shippingActiveId = 0;
	protected $user;

	public function display($tpl = null)
	{
		$this->user     = CMSFactory::getUser();
		$app            = CMSFactory::getApplication();
		$this->currency = easyshop(Currency::class)->getActive();
		$this->cart     = easyshop(Cart::class);
		$model          = easyshop('model', 'checkout');
		Text::script('COM_EASYSHOP_ERROR_INVALID_CARD_NUMBER');

		// @since 1.3.0. We need to check is there a redirect?
		$token = $app->input->get('token');

		if ($token && ($finalData = $app->getUserState($token)))
		{
			$app->setUserState($token, null);
			echo $finalData;

			return true;
		}

		$count                 = 0;
		$this->cartData        = $this->cart->extractVendorData();
		$checkoutData          = $this->cart->getCheckoutData();
		$this->paymentMethods  = $this->cart->getPaymentMethods();
		$this->shippingMethods = $this->cart->getShippingMethods();
		$layout                = $this->getLayout();

		if ($layout === 'confirm')
		{
			$methodsList = [
				'payment_id'  => ['paymentActiveId', $this->paymentMethods],
				'shipping_id' => ['shippingActiveId', $this->shippingMethods],
			];

			foreach ($methodsList as $methodKey => $methods)
			{
				list($activeKey, $methods) = $methods;

				if (empty($checkoutData[$methodKey]))
				{
					foreach ($methods as $method)
					{
						if ($method->is_default)
						{
							$this->{$activeKey} = (int) $method->id;
							$this->cart->setCheckoutData([$methodKey => $this->{$activeKey}], true);
							break;
						}
					}
				}
				else
				{
					$this->{$activeKey} = (int) $checkoutData[$methodKey];
				}
			}
		}

		if (!empty($this->cartData))
		{
			foreach ($this->cartData as $vendorId => $cartData)
			{
				$count += $cartData['count'];
			}
		}

		if (!$count)
		{
			$this->setLayout('empty');

			return parent::display($tpl);
		}

		$redirect = null;

		if (isset($checkoutData['layout'])
			&& in_array($layout, ['checkout', 'confirm'])
			&& $layout !== $checkoutData['layout']
		)
		{
			$redirect = Route::_(EasyshopHelperRoute::getCartRoute($checkoutData['layout']), false);
		}
		elseif (in_array($layout, ['login', 'checkout', 'confirm']))
		{
			if (ES_DETECT_JVERSION === 4)
			{
				CMSFactory::getApplication()->getDocument()->getWebAssetManager()->useScript('showon');
			}
			else
			{
				HTMLHelper::_('jquery.framework');
				HTMLHelper::_('script', 'jui/cms.js', ['relative' => true, 'version' => 'auto']);
			}

			switch ($layout)
			{
				case 'login':
					$redirect = Route::_(EasyshopHelperRoute::getCartRoute('checkout'), false);
					break;

				case 'checkout':
					$this->customerForm = easyshop('model', 'customer', ES_COMPONENT_SITE)->getForm();

					if (!empty($checkoutData['customerInfo']))
					{
						$this->customerForm->bind(['registration' => $checkoutData['customerInfo']]);
					}

					break;

				case 'confirm':

					if (empty($checkoutData['billing_address']) || empty($checkoutData['shipping_address']))
					{
						$redirect = Route::_(EasyshopHelperRoute::getCartRoute('checkout'), false);
						$app->enqueueMessage(Text::_('COM_EASYSHOP_WARNING_INVALID_CHECKOUT_DATA'), 'warning');
					}
					else
					{
						/**
						 * @var $customField CustomField
						 * @var $utility     Utility
						 */
						$customField     = easyshop(CustomField::class, ['reflector' => 'com_easyshop.user']);
						$utility         = easyshop(Utility::class);
						$billingAddress  = $customField->parseFormFieldData($checkoutData['billing_address']);
						$shippingAddress = $customField->parseFormFieldData($checkoutData['shipping_address']);
						$this->address   = [
							'billing'  => $utility->formatAddress($billingAddress),
							'shipping' => $utility->formatAddress($shippingAddress),
						];
					}

					break;
			}
		}

		if (null !== $redirect)
		{
			$app->redirect($redirect);
		}

		if ($layout == 'confirm' && ($finalData = $this->getFinalData()))
		{
			echo $finalData;
		}
		else
		{
			$this->checkoutForm = $model->getForm();
			parent::display($tpl);
		}
	}

	protected function getFinalData()
	{
		$data      = $this->cart->getCheckoutData();
		$finalData = null;

		if (isset($data['finalData']))
		{
			$finalData    = $data['finalData'];
			$vendorActive = $this->cart->getVendorActive();

			if (count($this->cartData) > 1 && isset($this->cartData[$vendorActive]['items']))
			{
				foreach ($this->cartData[$vendorActive]['items'] as $item)
				{
					$this->cart->removeItem($item['product']->id);
				}

				unset($data['finalData']);
				$this->cart->setCheckoutData($data);
			}
			else
			{
				$this->cart->setCheckoutData([]);
				$this->cart->destroy();
			}
		}

		return $finalData;
	}
}
