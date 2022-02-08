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
use ES\Classes\CustomField;
use ES\Classes\Currency;
use ES\Form\Form;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;

class EasyshopModelCheckout extends BaseDatabaseModel
{
	public function getForm($loadData = true)
	{
		/**
		 * @var Cart        $cart
		 * @var CustomField $customField
		 * @var Form        $form
		 */

		Form::addFieldPath(ES_COMPONENT_ADMINISTRATOR . '/models/fields');
		Form::addRulePath(ES_COMPONENT_ADMINISTRATOR . '/models/rules');
		$cart = easyshop(Cart::class);
		$form = new Form('com_easyshop.checkout', ['control' => 'jform']);
		$data = $cart->getCheckoutData();

		if ($form->loadFile(ES_COMPONENT_SITE . '/models/forms/checkout.xml'))
		{
			$config = ['reflector' => 'com_easyshop.user'];
			$user   = CMSFactory::getUser();

			if ($user->id)
			{
				Table::addIncludePath(ES_COMPONENT_ADMINISTRATOR . '/tables');
				$userTable = Table::getInstance('User', 'EasyshopTable');

				if ($userTable->load(['user_id' => $user->id]))
				{
					$config['reflector_id'] = $userTable->id;
				}
			}

			$customField  = easyshop(CustomField::class, $config);
			$billingForm  = $customField->getFormFieldData(0, ['checkout_field' => '1'], 'billing_address');
			$shippingForm = $customField->getFormFieldData(0, ['checkout_field' => '1'], 'shipping_address');

			if ($form->load($billingForm['form']) && $form->load($shippingForm['form']))
			{
				if ($customField->findFieldByName('user_name'))
				{
					$form->removeField('name', 'registration');
				}
			}
			else
			{
				throw new RuntimeException('Easyshop Error. Checkout form not found.', 404);
			}

			// @since 1.2.0 Checkout form fields
			$customField->setUp(
				[
					'reflector'    => 'com_easyshop.checkout',
					'reflector_id' => 0,
				]
			);

			$checkoutForm = $customField->getFormFieldData(0, [], 'checkoutFields');
			$form->load($checkoutForm['form']);

			if (empty($data['billing_address']))
			{
				$data['billing_address'] = $billingForm['data'];
			}
			else
			{
				foreach ($data['billing_address'] as $id => $field)
				{
					if (!isset($field['value']))
					{
						break;
					}

					$data['billing_address'][$id] = $field['value'];
				}
			}

			if (empty($data['shipping_address']))
			{
				$data['shipping_address'] = $shippingForm['data'];
			}
			else
			{
				if (empty($data['address_different']))
				{
					$data['shipping_address'] = $data['billing_address'];
				}
				else
				{
					foreach ($data['shipping_address'] as $id => $field)
					{
						if (!isset($field['value']))
						{
							break;
						}

						$data['shipping_address'][$id] = $field['value'];
					}
				}
			}
		}

		if (easyshop('config', 'terms_and_conditions', 1))
		{
			$url    = trim(easyshop('config', 'tnc_url', ''));
			$string = Text::_('COM_EASYSHOP_TERMS_AND_CONDITIONS');

			if (empty($url))
			{
				$label = Text::sprintf('COM_EASYSHOP_TERMS_AND_CONDITIONS_CONFIRM_NOTE', $string);
			}
			else
			{
				if (is_numeric($url))
				{
					$url = Route::_('index.php?Itemid=' . (int) $url, false);
				}
				else
				{
					$url = str_ireplace('{rootUrl}', Uri::root(true), $url);
				}

				$url   = '<a href="' . htmlspecialchars($url) . '" target="_blank">' . $string . '</a>';
				$label = Text::sprintf('COM_EASYSHOP_TERMS_AND_CONDITIONS_CONFIRM_NOTE', $url);
			}

			$form->setFieldAttribute('terms_and_conditions', 'label', $label, 'confirm');
		}
		else
		{
			$form->removeField('terms_and_conditions', 'confirm');
		}

		if ($loadData && !empty($data))
		{
			if (!empty($data['checkoutFields']))
			{
				/** @var Currency $currency */
				$currency = easyshop(Currency::class)->getActive();

				foreach ($data['checkoutFields'] as $fieldId => $fieldValue)
				{
					if (isset($data['checkoutFieldsDetails'][$fieldId]))
					{
						$price  = $data['checkoutFieldsDetails'][$fieldId]['price'];
						$prefix = $price > 0 ? '+' : '-';
						$form->setFieldAttribute($fieldId, 'extraHint', $prefix . $currency->toFormat($price, true), 'checkoutFields');
					}
				}
			}

			$form->bind($data);
		}

		easyshop('app')->triggerEvent('onEasyshopPrepareForm', [$form, $data]);

		return $form;
	}
}
