<?php
/**
 
 * @version     1.0.5
 
 
 
 */

defined('_JEXEC') or die;
JFormHelper::loadFieldClass('list');

class JFormFieldTax extends JFormFieldList
{
	protected $type = 'tax';

	protected function getOptions()
	{
		static $options = null;

		if (null === $options)
		{
			$options    = parent::getOptions();
			$taxesModel = plg_sytem_umart_main('model', 'taxes', UMART_COMPONENT_ADMINISTRATOR);
			$taxesModel->setState('list.select', 'a.id AS value, CONCAT(a.name, " (", a.rate, "%)") AS text, a.rate');
			$taxesModel->setState('filter.vendor_id', 0);
			$taxesModel->setState('filter.published', 1);

			if ($taxes = $taxesModel->getItems())
			{
				$taxRates = [];

				foreach ($taxes as $tax)
				{
					$taxRates['tax_' . $tax->value] = (int) $tax->rate;
				}

				plg_sytem_umart_main('doc')->addScriptDeclaration('
					_umart.$(document).ready(function($){
						var taxRate = ' . json_encode($taxRates) . '
						_umart.setData("taxRate", taxRate);
					});
				');

				$options = array_merge($options, $taxes);
			}
		}

		return $options;
	}
}
