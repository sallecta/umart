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
use ES\View\BaseView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class EasyshopViewSystem extends BaseView
{
	protected function beforeDisplay()
	{
		ToolbarHelper::title(JText::_('COM_EASYSHOP_SYSTEM'));
		$user = easyshop(User::class);

		if ($user->core('admin'))
		{
			ToolbarHelper::preferences('com_easyshop');
		}
	}
}
