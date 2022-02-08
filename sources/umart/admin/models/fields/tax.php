<?php
/**
 * @package     com_easyshop
 * @version     1.0.5
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
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
			$taxesModel = easyshop('model', 'taxes', ES_COMPONENT_ADMINISTRATOR);
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

				easyshop('doc')->addScriptDeclaration('
					_es.$(document).ready(function($){
						var taxRate = ' . json_encode($taxRates) . '
						_es.setData("taxRate", taxRate);
					});
				');

				$options = array_merge($options, $taxes);
			}
		}

		return $options;
	}
}
