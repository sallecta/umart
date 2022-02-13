<?php
/**
 
 
 
 
 
 */

use Umart\Classes\Order;
use Joomla\CMS\Form\FormHelper;

defined('_JEXEC') or die;
FormHelper::loadFieldClass('list');

class JFormFieldPaymentStatus extends JFormFieldList
{
	protected $type = 'PaymentStatus';

	protected function getOptions()
	{
		$options = parent::getOptions();
		$order   = plg_sytem_umart_main(Order::class);

		foreach ($order->getPaymentStatus() as $value => $text)
		{
			$option        = new stdClass;
			$option->value = $value;
			$option->text  = $text;
			$options[]     = $option;
		}

		if ($this->multiple
			&& !empty($this->value)
			&& !is_array($this->value)
		)
		{
			$this->value = explode(',', $this->value);
		}

		return $options;
	}
}
