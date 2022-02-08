<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

if ($params->get('prepare'))
{
	JPluginHelper::importPlugin('easyshop');
}

JLoader::register('ModEasyshopProductsHelper', __DIR__ . '/helper.php');
$items = ModEasyshopProductsHelper::getItems($params);

if (!empty($items))
{
	/**
	 * @var $config \Joomla\Registry\Registry
	 * @since 1.0.0
	 */
	$moduleClassSfx = htmlspecialchars($params->get('moduleclass_sfx'));
	$config         = clone easyshop('config');

	foreach ($params->toArray() as $name => $value)
	{
		if (!$config->exists($name) || trim($value) !== '')
		{
			$config->set($name, $value);
		}
	}

	$categories = ModEasyshopProductsHelper::getCategories();
	$layout     = $params->get('layout', 'default');

	require JModuleHelper::getLayoutPath('mod_easyshop_products', $layout);
}
