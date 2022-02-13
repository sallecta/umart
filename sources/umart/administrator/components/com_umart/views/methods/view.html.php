<?php
/**
 
 * @version     1.0.5
 * @Author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2019 github.com/sallecta/umart All Rights Reserved.
 
 */
defined('_JEXEC') or die;

use Umart\Classes\User;
use Umart\View\ListView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class UmartViewMethods extends ListView
{
	public function display($tpl = null)
	{
		$type = plg_sytem_umart_main('app')->input->getCmd('filter_type');

		if (!in_array($type, ['shipping', 'payment'], true))
		{
			throw new Exception(Text::_('COM_UMART_ERROR_INVALID_METHOD'));
		}

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		if ($this->getLayout() == 'select')
		{
			ToolbarHelper::title(Text::_('COM_UMART_METHOD_SELECT'));
			ToolbarHelper::back();

			if (plg_sytem_umart_main(User::class)->core('admin'))
			{
				ToolbarHelper::preferences('com_umart');
			}
		}
		else
		{
			parent::addToolbar();
		}
	}
}
