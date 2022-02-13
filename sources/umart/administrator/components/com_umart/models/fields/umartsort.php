<?php
/**
 
 
 
 
 
 */

use Umart\Classes\Utility;
use Joomla\CMS\Form\FormHelper;

defined('_JEXEC') or die;
FormHelper::loadFieldClass('list');

class JFormFieldUmartSort extends JFormFieldList
{
	protected $type = 'UmartSort';

	protected function getOptions()
	{
		static $options = null;

		if (null === $options)
		{
			$options = parent::getOptions();
			$values  = (array) $this->value;

			foreach (plg_sytem_umart_main(Utility::class)->getOrderingData() as $data)
			{
				$option           = new stdClass;
				$option->value    = $data['value'];
				$option->text     = $data['text'];
				$option->selected = in_array($data['value'], $values);
				$options[]        = $option;
			}
		}

		return $options;
	}
}
