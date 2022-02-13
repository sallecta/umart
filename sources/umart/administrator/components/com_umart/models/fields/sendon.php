<?php
/**
 
 
 
 
 
 */

use Umart\Classes\Email;
use Joomla\CMS\Form\FormHelper;

defined('_JEXEC') or die;
FormHelper::loadFieldClass('list');

class JFormFieldSendOn extends JFormFieldList
{
	protected $type = 'SendOn';

	protected function getOptions()
	{
		$options  = parent::getOptions();
		$triggers = plg_sytem_umart_main(Email::class)->register();

		foreach ($triggers as $value => $text)
		{
			$option        = new stdClass;
			$option->value = $value;
			$option->text  = $text;
			$options[]     = $option;
		}

		return $options;
	}
}
