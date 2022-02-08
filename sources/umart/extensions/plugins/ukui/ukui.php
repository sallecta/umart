<?php
/**
 * @version        1.1.4
 * @package        plg_system_ukui
 * @author         JoomTech Team
 * @copyright      Copyright (C) 2015 - 2020 www.joomtech.net All Rights Reserved.
 * @license        GNU General Public License, version 3
 */
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

class PlgSystemUkui extends CMSPlugin
{
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		if (!defined('UKUI_PATH'))
		{
			define('UKUI_PATH', JPATH_PLUGINS . '/system/ukui');
		}

		if (!defined('UKUI_MEDIA_PATH'))
		{
			define('UKUI_MEDIA_PATH', UKUI_PATH . '/media');
		}

		if (!defined('UKUI_MEDIA_URL'))
		{
			define('UKUI_MEDIA_URL', JUri::root(true) . '/plugins/system/ukui/media');
		}

		if (!defined('UKUI_ICON_URL'))
		{
			$icon = $this->params->get('load_awesome_icon', 'cdn');
			define('UKUI_ICON_URL', $icon ? 'https://use.fontawesome.com/releases/v5.3.1/css/all.css' : null);
		}

		if (!defined('UKUI_VERSION'))
		{
			define('UKUI_VERSION', '1.1.1');
		}

		if (!defined('UKUI_J4'))
		{
			define('UKUI_J4', version_compare(JVERSION, '4.0', 'ge'));
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
