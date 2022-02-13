<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

JLoader::register('UmartHelper', UMART_COMPONENT_ADMINISTRATOR . '/helpers/umart.php');
JLoader::register('UmartHelperRoute', UMART_COMPONENT_SITE . '/helpers/route.php');
plg_sytem_umart_main('dispatch');