<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ES\Controller;
defined('_JEXEC') or die;

use ES\Classes\StringHelper;
use ES\Classes\User;
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
				$stringHelper    = easyshop(StringHelper::class);
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
		return easyshop(User::class)->core('create');
	}

	protected function allowEdit($data = [], $key = 'id')
	{
		return easyshop(User::class)->core('edit');
	}
}
