<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ES\View;

defined('_JEXEC') or die;

use ES\Classes\Currency;
use ES\Classes\Renderer;
use ES\Classes\Utility;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use ReflectionClass;

class BaseView extends HtmlView
{
	/**
	 * @var Currency $currency
	 * @var Registry $config
	 * @var Utility  $utility
	 * @since 1.0.0
	 */
	protected $currency;
	protected $config = null;
	protected $utility = null;
	protected $templatePath = null;
	protected $renderer = null;

	public function __construct(array $config)
	{
		$viewName      = $this->getName();
		$styleName     = easyshop('app')->getTemplate();
		$reflection    = new ReflectionClass($this);
		$basePath      = dirname($reflection->getFileName());
		$tplPath       = dirname(dirname($basePath)) . '/templates';
		$extraName     = null;
		$templatePaths = [
			ES_COMPONENT_ADMINISTRATOR . '/templates/global',
			$basePath . '/tmpl',
			$tplPath . '/default/' . $viewName,
		];

		if ($this->templatePath)
		{
			$templatePaths[] = $this->templatePath . '/' . $viewName;
			$extraName       = basename($this->templatePath);
		}

		if ($extraName)
		{
			$templatePaths[] = JPATH_THEMES . '/' . $styleName . '/html/com_easyshop/templates/' . $extraName . '/' . $viewName;
		}

		/** @var Renderer $renderer */
		$renderer        = easyshop('renderer');
		$templatePaths[] = JPATH_SITE . '/templates/' . $renderer->getSiteTemplate() . '/html/com_easyshop/templates/global/' . $viewName;
		$templatePaths[] = JPATH_THEMES . '/' . $styleName . '/html/com_easyshop/' . $viewName;
		$templatePaths[] = JPATH_THEMES . '/' . $styleName . '/html/com_easyshop/templates/default/' . $viewName;
		$config['template_path'] = ArrayHelper::arrayUnique($templatePaths);

		if (easyshop('site'))
		{
			$this->currency = easyshop(Currency::class)->getActive();
		}
		else
		{
			$this->currency = easyshop(Currency::class)->getDefault();
		}

		$this->renderer = $this->getRenderer();
		$this->utility  = easyshop(Utility::class);
		$this->config   = clone easyshop('config');

		parent::__construct($config);
	}

	/**
	 * @return Renderer
	 * @since 1.0.0
	 */

	public function getRenderer()
	{
		if (!isset($this->renderer))
		{
			$renderer = easyshop('state')->get('view.' . $this->getName() . '.renderer');

			if ($renderer instanceof Renderer)
			{
				$this->renderer = $renderer;
			}
			else
			{
				$templates = [];

				if ($this->templatePath)
				{
					$templates[] = basename($this->templatePath);
				}

				$templates[] = 'default';
				$config      = [
					'templates' => ArrayHelper::arrayUnique($templates),
				];

				$this->renderer = easyshop('renderer', $config);
			}
		}

		return $this->renderer;
	}

	public function setRenderer(Renderer $renderer)
	{
		$this->renderer = $renderer;
	}

	public function getTemplatePath()
	{
		return $this->templatePath;
	}

	public function setTemplatePath($path)
	{
		$this->templatePath = $path;

		return $this;
	}

	public function display($tpl = null)
	{
		$this->beforeDisplay();

		parent::display($tpl);
	}

	protected function beforeDisplay()
	{
		return;
	}

	public function getProperty($property, $default = null)
	{
		return property_exists($this, $property) ? $this->{$property} : $default;
	}

	protected function _setPath($type, $path)
	{
		$this->_path[$type] = [];
		$this->_addPath($type, $path);
	}
}
