<?php
/**
 * @version        1.1.4
 * @package        plg_system_umartukui
 * @author         JoomTech Team
 * @copyright      Copyright (C) 2015 - 2020 github.com/sallecta/umart All Rights Reserved.
 * @license        GNU General Public License, version 3
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;

defined('_JEXEC') or die;

if (!class_exists('plgSystemUmartUiInstallerScript'))
{
	class plgSystemUmartUiInstallerScript
	{
		protected $minimumPHPVersion = '5.4.0';
		protected $minimumJoomlaVersion = '3.8.0';
		protected $maximumJoomlaVersion = '4.9.999';

		public function postflight($route, $adapter)
		{
			if ($route == 'install' || $route == 'update')
			{
				if (!$this->checkRequirement())
				{
					return false;
				}
			}

			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->update($db->quoteName('#__extensions'))
				->set($db->quoteName('enabled') . ' = 1')
				->set($db->quoteName('protected') . ' = 1')
				->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
				->where($db->quoteName('element') . ' = ' . $db->quote('ui'))
				->where($db->quoteName('folder') . ' = ' . $db->quote('umart'));
			$db->setQuery($query)
				->execute();

			return true;
		}

		public function checkRequirement()
		{
			// Check the minimum PHP version
			if (!version_compare(PHP_VERSION, $this->minimumPHPVersion, 'ge'))
			{
				$msg = "<p>You need PHP $this->minimumPHPVersion or later to install this package</p>";
				throw new RuntimeException($msg);
			}

			// Check the minimum Joomla! version
			if (!version_compare(JVERSION, $this->minimumJoomlaVersion, 'ge'))
			{
				$msg = "<p>You need Joomla! $this->minimumJoomlaVersion or later to install this component</p>";
				throw new RuntimeException($msg);
			}

			// Check the maximum Joomla! version
			if (!version_compare(JVERSION, $this->maximumJoomlaVersion, 'le'))
			{
				$msg = "<p>You need Joomla! $this->maximumJoomlaVersion or earlier to install this component</p>";
				throw new RuntimeException($msg);
			}

			return true;
		}

		public function update($adapter)
		{
			$folders = [
				JPATH_PLUGINS . '/system/umartui/media/fonts',
				JPATH_PLUGINS . '/system/umartui/media/webfonts',
			];

			$files = [
				JPATH_PLUGINS . '/system/umartui/media/css/font-awesome.min.css',
			];

			foreach ($folders as $folder)
			{
				if (is_dir($folder))
				{
					Folder::delete($folder);
				}
			}

			foreach ($files as $file)
			{
				if (is_file($file))
				{
					File::delete($file);
				}
			}
		}
	}
}
