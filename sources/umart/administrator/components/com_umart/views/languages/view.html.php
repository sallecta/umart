<?php
/**
 
 * @version     1.0.5
 
 
 
 */

defined('_JEXEC') or die;

use Umart\Classes\User;
use Umart\View\BaseView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class UmartViewLanguages extends BaseView
{
	protected $languages;

	public function display($tpl = null)
	{
		$this->languages = UmartHelper::getLanguageList();
		sort($this->languages);

		parent::display($tpl);

		$this->addToolbar();
	}

	protected function addToolbar()
	{
		ToolbarHelper::title(JText::_('COM_UMART_LANGUAGES'));

		if (plg_sytem_umart_main(User::class)->core('admin'))
		{
			ToolbarHelper::preferences('com_umart');
		}
	}
}
