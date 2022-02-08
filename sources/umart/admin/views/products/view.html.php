<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\User;
use ES\View\ListView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class EasyshopViewProducts extends ListView
{
	protected function addToolbar()
	{
		parent::addToolbar();

		if (easyshop(User::class)->core('admin'))
		{
			ToolbarHelper::custom('products.export', 'download', 'download', 'COM_EASYSHOP_EXPORT');
		}
	}
}
