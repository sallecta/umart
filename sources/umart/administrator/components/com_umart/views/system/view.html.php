<?php
/**
 
 
 
 
 
 */

defined('_JEXEC') or die;

use Umart\Classes\User;
use Umart\View\BaseView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class UmartViewSystem extends BaseView
{
	protected function beforeDisplay()
	{
		ToolbarHelper::title(JText::_('COM_UMART_SYSTEM'));
		$user = plg_sytem_umart_main(User::class);

		if ($user->core('admin'))
		{
			ToolbarHelper::preferences('com_umart');
		}
	}
}
