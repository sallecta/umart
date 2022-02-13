<?php

/**
 
 
 
 
 
 */

namespace Umart;

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
		JLoader::registerNamespace('Umart', UMART_LIBRARIES, false, false, 'psr4');

		// Class
		JLoader::registerAlias('Umart\\Addon', 'Umart\\Classes\\Addon', '4.0');
		JLoader::registerAlias('Umart\\Cart', 'Umart\\Classes\\Cart', '4.0');
		JLoader::registerAlias('Umart\\Currency', 'Umart\\Classes\\Currency', '4.0');
		JLoader::registerAlias('Umart\\CustomField', 'Umart\\Classes\\CustomField', '4.0');
		JLoader::registerAlias('Umart\\Discount', 'Umart\\Classes\\Discount', '4.0');
		JLoader::registerAlias('Umart\\Email', 'Umart\\Classes\\Email', '4.0');
		JLoader::registerAlias('Umart\\Event', 'Umart\\Classes\\Event', '4.0');
		JLoader::registerAlias('Umart\\Html', 'Umart\\Classes\\Html', '4.0');
		JLoader::registerAlias('Umart\\Log', 'Umart\\Classes\\Log', '4.0');
		JLoader::registerAlias('Umart\\Media', 'Umart\\Classes\\Media', '4.0');
		JLoader::registerAlias('Umart\\Method', 'Umart\\Classes\\Method', '4.0');
		JLoader::registerAlias('Umart\\Order', 'Umart\\Classes\\Order', '4.0');
		JLoader::registerAlias('Umart\\Params', 'Umart\\Classes\\Params', '4.0');
		JLoader::registerAlias('Umart\\Privacy', 'Umart\\Classes\\Privacy', '4.0');
		JLoader::registerAlias('Umart\\Product', 'Umart\\Classes\\Product', '4.0');
		JLoader::registerAlias('Umart\\Renderer', 'Umart\\Classes\\Renderer', '4.0');
		JLoader::registerAlias('Umart\\LayoutHelper', 'Umart\\Classes\\Renderer', '4.0');
		JLoader::registerAlias('Umart\\StringHelper', 'Umart\\Classes\\StringHelper', '4.0');
		JLoader::registerAlias('Umart\\System', 'Umart\\Classes\\System', '4.0');
		JLoader::registerAlias('Umart\\Tags', 'Umart\\Classes\\Tags', '4.0');
		JLoader::registerAlias('Umart\\User', 'Umart\\Classes\\User', '4.0');
		JLoader::registerAlias('Umart\\Utility', 'Umart\\Classes\\Utility', '4.0');
		JLoader::registerAlias('Umart\\Zone', 'Umart\\Classes\\Zone', '4.0');

		// Controller
		JLoader::registerAlias('Umart\\Controller\\ControllerAdmin', 'Umart\\Controller\\AdminController', '4.0');
		JLoader::registerAlias('Umart\\Controller\\ControllerForm', 'Umart\\Controller\\FormController', '4.0');
		JLoader::registerAlias('Umart\\Controller\\ControllerLegacy', 'Umart\\Controller\\BaseController', '4.0');

		// Model
		JLoader::registerAlias('Umart\\Model\\ModelAdmin', 'Umart\\Model\\AdminModel', '4.0');
		JLoader::registerAlias('Umart\\Model\\ModelList', 'Umart\\Model\\ListModel', '4.0');

		// View
		JLoader::registerAlias('Umart\\View\\ViewLegacy', 'Umart\\View\\BaseView', '4.0');
		JLoader::registerAlias('Umart\\View\\ViewItem', 'Umart\\View\\ItemView', '4.0');
		JLoader::registerAlias('Umart\\View\\ViewList', 'Umart\\View\\ListView', '4.0');

		// Table
		JLoader::registerAlias('Umart\\Table\\TableAbstract', 'Umart\\Table\\AbstractTable', '4.0');
	}
}

Loader::register();