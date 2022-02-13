<?php
/**
 
 
 
 
 
 */

defined('_JEXEC') or die;
JFormHelper::loadFieldClass('checkboxes');

class JFormFieldInline extends JFormFieldCheckboxes
{
	protected $type = 'Inline';

	protected function getInput()
	{
		$data      = parent::getLayoutData();
		$extraData = array(
			'options' => $this->getOptions(),
			'value'   => $this->value,
		);

		return plg_sytem_umart_main('renderer')->render('form.field.inline', array_merge($data, $extraData));
	}
}
