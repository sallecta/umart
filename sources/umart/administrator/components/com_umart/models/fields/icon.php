<?php
/**
 
 
 
 
 
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('hidden');

class JFormFieldIcon extends JFormFieldHidden
{
	protected $type = 'icon';

	protected function getInput()
	{
		static $icons = null;

		if (null === $icons)
		{
			preg_match_all('/id=\"(es\-icon\-[a-zA-Z0-9\-_]+)\"/', file_get_contents(UMART_MEDIA . '/images/icons.svg'), $matches);
			$icons = $matches[1];
		}

		return plg_sytem_umart_main('renderer')->render('form.field.icon', [
			'icons' => $icons,
			'field' => $this,
			'input' => parent::getInput(),
		]);
	}
}
