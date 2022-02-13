<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\CustomField;
use Umart\Classes\Order;
use Umart\Model\AdminModel;

class UmartModelOrder extends AdminModel
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
			$order        = plg_sytem_umart_main(Order::class);
			$field        = plg_sytem_umart_main(CustomField::class, [
				'reflector' => 'com_umart.user',
			]);
			$billingForm  = $field->getFormFieldData(0, ['checkout_field' => 1], 'billing');
			$shippingForm = $field->getFormFieldData(0, ['checkout_field' => 1], 'shipping');
			$field->setUp(['reflector' => 'com_umart.checkout']);
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

			if (!plg_sytem_umart_main('config', 'multi_currencies_mode'))
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
