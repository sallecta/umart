<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ES\Plugin;

defined('_JEXEC') or die;

class PluginDispatch extends PluginAddon
{
	protected $taskMaps = [];
	protected static $task;
	protected static $view;

	public function __construct($subject, array $config = array())
	{
		parent::__construct($subject, $config);

		if (!isset(self::$task))
		{
			$command    = $this->app->input->get('task', 'display');
			self::$view = $this->app->input->get('view');

			if (strpos($command, '.') !== false)
			{
				$tasks      = explode('.', $command, 2);
				self::$task = $tasks[0];
			}
			else
			{
				self::$task = $command;
			}
		}

		$baseDir    = JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/dispatch';
		$basePath   = 'ES_PLUGIN_' . strtoupper($this->_name);
		$layoutPath = $basePath . '_LAYOUT';
		$sitePath   = $basePath . '_SITE';
		$adminPath  = $basePath . '_ADMINISTRATOR';

		if (!defined($basePath))
		{
			define($basePath, $baseDir . '/' . ($this->app->getName() == 'site' ? 'site' : 'administrator'));
		}

		if (!defined($layoutPath))
		{
			define($layoutPath, JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/layouts');
		}

		if (!defined($sitePath))
		{
			define($sitePath, $baseDir . '/site');
		}

		if (!defined($adminPath))
		{
			define($adminPath, $baseDir . '/administrator');
		}
	}

	public function onEasyshopBeforeDispatch(&$config)
	{
		if (empty($this->taskMaps) || defined('ES_PLUGIN_DISPATCHED'))
		{
			return;
		}

		if (self::$task === 'display' || empty(self::$task))
		{
			require_once ES_COMPONENT . '/controller.php';
		}

		if (in_array(self::$task, $this->taskMaps, true)
			|| in_array(self::$view, $this->taskMaps, true)
		)
		{
			$config['base_path'] = constant('ES_PLUGIN_' . strtoupper($this->_name));
			define('ES_PLUGIN_DISPATCHED', true);

			if (!empty(self::$view))
			{
				easyshop('state')->set('view.' . self::$view . '.renderer', $this->getRenderer());
			}
		}
	}
}
