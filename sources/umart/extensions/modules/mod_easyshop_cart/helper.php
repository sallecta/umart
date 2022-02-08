<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

class ModEasyshopCartHelper
{
	public static function loadModuleAjax()
	{
		$input    = easyshop('app')->input;
		$moduleId = (int) $input->getUint('moduleId', 0);
		$modules  = ModuleHelper::getModuleList();

		foreach ($modules as $module)
		{
			if ((int) $module->id === $moduleId)
			{
				$cartModule = $module;
				break;
			}
		}

		if (!isset($cartModule) || $cartModule->module != 'mod_easyshop_cart')
		{
			$cartModule            = new stdClass;
			$cartModule->id        = 0;
			$cartModule->title     = '';
			$cartModule->module    = 'mod_easyshop_cart';
			$cartModule->position  = '';
			$cartModule->content   = '';
			$cartModule->showtitle = 0;
			$cartModule->control   = '';
			$cartModule->params    = $input->get('params', [], 'array');
		}

		return ModuleHelper::renderModule($cartModule);
	}
}
