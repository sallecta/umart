<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;
JFormHelper::loadFieldClass('list');

class JFormFieldImageSize extends JFormFieldList
{
	protected $type = 'ImageSize';

	protected function getOptions()
	{
		return array_merge(parent::getOptions(), [
			['value' => 'tiny', 'text' => JText::_('COM_EASYSHOP_TINY_SIZE')],
			['value' => 'small', 'text' => JText::_('COM_EASYSHOP_SMALL_SIZE')],
			['value' => 'medium', 'text' => JText::_('COM_EASYSHOP_MEDIUM_SIZE')],
			['value' => 'large', 'text' => JText::_('COM_EASYSHOP_LARGE_SIZE')],
			['value' => 'xlarge', 'text' => JText::_('COM_EASYSHOP_XLARGE_SIZE')],
		]);
	}
}
