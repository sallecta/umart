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

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Application\CMSApplication;
use ES\Classes\Renderer;
use JDatabaseDriver;

class PluginLegacy extends CMSPlugin
{
	/**
	 * @var CMSApplication
	 * @since 1.0.0
	 */
	protected $app;
	/**
	 * @var  JDatabaseDriver
	 * @since 1.0.0
	 */
	protected $db;
	protected $autoloadLanguage = true;

	public function __construct($subject, array $config = [])
	{
		$this->app = easyshop('app');
		$this->db  = easyshop('db');
		parent::__construct($subject, $config);
		easyshop('state')->set('plugin.' . $this->_type . '.' . $this->_name . '.renderer', $this->getRenderer());
	}

	protected function getRenderer()
	{
		$renderer = new Renderer;
		$renderer->setBasePath(JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/layouts');
		$renderer->refreshDefaultPaths();

		return $renderer;
	}

	protected function getConfig($mergeParams = true)
	{
		$config = clone easyshop('config');

		if ($mergeParams)
		{
			foreach ($this->params->toArray() as $name => $value)
			{
				if ($config->exists($name) && trim($value) !== '')
				{
					$config->set($name, $value);
				}
			}
		}

		return $config;
	}
}
