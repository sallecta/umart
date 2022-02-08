<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
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

		return easyshop('renderer')->render('form.field.colors', array_merge($data, $extraData));
	}
}
