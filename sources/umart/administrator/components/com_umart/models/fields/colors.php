<?php
/**
 
 
 
 
 
 */

defined('_JEXEC') or die;
JFormHelper::loadFieldClass('checkboxes');

class JFormFieldColors extends JFormFieldCheckboxes
{
	protected $type = 'Colors';

	protected function getInput()
	{
		$data      = parent::getLayoutData();
		$extraData = array(
			'options' => $this->getOptions(),
			'value'   => $this->value,
		);

		return plg_sytem_umart_main('renderer')->render('form.field.colors', array_merge($data, $extraData));
	}
}
