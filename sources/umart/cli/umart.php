<?php
/**
 
 
 
 
 
 */

const _JEXEC = 1;

error_reporting(E_ALL | E_NOTICE);
ini_set('display_errors', 1);

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_LIBRARIES . '/import.legacy.php';
require_once JPATH_LIBRARIES . '/cms.php';

// Load the configuration
require_once JPATH_CONFIGURATION . '/configuration.php';
JLoader::import('joomla.filesystem.folder');
JLoader::import('joomla.filesystem.file');

use Joomla\CMS\Application\CliApplication;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Filesystem\Folder;

class UmartCron extends CliApplication
{
	protected function loadPlugins($group, &$count, \Umart\Log $log)
	{
		if (is_dir(JPATH_PLUGINS . '/' . $group))
		{
			$folders = Folder::folders(JPATH_PLUGINS . '/' . $group, '[a-zA-Z0-9_\-]', false, true);

			foreach ($folders as $folder)
			{
				$name = basename($folder);

				if (is_file($folder . '/' . $name . '.php') && PLuginHelper::isEnabled($group, $name))
				{
					require_once $folder . '/' . $name . '.php';
					$class = 'Plg' . ucfirst($group) . ucfirst($name);

					if (class_exists($class) && is_callable($class . '::onUmartExecuteCron'))
					{
						$result = call_user_func($class . '::onUmartExecuteCron');
						$count++;

						if (!empty($result) && is_string($result))
						{
							$log->addEntry('com_umart.cron', 'COM_UMART_CRON_EXECUTED_FORMAT', [$result]);
							$this->out("\n$result");
						}
						else
						{
							$log->addEntry('com_umart.cron', 'COM_UMART_CRON_EXECUTED_FORMAT', [$class . '::onUmartExecuteCron']);
						}
					}
				}
			}
		}
	}

	protected function doExecute()
	{
		if (!defined('UMART_MEDIA_URL'))
		{
			define('UMART_MEDIA_URL', '/media/com_umart');
		}

		require_once JPATH_PLUGINS . '/umart/system/system.php';
		plgSystemUmart::defines();
		$factory = \Umart\Factory::getInstance();
		$factory->addIncludeClassPath(JPATH_LIBRARIES . '/umart/classes');
		$log = $factory->getClass('Log');

		try
		{
			$this->out('@package    com_umart');
			$this->out('@version    ' . UMART_VERSION);
			$this->out('@Author     JoomTech Team');
			$this->out('@copyright  Copyright (C) 2015 - 2019 All Rights Reserved.');
			$this->out('@license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html');
			$this->out("\n================ Fetching cron jobs ================\n");
			$count = 0;
			$this->loadPlugins('umart', $count, $log);
			$this->loadPlugins('umartshipping', $count, $log);
			$this->loadPlugins('umart_payment', $count, $log);

			$this->out("\n================ Finished cron jobs ================\n");
			$this->out("\n================ {$count} jobs executed ================\n");
		}
		catch (\Exception $e)
		{
			$log->addEntry('com_umart.cron', 'COM_UMART_CRON_FAIL_FORMAT', [$e->getMessage()]);
			$this->out("\nFetching error: " . $e->getMessage());
		}
	}
}

CliApplication::getInstance('UmartCron')->execute();