<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ES\Classes;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

class Router
{
	protected static $routes = [];

	public function __construct()
	{
		static $handled = false;

		if (!$handled)
		{
			$handled = true;
			$db      = easyshop('db');
			$query   = $db->getQuery(true)
				->select('a.folder, a.element, a.params')
				->from($db->quoteName('#__extensions', 'a'))
				->where('a.type = ' . $db->quote('plugin'))
				->where('a.folder LIKE ' . $db->quote('easyshop%'))
				->where('a.enabled = 1');

			if ($plugins = $db->setQuery($query)->loadObjectList())
			{
				foreach ($plugins as $plugin)
				{
					$class = ucfirst($plugin->folder) . ucfirst($plugin->element) . 'Router';

					if (!class_exists($class))
					{
						$routePath = JPATH_PLUGINS . '/' . $plugin->folder . '/' . $plugin->element . '/router.php';

						if (is_file($routePath))
						{
							require_once $routePath;
						}
					}

					if (class_exists($class))
					{
						$router = new $class;

						if (is_callable([$router, 'routes']))
						{
							$params = new Registry($plugin->params);
							call_user_func_array([$router, 'routes'], [$this, $params]);
						}
					}
				}
			}
		}
	}

	public function addGet($pattern, $callback)
	{
		return $this->add($pattern, $callback, 'GET');
	}

	public function add($pattern, $callback, $method = 'REQUEST')
	{
		$pattern = preg_replace('/\?.*$/', '', $pattern);
		$pattern = preg_replace('/\/+/', '/', $pattern);
		$pattern = trim($pattern, '/');

		if (!empty($pattern))
		{
			static::$routes[$method][$pattern] = $callback;
		}

		return $this;
	}

	public function getPattern($pattern, $checkLanguage = false)
	{
		static $langSef = null;

		if (null === $langSef)
		{
			$langSef = '';

			if (Multilanguage::isEnabled() && easyshop('site'))
			{
				$langCode  = easyshop('app')->getLanguage()->getTag();
				$default   = ComponentHelper::getParams('com_language')->get('site', 'en-GB');
				$languages = LanguageHelper::getLanguages('lang_code');

				if ($langCode !== $default && isset($languages[$langCode]))
				{
					$langSef = $languages[$langCode]->sef . '/';
				}
			}
		}

		if ($checkLanguage)
		{
			$pattern = $langSef . $pattern;
		}

		return $pattern;
	}

	public function addPost($pattern, $callback)
	{
		return $this->add($pattern, $callback, 'POST');
	}

	public function addDelete($pattern, $callback)
	{
		return $this->add($pattern, $callback, 'DELETE');
	}

	public function execute($uri = null)
	{
		$app  = easyshop('app');
		$uri  = trim($uri ?: $app->input->server->get('REQUEST_URI', '', 'raw'), '/');
		$uri  = preg_replace('/\?.*$/', '', $uri);
		$root = ltrim(Uri::root(true), '/') . '/';

		if (strpos($uri, $root) === 0)
		{
			$uri = substr($uri, strlen($root));
		}

		if (strpos($uri, 'index.php/') === 0)
		{
			$uri = substr($uri, 10);
		}

		if (!$uri || !static::$routes)
		{
			return;
		}

		$method   = $app->input->getMethod();
		$callable = null;

		if (isset(static::$routes['REQUEST']))
		{
			$callable = $this->findMatch($uri, static::$routes['REQUEST']);
		}

		if (!$callable && isset(static::$routes[$method]))
		{
			$callable = $this->findMatch($uri, static::$routes[$method]);
		}

		if (is_array($callable))
		{
			list($callback, $params) = $callable;
			call_user_func_array($callback, $params);
		}
	}

	protected function findMatch($uri, array $routes)
	{
		$paths = explode('/', $uri);
		$count = count($paths);

		foreach ($routes as $pattern => $callback)
		{
			$parts = explode('/', $pattern);

			if (count($parts) === $count)
			{
				$match  = true;
				$params = [];

				foreach (explode('/', $pattern) as $i => $path)
				{
					if (!isset($paths[$i]))
					{
						$match = false;
						break;
					}

					$param = $paths[$i];

					if (strpos($path, ':') === 0)
					{
						$filter  = strtolower(substr($path, 1));
						$isValid = false;

						switch ($filter)
						{
							case 'int':
							case 'uint':
								$value = filter_var($param, FILTER_VALIDATE_INT);

								if ($value !== false && ($filter !== 'uint' || $value >= 0))
								{
									$isValid = true;
									$param   = (int) $param;
								}

								break;

							case 'float':
							case 'double':
							case 'ufloat':
							case 'udouble':
								$value = filter_var($param, FILTER_VALIDATE_FLOAT);

								if ($value !== false && (!in_array($filter, ['ufloat', 'udouble']) || $value >= 0))
								{
									$isValid = true;
									$param   = (float) $param;
								}

								break;

							default:

								if (0 === strpos($filter, 'regex='))
								{
									$regex   = explode('=', $filter, 2)[1];
									$isValid = !!@preg_match('/' . $regex . '/', $param, $matches);
								}
								else
								{
									$isValid = true;
								}

								break;
						}

						if ($isValid)
						{
							$params[] = $param;
						}
						else
						{
							$match = false;
							break;
						}
					}
					elseif ($paths[$i] !== $path)
					{
						$match = false;
						break;
					}
				}

				if ($match && is_callable($callback))
				{
					return [$callback, $params];
				}
			}
		}

		return null;
	}
}
