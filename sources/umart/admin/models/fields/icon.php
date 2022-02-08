<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
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
			preg_match_all('/id=\"(es\-icon\-[a-zA-Z0-9\-_]+)\"/', file_get_contents(ES_MEDIA . '/images/icons.svg'), $matches);
			$icons = $matches[1];
		}

		return easyshop('renderer')->render('form.field.icon', [
			'icons' => $icons,
			'field' => $this,
			'input' => parent::getInput(),
		]);
	}
}
