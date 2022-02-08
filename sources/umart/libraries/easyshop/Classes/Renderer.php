<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ES\Classes;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Layout\FileLayout;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

class Renderer
{
	protected $basePath = ES_COMPONENT . '/layouts';
	protected $templates = ['default'];
	protected $layoutId = null;
	protected $displayData = [];
	protected $includePaths = [];

	public function __construct($config = [])
	{
		if (!empty($config['basePath']))
		{
			$this->setBasePath($config['basePath']);
		}

		if (!empty($config['templates']))
		{
			$this->templates = $config['templates'];
		}
	}

	public function setBasePath($basePath)
	{
		if (is_dir($basePath))
		{
			$this->basePath = $basePath;
		}

		return $this;
	}

	public function setTemplates(array $templates = ['default'])
	{
		$this->templates = $templates;

		return $this;
	}

	public function setLayoutId($layoutId)
	{
		$this->layoutId = $layoutId;

		return $this;
	}

	public function setDisplayData($displayData)
	{
		$this->displayData = $displayData;

		return $this;
	}

	public function getSiteTemplate()
	{
		static $siteTemplate = null;

		if (null === $siteTemplate)
		{
			$db    = CMSFactory::getDbo();
			$query = $db->getQuery(true)
				->select('a.template')
				->from($db->quoteName('#__template_styles', 'a'))
				->where('a.home = 1 AND a.client_id = 0');
			$db->setQuery($query);
			$siteTemplate = $db->loadResult();
		}

		return $siteTemplate;
	}

	public function refreshDefaultPaths()
	{
		$styleName    = easyshop('app')->getTemplate();
		$templatePath = JPATH_THEMES . '/' . $styleName . '/html/com_easyshop/layouts';
		$paths        = [
			JPATH_THEMES . '/' . $styleName . '/html/com_easyshop/layouts/global',
		];

		foreach ($this->templates as $template)
		{
			$paths[] = $templatePath . '/' . $template;
		}

		$paths[] = $templatePath;
		$paths[] = JPATH_THEMES . '/' . $styleName . '/html/layouts/com_easyshop';
		$paths[] = $this->basePath;
		$paths[] = ES_COMPONENT_SITE . '/layouts';
		$paths[] = ES_COMPONENT_ADMINISTRATOR . '/layouts';
		$this->setPaths($paths);

		return $this;
	}

	public function setPaths($paths, $resetPath = true)
	{
		settype($paths, 'array');

		if ($resetPath)
		{
			$this->includePaths = [];
		}

		return $this->addIncludePath($paths);
	}

	public function addIncludePath($path)
	{
		if (is_array($path))
		{
			$realPaths = [];

			foreach ($path as $dir)
			{
				if (is_dir($dir))
				{
					$realPaths[] = Path::clean($dir);
				}
			}

			$this->includePaths = array_merge($realPaths, $this->includePaths);
		}
		elseif (is_dir($path))
		{
			array_unshift($this->includePaths, Path::clean($path));
		}

		$this->includePaths = ArrayHelper::arrayUnique($this->includePaths);

		return $this->includePaths;
	}

	public function __toString()
	{
		if ($this->layoutId)
		{
			return $this->render($this->layoutId, $this->displayData);
		}

		return json_encode($this);
	}

	public function render($layoutId, $displayData = [])
	{
		if (is_array($displayData)
			&& !array_key_exists('renderer', $displayData)
		)
		{
			$displayData['renderer'] = $this;
		}
		elseif (is_object($displayData)
			&& !property_exists($displayData, 'renderer')
		)
		{
			$displayData->renderer = $this;
		}

		if (is_array($displayData)
			&& !array_key_exists('currency', $displayData)
		)
		{
			$displayData['currency'] = easyshop(Currency::class)->getActive();
		}
		elseif (is_object($displayData)
			&& !property_exists($displayData, 'currency')
		)
		{
			$displayData->currency = easyshop(Currency::class)->getActive();
		}

		return $this->getFileLayout($layoutId)->render($displayData);
	}

	public function getFileLayout($layoutId)
	{
		$options = new Registry;
		$options->set('component', 'com_easyshop');
		$fileLayout = new FileLayout($layoutId, $this->basePath, $options);

		if (count($this->includePaths))
		{
			$fileLayout->setIncludePaths($this->includePaths);
		}

		return $fileLayout;
	}
}
