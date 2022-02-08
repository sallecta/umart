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

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController as CMSBaseController;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

class BaseController extends CMSBaseController
{
	/**
	 * @var CMSApplication $app
	 * @since 1.0.0
	 */
	protected $app;

	public function __construct(array $config = [])
	{
		parent::__construct($config);
		$this->app = easyshop('app');
	}

	public function checkToken($method = 'post', $redirect = true)
	{
		$valid = Session::checkToken($method);

		if (!$valid && $redirect)
		{
			$this->redirectBackPage(Text::_('JINVALID_TOKEN_NOTICE'), 'warning');
		}

		return $valid;
	}

	public function redirectBackPage($message = null, $type = 'message')
	{
		if ($return = $this->app->input->getBase64('return'))
		{
			$redirect = base64_decode($return);
		}
		else
		{
			$redirect = $this->app->input->server->getString('HTTP_REFERER');

			if (!Uri::isInternal($redirect))
			{
				$redirect = Uri::root();
			}
		}

		if ($message)
		{
			$this->app->enqueueMessage($message, $type);
		}

		$this->app->redirect($redirect);
	}
}
