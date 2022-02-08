<?php

/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ES;

defined('_JEXEC') or die;

use JLoader;

class Loader
{
	public static function register()
	{
		static $loaded = false;

		if ($loaded)
		{
			return;
		}

		$loaded = true;

		// Register PSR4
		JLoader::registerNamespace('ES', ES_LIBRARIES, false, false, 'psr4');

		// Class
		JLoader::registerAlias('ES\\Addon', 'ES\\Classes\\Addon', '4.0');
		JLoader::registerAlias('ES\\Cart', 'ES\\Classes\\Cart', '4.0');
		JLoader::registerAlias('ES\\Currency', 'ES\\Classes\\Currency', '4.0');
		JLoader::registerAlias('ES\\CustomField', 'ES\\Classes\\CustomField', '4.0');
		JLoader::registerAlias('ES\\Discount', 'ES\\Classes\\Discount', '4.0');
		JLoader::registerAlias('ES\\Email', 'ES\\Classes\\Email', '4.0');
		JLoader::registerAlias('ES\\Event', 'ES\\Classes\\Event', '4.0');
		JLoader::registerAlias('ES\\Html', 'ES\\Classes\\Html', '4.0');
		JLoader::registerAlias('ES\\Log', 'ES\\Classes\\Log', '4.0');
		JLoader::registerAlias('ES\\Media', 'ES\\Classes\\Media', '4.0');
		JLoader::registerAlias('ES\\Method', 'ES\\Classes\\Method', '4.0');
		JLoader::registerAlias('ES\\Order', 'ES\\Classes\\Order', '4.0');
		JLoader::registerAlias('ES\\Params', 'ES\\Classes\\Params', '4.0');
		JLoader::registerAlias('ES\\Privacy', 'ES\\Classes\\Privacy', '4.0');
		JLoader::registerAlias('ES\\Product', 'ES\\Classes\\Product', '4.0');
		JLoader::registerAlias('ES\\Renderer', 'ES\\Classes\\Renderer', '4.0');
		JLoader::registerAlias('ES\\LayoutHelper', 'ES\\Classes\\Renderer', '4.0');
		JLoader::registerAlias('ES\\StringHelper', 'ES\\Classes\\StringHelper', '4.0');
		JLoader::registerAlias('ES\\System', 'ES\\Classes\\System', '4.0');
		JLoader::registerAlias('ES\\Tags', 'ES\\Classes\\Tags', '4.0');
		JLoader::registerAlias('ES\\User', 'ES\\Classes\\User', '4.0');
		JLoader::registerAlias('ES\\Utility', 'ES\\Classes\\Utility', '4.0');
		JLoader::registerAlias('ES\\Zone', 'ES\\Classes\\Zone', '4.0');

		// Controller
		JLoader::registerAlias('ES\\Controller\\ControllerAdmin', 'ES\\Controller\\AdminController', '4.0');
		JLoader::registerAlias('ES\\Controller\\ControllerForm', 'ES\\Controller\\FormController', '4.0');
		JLoader::registerAlias('ES\\Controller\\ControllerLegacy', 'ES\\Controller\\BaseController', '4.0');

		// Model
		JLoader::registerAlias('ES\\Model\\ModelAdmin', 'ES\\Model\\AdminModel', '4.0');
		JLoader::registerAlias('ES\\Model\\ModelList', 'ES\\Model\\ListModel', '4.0');

		// View
		JLoader::registerAlias('ES\\View\\ViewLegacy', 'ES\\View\\BaseView', '4.0');
		JLoader::registerAlias('ES\\View\\ViewItem', 'ES\\View\\ItemView', '4.0');
		JLoader::registerAlias('ES\\View\\ViewList', 'ES\\View\\ListView', '4.0');

		// Table
		JLoader::registerAlias('ES\\Table\\TableAbstract', 'ES\\Table\\AbstractTable', '4.0');
	}
}

Loader::register();