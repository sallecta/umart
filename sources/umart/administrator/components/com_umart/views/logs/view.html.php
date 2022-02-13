<?php
/**
 
 
 
 * @copyright   Copyright (C) 2015 - 2019 github.com/sallecta/umart All Rights Reserved.
 
 */

defined('_JEXEC') or die;

use Umart\Classes\User;
use Umart\View\ListView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class UmartViewLogs extends ListView
{
	protected function addToolbar()
	{
		ToolbarHelper::title(JText::_('COM_UMART_LOGS_MANAGE'));
		$user = plg_sytem_umart_main(User::class);

		if ($user->core('delete'))
		{
			ToolbarHelper::deleteList('COM_UMART_REMOVE_CONFIRM', 'logs.delete');
		}

		if ($user->core('admin'))
		{
			ToolbarHelper::preferences('com_umart');
		}
	}
}
