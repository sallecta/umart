<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Controller\BaseController;

class EasyshopController extends BaseController
{
	protected $default_view = 'dashboard';

	public function display($cachable = false, $urlparams = array())
	{
		parent::display($cachable, $urlparams = array());

		return $this;
	}
}
