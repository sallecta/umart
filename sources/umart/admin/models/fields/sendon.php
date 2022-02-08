<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use ES\Classes\Email;
use Joomla\CMS\Form\FormHelper;

defined('_JEXEC') or die;
FormHelper::loadFieldClass('list');

class JFormFieldSendOn extends JFormFieldList
{
	protected $type = 'SendOn';

	protected function getOptions()
	{
		$options  = parent::getOptions();
		$triggers = easyshop(Email::class)->register();

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
