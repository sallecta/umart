<?php
/**
 * @version        1.1.4
 * @package        plg_system_umartukui
 * @author         JoomTech Team
 * @copyright      Copyright (C) 2015 - 2020 github.com/sallecta/umart All Rights Reserved.
 * @license        GNU General Public License, version 3
 */
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

class PlgSystemUmartUi extends CMSPlugin
{
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		if (!defined('UMARTUI_PATH'))
		{
			define('UMARTUI_PATH', JPATH_PLUGINS . '/system/umartui');
		}

		if (!defined('UMARTUI_MEDIA_PATH'))
		{
			define('UMARTUI_MEDIA_PATH', UMARTUI_PATH . '/media');
		}

		if (!defined('UMARTUI_MEDIA_URL'))
		{
			define('UMARTUI_MEDIA_URL', JUri::root(true) . '/plugins/system/umartui/media');
		}

		if (!defined('UMARTUI_ICON_URL'))
		{
			$icon = $this->params->get('load_awesome_icon', 'cdn');
			define('UMARTUI_ICON_URL', $icon ? 'https://use.fontawesome.com/releases/v5.3.1/css/all.css' : null);
		}

		if (!defined('UMARTUI_VERSION'))
		{
			define('UMARTUI_VERSION', '1.1.1');
		}

		if (!defined('UMARTUI_J4'))
		{
			define('UMARTUI_J4', version_compare(JVERSION, '4.0', 'ge'));
		}

		HTMLHelper::addIncludePath(__DIR__ . '/helpers/html');
		// Load official DR BUILDER COMPONENT
		if (ComponentHelper::isEnabled('com_drbuilder'))
		{
			JLoader::register('JoomTech\\Event\\DRBuilder', JPATH_ADMINISTRATOR . '/components/com_drbuilder/event.php');

			if (class_exists('JoomTech\\Event\\DRBuilder'))
			{
				$this->_subject->attach(new JoomTech\Event\DRBuilder($this->_subject, $config));
			}
		}
	}
}
