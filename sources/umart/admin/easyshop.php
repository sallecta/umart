<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;
JLoader::register('ES\\Helper\\Navbar', ES_COMPONENT_ADMINISTRATOR . '/helpers/navbar.php');
JLoader::register('EasyshopHelper', ES_COMPONENT_ADMINISTRATOR . '/helpers/easyshop.php');
easyshop('dispatch');