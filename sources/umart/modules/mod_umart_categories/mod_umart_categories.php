<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
JLoader::register('ModUmartCategoriesHelper', __DIR__ . '/helper.php');
$source   = $params->get('source', 'category');
$base     = $params->get('base_' . $source);
$instance = $source == 'category' ? 'umart.product' : 'umart.brand';

if ($root = JCategories::getInstance($instance)->get($base))
{
	$layout         = $params->get('layout', 'default');
	$activeId       = $source == 'category' ? ModUmartCategoriesHelper::getActiveId() : 0;
	$moduleClassSfx = htmlspecialchars($params->get('moduleclass_sfx'));

	require JModuleHelper::getLayoutPath('mod_umart_categories', $layout);
}
