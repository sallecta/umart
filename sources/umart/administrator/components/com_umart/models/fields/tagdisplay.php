<?php
/**
 
 
 
 
 
 */

defined('_JEXEC') or die;

class JFormFieldTagDisplay extends JFormField
{
	protected $type = 'TagDisplay';

	protected function getInput()
	{
		$tagModule = JModuleHelper::getModule('mod_umart_tags');

		return JModuleHelper::renderModule($tagModule);
	}
}
