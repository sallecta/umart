<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\CustomField;
use ES\Classes\Order;
use ES\Model\AdminModel;

class EasyshopModelOrder extends AdminModel
{
	public function getForm($data = [], $loadData = true)
	{
		if ($form = parent::getForm($data, $loadData))
		{
			/**
			 * @var $field          CustomField
			 * @var $order          Order
			 */

			$id           = (int) $this->getState('order.id');
			$order        = easyshop(Order::class);
			$field        = easyshop(CustomField::class, [
				'reflector' => 'com_easyshop.user',
			]);
			$billingForm  = $field->getFormFieldData(0, ['checkout_field' => 1], 'billing');
			$shippingForm = $field->getFormFieldData(0, ['checkout_field' => 1], 'shipping');
			$field->setUp(['reflector' => 'com_easyshop.checkout']);
			$checkoutForm = $field->getFormFieldData(0, [], 'checkout');

			if ($id < 1 || !$order->load($id))
			{
				$loadData = false;
			}

			$loadForm = function ($name, $formData) use ($form, $field, $order, $loadData) {
				if ($form->load($formData['form']) && $loadData)
				{
					$values = [];
					$fields = $name == 'checkout' ? $order->checkoutFields : $order->address[$name];

					foreach ($fields as $field)
					{
						$values[$field->customfield_id] = $field->value;
					}

					$form->bind([$name => $values]);
				}
			};

			if ($loadData)
			{
				$form->bind([
					'total_price' => $order->get('total_price', 0.00),
					'total_taxes' => $order->get('total_taxes', 0.00),
				]);
			}

			$loadForm('billing', $billingForm);
			$loadForm('shipping', $shippingForm);
			$loadForm('checkout', $checkoutForm);

			if (!easyshop('config', 'multi_currencies_mode'))
			{
				$form->setFieldAttribute('currency_id', 'readonly', 'readonly');
			}

			$vendorId = (int) $order->get('vendor_id', 0);
			$form->setFieldAttribute('payment_id', 'vendor_id', $vendorId);
			$form->setFieldAttribute('shipping_id', 'vendor_id', $vendorId);
		}

		return $form;
	}
}
