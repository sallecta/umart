<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
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
		HTMLHelper::script('com_easyshop/emoji.js', ['relative' => true]);
		$this->class = rtrim('input-emoji ' . $this->class);

		return parent::getInput();
	}
}
