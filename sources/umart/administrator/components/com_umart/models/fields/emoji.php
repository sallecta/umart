<?php
/**
 
 
 
 
 
 */

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;
FormHelper::loadFieldClass('textarea');

class JFormFieldEmoji extends JFormFieldTextarea
{
	protected $type = 'Emoji';

	protected function getInput()
	{
		HTMLHelper::script('com_umart/emoji.js', ['relative' => true]);
		$this->class = rtrim('input-emoji ' . $this->class);

		return parent::getInput();
	}
}
