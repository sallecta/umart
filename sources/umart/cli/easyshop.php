<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
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

class EasyShopCron extends CliApplication
{
	protected function loadPlugins($group, &$count, \ES\Log $log)
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

					if (class_exists($class) && is_callable($class . '::onEasyshopExecuteCron'))
					{
						$result = call_user_func($class . '::onEasyshopExecuteCron');
						$count++;

						if (!empty($result) && is_string($result))
						{
							$log->addEntry('com_easyshop.cron', 'COM_EASYSHOP_CRON_EXECUTED_FORMAT', [$result]);
							$this->out("\n$result");
						}
						else
						{
							$log->addEntry('com_easyshop.cron', 'COM_EASYSHOP_CRON_EXECUTED_FORMAT', [$class . '::onEasyshopExecuteCron']);
						}
					}
				}
			}
		}
	}

	protected function doExecute()
	{
		if (!defined('ES_MEDIA_URL'))
		{
			define('ES_MEDIA_URL', '/media/com_easyshop');
		}

		require_once JPATH_PLUGINS . '/system/easyshop/easyshop.php';
		PlgSystemEasyshop::defines();
		$factory = \ES\Factory::getInstance();
		$factory->addIncludeClassPath(JPATH_LIBRARIES . '/easyshop/classes');
		$log = $factory->getClass('Log');

		try
		{
			$this->out('@package    com_easyshop');
			$this->out('@version    ' . ES_VERSION);
			$this->out('@Author     JoomTech Team');
			$this->out('@copyright  Copyright (C) 2015 - 2019 All Rights Reserved.');
			$this->out('@license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html');
			$this->out("\n================ Fetching cron jobs ================\n");
			$count = 0;
			$this->loadPlugins('easyshop', $count, $log);
			$this->loadPlugins('easyshopshipping', $count, $log);
			$this->loadPlugins('easyshoppayment', $count, $log);

			$this->out("\n================ Finished cron jobs ================\n");
			$this->out("\n================ {$count} jobs executed ================\n");
		}
		catch (\Exception $e)
		{
			$log->addEntry('com_easyshop.cron', 'COM_EASYSHOP_CRON_FAIL_FORMAT', [$e->getMessage()]);
			$this->out("\nFetching error: " . $e->getMessage());
		}
	}
}

CliApplication::getInstance('EasyShopCron')->execute();