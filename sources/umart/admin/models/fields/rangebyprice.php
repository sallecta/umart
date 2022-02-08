<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use ES\Classes\Currency;
use Joomla\CMS\Form\FormHelper;
use Joomla\Registry\Registry;

FormHelper::loadFieldClass('list');

class JFormFieldRangeByPrice extends JFormFieldList
{
	protected $type = 'RangeByPrice';

	protected function getOptions()
	{
		/** @var $currencyClass Currency */
		$currencyClass = easyshop(Currency::class);
		$currencyId    = (int) $this->getAttribute('currency_id', '0');

		if (!$currencyId)
		{
			$currencyId = $currencyClass->getActiveId();
		}

		$currencyClass->load($currencyId);
		$params  = new Registry($currencyClass->get('params', '{}'));
		$range   = $params->get('range_by_price', '0-49&#13;50-99&#13;100-200&#13;200-400&#13;500-0');
		$range   = preg_split('/\&\#13;|\r\n|\n/', $range);
		$options = parent::getOptions();

		foreach ($range as $rang)
		{
			if (strpos($rang, '-') !== false)
			{
				$parts         = explode('-', $rang, 2);
				$min           = (float) $parts[0];
				$max           = (float) $parts[1];
				$option        = new stdClass;
				$option->value = $min . '-' . $max;
				$option->text  = $currencyClass->toFormat($min) . ' - ' . $currencyClass->toFormat($max);

				if ($max < 0.01)
				{
					$option->text = '> ' . $currencyClass->toFormat($min);
				}

				$options[] = $option;
			}
		}

		return $options;
	}
}
