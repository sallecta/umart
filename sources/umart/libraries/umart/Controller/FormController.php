<?php
/**
 
 
 
 
 
 */

namespace Umart\Controller;
defined('_JEXEC') or die;

use Umart\Classes\StringHelper;
use Umart\Classes\User;
use Exception;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController as CMSFormController;

class FormController extends CMSFormController
{
	public function __construct(array $config)
	{
		if (empty($this->view_list) || empty($this->view_item))
		{
			$name = $this->getReflectorName();

			if (empty($this->view_list))
			{
				$stringHelper    = plg_sytem_umart_main(StringHelper::class);
				$this->view_list = $stringHelper->toPlural($name);
			}

			if (empty($this->view_item))
			{
				$this->view_item = $name;
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

	protected function allowAdd($data = [])
	{
		return plg_sytem_umart_main(User::class)->core('create');
	}

	protected function allowEdit($data = [], $key = 'id')
	{
		return plg_sytem_umart_main(User::class)->core('edit');
	}
}
