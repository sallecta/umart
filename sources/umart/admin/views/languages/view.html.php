<?php
/**
 * @package     com_easyshop
 * @version     1.0.5
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use ES\Classes\User;
use ES\View\BaseView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class EasyshopViewLanguages extends BaseView
{
	protected $languages;

	public function display($tpl = null)
	{
		$this->languages = EasyshopHelper::getLanguageList();
		sort($this->languages);

		parent::display($tpl);

		$this->addToolbar();
	}

	protected function addToolbar()
	{
		ToolbarHelper::title(JText::_('COM_EASYSHOP_LANGUAGES'));

		if (easyshop(User::class)->core('admin'))
		{
			ToolbarHelper::preferences('com_easyshop');
		}
	}
}
