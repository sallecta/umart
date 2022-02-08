<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

if (easyshop('config', 'enable_track_order'))
{
	JLoader::register('EasyshopHelperRoute', ES_COMPONENT_SITE . '/helpers/route.php');

	if (easyshop('app')->input->get('option') !== 'com_easyshop')
	{
		easyshop()->addLangText([
			'COM_EASYSHOP_INPUT_INVALID_REQUIRED',
			'COM_EASYSHOP_INPUT_INVALID_MIN',
			'COM_EASYSHOP_INPUT_INVALID_MAX',
			'COM_EASYSHOP_INPUT_INVALID_REGEX',
			'COM_EASYSHOP_INPUT_INVALID_EMAIL',
			'COM_EASYSHOP_INPUT_INVALID_NUMBER',
		]);
	}

	require JModuleHelper::getLayoutPath('mod_easyshop_tracking', $params->get('layout', 'default'));
}
