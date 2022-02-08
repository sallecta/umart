<?php
/**
 * @package     com_easyshop
 * @version     1.0.5
 * @Author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2019 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\User;
use ES\View\ListView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class EasyshopViewMethods extends ListView
{
	public function display($tpl = null)
	{
		$type = easyshop('app')->input->getCmd('filter_type');

		if (!in_array($type, ['shipping', 'payment'], true))
		{
			throw new Exception(Text::_('COM_EASYSHOP_ERROR_INVALID_METHOD'));
		}

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		if ($this->getLayout() == 'select')
		{
			ToolbarHelper::title(Text::_('COM_EASYSHOP_METHOD_SELECT'));
			ToolbarHelper::back();

			if (easyshop(User::class)->core('admin'))
			{
				ToolbarHelper::preferences('com_easyshop');
			}
		}
		else
		{
			parent::addToolbar();
		}
	}
}
