<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

class ModUmartCartHelper
{
	public static function loadModuleAjax()
	{
		$input    = umart('app')->input;
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

		if (!isset($cartModule) || $cartModule->module != 'mod_umart_cart')
		{
			$cartModule            = new stdClass;
			$cartModule->id        = 0;
			$cartModule->title     = '';
			$cartModule->module    = 'mod_umart_cart';
			$cartModule->position  = '';
			$cartModule->content   = '';
			$cartModule->showtitle = 0;
			$cartModule->control   = '';
			$cartModule->params    = $input->get('params', [], 'array');
		}

		return ModuleHelper::renderModule($cartModule);
	}
}
