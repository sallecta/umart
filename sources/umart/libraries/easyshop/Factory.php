<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ES;
defined('_JEXEC') or die;

use ES\Controller\BaseController;
use JDatabaseDriver;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use RuntimeException;

class Factory
{
	protected static $classPaths = [];
	protected static $classes = [];
	protected static $instance = null;
	/**
	 * @var CMSApplication
	 * @since 1.0.0
	 */
	protected $app;
	/**
	 * @var Document
	 * @since 1.0.0
	 */
	protected $doc;
	/**
	 * @var JDatabaseDriver
	 * @since 1.0.0
	 */
	protected $db;
	/**
	 * @var  Registry
	 * @since 1.0.0
	 */
	protected $state;

	public static function getInstance()
	{
		if (null === self::$instance)
		{
			self::$instance = new Factory;
		}

		return self::$instance;
	}

	public function dispatch()
	{
		/** @var User $user */
		$user    = $this->getClass('User');
		$isAdmin = easyshop('administrator');

		if ($isAdmin && !$user->core('manage'))
		{
			throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		PluginHelper::importPlugin('easyshop');
		HTMLHelper::_('ukui.tabState');
		easyshop(Classes\Html::class)->initChosen();

		$config  = [];
		$app     = $this->__get('app');
		$view    = $app->input->get('view');
		$task    = $app->input->get('task');
		$isMedia = ($view == 'media' || strpos($task, 'media.') === 0);

		if ($isMedia
			&& !$isAdmin
			&& ($user->core('admin') || $user->isCustomer())
		)
		{
			$config['base_path'] = ES_COMPONENT_ADMINISTRATOR;
		}

		$app->triggerEvent('onEasyshopBeforeDispatch', [&$config]);
		ob_start();
		$controller = BaseController::getInstance('Easyshop', $config);
		$controller->execute($app->input->get('task', null, 'CMD'));
		$buffer = ob_get_clean();
		$app->triggerEvent('onEasyshopAfterDispatch', [&$buffer]);
		echo '<div id="es-component" class="uk-scope es-scope">' . $buffer . '</div>';

		$controller->redirect();

		return true;
	}

	public function getClass($className, $config = [], $namespace = 'ES', $constructor = null)
	{
		if (false === strpos($className, '\\'))
		{
			$class = $namespace . '\\' . ucfirst($className);
		}
		else
		{
			$class = $className;
		}

		$className = strtolower($className);
		$classPath = null;

		if (isset(self::$classes[$className]))
		{
			list($classPath, $classAlias) = self::$classes[$className];

			if (null !== $classAlias)
			{
				$class = $classAlias;
			}
		}

		if (!class_exists($class))
		{
			if (null === $classPath)
			{
				$classPath = Path::find($this->addIncludeClassPath(), $className . '.php');
			}

			if (is_file($classPath))
			{
				require_once $classPath;
			}

			if (!class_exists($class))
			{
				throw new RuntimeException('Class ' . $class . ' not found');
			}
		}

		if (null !== $constructor && is_callable([$class, $constructor]))
		{
			return call_user_func_array([$class, $constructor], [$config]);
		}

		return new $class($config);
	}

	public function addIncludeClassPath($paths = [])
	{
		settype($paths, 'array');

		foreach ($paths as $path)
		{
			$path = Path::clean($path);

			if (!in_array($path, self::$classPaths))
			{
				array_push(self::$classPaths, $path);
			}
		}

		return self::$classPaths;

	}

	public function __get($name)
	{
		switch ($name)
		{
			case 'app':

				if (!isset($this->app))
				{
					$this->app = CMSFactory::getApplication();
				}

				return $this->app;

			case 'db':

				if (!isset($this->db))
				{
					$this->db = CMSFactory::getDbo();
				}

				return $this->db;

			case 'doc':

				if (!isset($this->doc))
				{
					$this->doc = CMSFactory::getDocument();
				}

				return $this->doc;

			case 'state':

				if (!($this->state instanceof Registry))
				{
					$this->state = new Registry;
				}

				return $this->state;
		}

		throw new RuntimeException('Cannot access protected property class ES\\Classes\\Factory::' . $name);
	}

	public function addLangText($keys = [])
	{
		settype($keys, 'array');
		$data = [];

		foreach ($keys as $key)
		{
			$key        = trim(strtoupper($key));
			$data[$key] = Text::_($key);
		}

		$this->doc->addScriptDeclaration('_es.lang.load(' . json_encode($data) . ');');
	}

	public function getModel($name, $basePath = ES_COMPONENT, $config = ['ignore_request' => true])
	{
		static $includePaths = [];

		if (!in_array($basePath, $includePaths))
		{
			BaseDatabaseModel::addIncludePath($basePath . '/models', 'EasyshopModel');
			$includePaths[] = $basePath;
		}

		return BaseDatabaseModel::getInstance(ucfirst($name), 'EasyshopModel', $config);
	}

	public function getConfig($name = null, $default = null)
	{
		static $config;

		if (!($config instanceof Registry))
		{
			$config = clone ComponentHelper::getParams('com_easyshop');

			if ($this->__get('app')->isClient('site'))
			{
				$listLimit = $config->get('list_limit', '15,25,50,75,100');

				if (strpos($listLimit, ',') === false)
				{
					$listLimit = '15,25,50,75,100';
				}

				$listLimit = preg_replace('/\,+/', ',', trim($listLimit));
				$listLimit = ArrayHelper::toInteger(explode(',', $listLimit));
				$config->set('list_limit', $listLimit);
			}
		}

		return $name ? $config->get($name, $default) : $config;
	}

	public function getPlugin($name, $group = 'easyshop')
	{
		static $plugins = [];

		if (!isset($plugins[$name]))
		{
			$pluginTable = Table::getInstance('Extension', 'JTable');

			if (!$pluginTable->load(['folder' => strtolower($group), 'element' => strtolower($name), 'type' => 'plugin', 'enabled' => '1']))
			{
				return false;
			}

			$params         = new Registry($pluginTable->params);
			$plugin         = new Registry($pluginTable->getProperties());
			$plugin         = $plugin->toObject();
			$plugin->params = $params;
			$plugins[$name] = $plugin;
		}

		return $plugins[$name];
	}

	public function registerClass($className, $classPath, $classAlias = null)
	{
		$className = strtolower($className);
		$classPath = Path::clean($classPath, '/');

		if (is_file($classPath))
		{
			// Make it's overridable
			self::$classes[$className] = [$classPath, $classAlias];
		}

		return self::$classes;
	}
}
