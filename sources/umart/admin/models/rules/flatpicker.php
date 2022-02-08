<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;

class JFormRuleFlatPicker extends FormRule
{
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
	{
		// If the field is empty and not required, the field is valid.
		$required = ((string) $element['required'] == 'true' || (string) $element['required'] == 'required');

		if (!$required && empty($value))
		{
			return true;
		}

		if (strtolower($value) == 'now')
		{
			return true;
		}

		try
		{
			$mode = (string) $element['mode'];

			if (empty($mode) || $mode === 'single')
			{
				return CMSFactory::getDate($value) instanceof Date;
			}

			$delimiter = $mode === 'range' ? EasyshopHelper::PICKER_RANGE_SEPARATOR : EasyshopHelper::PICKER_MULTIPLE_SEPARATOR;

			foreach (explode($delimiter, $value) as $date)
			{
				if (!CMSFactory::getDate($date) instanceof Date)
				{
					return false;
				}
			}

			return true;
		}
		catch (Exception $e)
		{
			return false;
		}
	}
}
