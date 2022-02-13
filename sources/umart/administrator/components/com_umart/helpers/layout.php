<?php
/**
 *  @package     com_umart
 *  @version     1.0.5
 *  @Author      JoomTech Team
* @copyright   Copyright (C) 2015 - 2019 github.com/sallecta/umart All Rights Reserved.
 *  @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

class UmartHelperLayout
{
	protected static $includePaths = array();

	public static function addIncludePath($paths)
	{
		if (!empty($paths))
		{
			settype($paths, 'array');

			foreach ($paths as $includePath)
			{
				array_unshift(self::$includePaths, $includePath);
			}
		}

	}

	public static function render($layoutId, $displayData = array(), $resetPath = true)
	{
		if (true === $resetPath)
		{
			self::addIncludePath(
				array(
					JPATH_BASE . '/components/com_umart/layouts',
					JPATH_THEMES . '/' . plg_sytem_umart_main('app')->getTemplate() . '/html/layouts/com_umart'
				)
			);
		}

		$rawPath = str_replace('.', '/', $layoutId) . '.php';
		$path    = JPath::find(self::$includePaths, $rawPath);

		if (!empty($path))
		{
			ob_start();
			include $path;

			return ob_get_clean();
		}

		return false;
	}

}
