<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class JFormFieldTagDisplay extends JFormField
{
	protected $type = 'TagDisplay';

	protected function getInput()
	{
		$tagModule = JModuleHelper::getModule('mod_easyshop_tags');

		return JModuleHelper::renderModule($tagModule);
	}
}
