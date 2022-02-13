<?php
/**
 
 
 
 
 
 */

namespace Umart\Plugin;
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Application\CMSApplication;
use Umart\Classes\Renderer;
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
		$this->app = plg_sytem_umart_main('app');
		$this->db  = plg_sytem_umart_main('db');
		parent::__construct($subject, $config);
		plg_sytem_umart_main('state')->set('plugin.' . $this->_type . '.' . $this->_name . '.renderer', $this->getRenderer());
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
		$config = clone plg_sytem_umart_main('config');

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
