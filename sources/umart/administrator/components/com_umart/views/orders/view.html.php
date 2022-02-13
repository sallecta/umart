<?php

/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\User;
use Umart\View\ListView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class UmartViewOrders extends ListView
{
	protected function addToolbar()
	{
		$user = plg_sytem_umart_main(User::class);

		ToolbarHelper::title(Text::_('COM_UMART_ORDERS_MANAGE'));

		if ($user->core('create'))
		{
			ToolbarHelper::addNew('order.add');
		}

		if ($user->core('edit') || $user->core('edit.state'))
		{
			if ($this->state->get('filter.published') == -2 && $user->core('delete'))
			{
				ToolbarHelper::deleteList('', 'orders.delete');
			}
			else
			{
				ToolbarHelper::trash('orders.trash');
			}

			if ($user->core('edit.state'))
			{
				ToolbarHelper::checkin('orders.checkin');
			}
		}

		if ($user->core('admin'))
		{
			ToolbarHelper::custom('orders.export', 'download', '', 'COM_UMART_EXPORT');
			ToolbarHelper::preferences('com_umart');
		}
	}
}
