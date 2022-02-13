<?php
/**
 
 
 
 
 
 */

namespace Umart\Controller;

defined('_JEXEC') or die;

use Umart\Classes\StringHelper;
use Umart\Factory;
use Exception;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController as CMSAdminController;
use ReflectionClass;

class AdminController extends CMSAdminController
{
	public function __construct(array $config)
	{
		if (empty($this->view_list) || empty($this->view_item))
		{
			$name = $this->getReflectorName();

			if (empty($this->view_list))
			{
				$this->view_list = $name;
			}

			if (empty($this->view_item))
			{
				$stringHelper    = plg_sytem_umart_main(StringHelper::class);
				$this->view_item = $stringHelper->toSingular($name);
			}
		}

		parent::__construct($config);
	}

	protected function getReflectorName()
	{
		$r = null;

		if (!preg_match('/Controller(.*)/i', get_class($this), $r))
		{
			throw new Exception(Text::_('JLIB_APPLICATION_ERROR_CONTROLLER_GET_NAME'), 500);
		}

		return strtolower($r[1]);
	}

	public function getModel($name = '', $prefix = 'UmartModel', $config = ['ignore_request' => true])
	{
		if (empty($name))
		{
			$stringHelper = plg_sytem_umart_main(StringHelper::class);
			$name         = $stringHelper->toSingular($this->getReflectorName());

			if ($name == 'customfields')
			{
				$name = 'customfield';
			}
		}

		$reflection = new ReflectionClass($this);
		$path       = dirname(dirname($reflection->getFileName()));

		return Factory::getInstance()->getModel($name, $path, $config);
	}
}
