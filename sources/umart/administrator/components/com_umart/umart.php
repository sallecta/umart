<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
JLoader::register('Umart\\Helper\\Navbar', UMART_COMPONENT_ADMINISTRATOR . '/helpers/navbar.php');
JLoader::register('UmartHelper', UMART_COMPONENT_ADMINISTRATOR . '/helpers/umart.php');
plg_sytem_umart_main('dispatch');