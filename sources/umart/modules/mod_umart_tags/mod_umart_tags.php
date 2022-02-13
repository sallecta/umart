<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

JLoader::register('ModUmartTagsHelper', __DIR__ . '/helper.php');
$tags = ModUmartTagsHelper::getTags();

if (JComponentHelper::isEnabled('com_umart') && !empty($tags))
{
	require JModuleHelper::getLayoutPath('mod_umart_tags', $params->get('layout', 'default'));
}
