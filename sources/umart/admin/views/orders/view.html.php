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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class EasyshopViewOrders extends ListView
{
	protected function addToolbar()
	{
		$user = easyshop(User::class);

		ToolbarHelper::title(Text::_('COM_EASYSHOP_ORDERS_MANAGE'));

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
			ToolbarHelper::custom('orders.export', 'download', '', 'COM_EASYSHOP_EXPORT');
			ToolbarHelper::preferences('com_easyshop');
		}
	}
}
