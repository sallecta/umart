<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\Component\ComponentHelper;

defined('_JEXEC') or die;

if (ComponentHelper::isEnabled('com_easyshop'))
{
	JLoader::register('ModEasyshopSearchHelper', __DIR__ . '/helper.php');
	ModEasyshopSearchHelper::loadFormLayout($params);
}
