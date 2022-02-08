<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2019 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use ES\Classes\User;
use ES\View\ListView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class EasyshopViewLogs extends ListView
{
	protected function addToolbar()
	{
		ToolbarHelper::title(JText::_('COM_EASYSHOP_LOGS_MANAGE'));
		$user = easyshop(User::class);

		if ($user->core('delete'))
		{
			ToolbarHelper::deleteList('COM_EASYSHOP_REMOVE_CONFIRM', 'logs.delete');
		}

		if ($user->core('admin'))
		{
			ToolbarHelper::preferences('com_easyshop');
		}
	}
}
