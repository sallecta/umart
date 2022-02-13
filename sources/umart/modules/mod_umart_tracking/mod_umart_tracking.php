<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

if (umart('config', 'enable_track_order'))
{
	JLoader::register('UmartHelperRoute', UMART_COMPONENT_SITE . '/helpers/route.php');

	if (umart('app')->input->get('option') !== 'com_umart')
	{
		umart()->addLangText([
			'COM_UMART_INPUT_INVALID_REQUIRED',
			'COM_UMART_INPUT_INVALID_MIN',
			'COM_UMART_INPUT_INVALID_MAX',
			'COM_UMART_INPUT_INVALID_REGEX',
			'COM_UMART_INPUT_INVALID_EMAIL',
			'COM_UMART_INPUT_INVALID_NUMBER',
		]);
	}

	require JModuleHelper::getLayoutPath('mod_umart_tracking', $params->get('layout', 'default'));
}
