<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use ES\Classes\Currency;
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
			$currencyClass = easyshop(Currency::class);
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
