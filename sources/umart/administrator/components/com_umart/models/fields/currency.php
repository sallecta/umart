<?php
/**
 
 
 
 
 
 */

use Umart\Classes\Currency;
use Joomla\CMS\Form\FormHelper;

defined('_JEXEC') or die;
FormHelper::loadFieldClass('list');

class JFormFieldCurrency extends JFormFieldList
{
	protected $type = 'currency';

	protected function getOptions()
	{
		static $options = null;

		if (null === $options)
		{
			/** @var $currencyClass Currency */
			$currencyClass = plg_sytem_umart_main(Currency::class);
			$options       = parent::getOptions();

			foreach ($currencyClass->getList() as $currency)
			{
				$option        = new stdClass;
				$option->value = $currency->id;
				$option->text  = $currency->name;
				$options[]     = $option;
			}
		}

		return $options;
	}
}
