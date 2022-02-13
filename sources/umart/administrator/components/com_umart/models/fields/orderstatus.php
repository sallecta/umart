<?php
/**
 
 
 
 
 
 */

use Umart\Classes\Order;
use Joomla\CMS\Form\FormHelper;

defined('_JEXEC') or die;
FormHelper::loadFieldClass('list');

class JFormFieldOrderStatus extends JFormFieldList
{
	protected $type = 'OrderStatus';

	protected function getOptions()
	{
		$options  = parent::getOptions();
		$order    = plg_sytem_umart_main(Order::class);
		$excludes = trim($this->getAttribute('excludes', ''));

		if (!empty($excludes))
		{
			$excludes = explode(',', $this->getAttribute('excludes', ''));
		}

		foreach ($order->getOrderStatus() as $value => $text)
		{
			if (empty($excludes) || !in_array($value, $excludes))
			{
				$option        = new stdClass;
				$option->value = $value;
				$option->text  = $text;
				$options[]     = $option;
			}
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
