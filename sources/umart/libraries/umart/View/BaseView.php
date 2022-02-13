<?php
/**
 
 
 
 
 
 */

namespace Umart\View;

defined('_JEXEC') or die;

use Umart\Classes\Currency;
use Umart\Classes\Renderer;
use Umart\Classes\Utility;
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
		$styleName     = plg_sytem_umart_main('app')->getTemplate();
		$reflection    = new ReflectionClass($this);
		$basePath      = dirname($reflection->getFileName());
		$tplPath       = dirname(dirname($basePath)) . '/templates';
		$extraName     = null;
		$templatePaths = [
			UMART_COMPONENT_ADMINISTRATOR . '/templates/global',
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
			$templatePaths[] = JPATH_THEMES . '/' . $styleName . '/html/com_umart/templates/' . $extraName . '/' . $viewName;
		}

		/** @var Renderer $renderer */
		$renderer        = plg_sytem_umart_main('renderer');
		$templatePaths[] = JPATH_SITE . '/templates/' . $renderer->getSiteTemplate() . '/html/com_umart/templates/global/' . $viewName;
		$templatePaths[] = JPATH_THEMES . '/' . $styleName . '/html/com_umart/' . $viewName;
		$templatePaths[] = JPATH_THEMES . '/' . $styleName . '/html/com_umart/templates/default/' . $viewName;
		$config['template_path'] = ArrayHelper::arrayUnique($templatePaths);

		if (plg_sytem_umart_main('site'))
		{
			$this->currency = plg_sytem_umart_main(Currency::class)->getActive();
		}
		else
		{
			$this->currency = plg_sytem_umart_main(Currency::class)->getDefault();
		}

		$this->renderer = $this->getRenderer();
		$this->utility  = plg_sytem_umart_main(Utility::class);
		$this->config   = clone plg_sytem_umart_main('config');

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
			$renderer = plg_sytem_umart_main('state')->get('view.' . $this->getName() . '.renderer');

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

				$this->renderer = plg_sytem_umart_main('renderer', $config);
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
