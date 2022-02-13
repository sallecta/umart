<?php
/**
 
 
 
 
 
 */

defined('_JEXEC') or die;
JFormHelper::loadFieldClass('list');

class JFormFieldImageSize extends JFormFieldList
{
	protected $type = 'ImageSize';

	protected function getOptions()
	{
		return array_merge(parent::getOptions(), [
			['value' => 'tiny', 'text' => JText::_('COM_UMART_TINY_SIZE')],
			['value' => 'small', 'text' => JText::_('COM_UMART_SMALL_SIZE')],
			['value' => 'medium', 'text' => JText::_('COM_UMART_MEDIUM_SIZE')],
			['value' => 'large', 'text' => JText::_('COM_UMART_LARGE_SIZE')],
			['value' => 'xlarge', 'text' => JText::_('COM_UMART_XLARGE_SIZE')],
		]);
	}
}
