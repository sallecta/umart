<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;
JLoader::register('ModEasyshopCategoriesHelper', __DIR__ . '/helper.php');
$source   = $params->get('source', 'category');
$base     = $params->get('base_' . $source);
$instance = $source == 'category' ? 'easyshop.product' : 'easyshop.brand';

if ($root = JCategories::getInstance($instance)->get($base))
{
	$layout         = $params->get('layout', 'default');
	$activeId       = $source == 'category' ? ModEasyshopCategoriesHelper::getActiveId() : 0;
	$moduleClassSfx = htmlspecialchars($params->get('moduleclass_sfx'));

	require JModuleHelper::getLayoutPath('mod_easyshop_categories', $layout);
}
