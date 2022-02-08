<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

JLoader::register('ModEasyshopTagsHelper', __DIR__ . '/helper.php');
$tags = ModEasyshopTagsHelper::getTags();

if (JComponentHelper::isEnabled('com_easyshop') && !empty($tags))
{
	require JModuleHelper::getLayoutPath('mod_easyshop_tags', $params->get('layout', 'default'));
}
