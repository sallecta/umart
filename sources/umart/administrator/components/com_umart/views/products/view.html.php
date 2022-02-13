<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\User;
use Umart\View\ListView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class UmartViewProducts extends ListView
{
	protected function addToolbar()
	{
		parent::addToolbar();

		if (plg_sytem_umart_main(User::class)->core('admin'))
		{
			ToolbarHelper::custom('products.export', 'download', 'download', 'COM_UMART_EXPORT');
		}
	}
}
