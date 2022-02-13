<?php
/**
 
 
 
 
 
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

			$delimiter = $mode === 'range' ? UmartHelper::PICKER_RANGE_SEPARATOR : UmartHelper::PICKER_MULTIPLE_SEPARATOR;

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
