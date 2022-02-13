<?php
/**
 
 
 
 
 
 */

defined('_JEXEC') or die;

use Umart\View\ItemView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class UmartViewMethod extends ItemView
{
	protected $methodType;

	public function display($tpl = null)
	{
		$type = plg_sytem_umart_main('app')->input->getCmd('filter_type');

		if (!in_array($type, ['shipping', 'payment'], true))
		{
			throw new Exception(Text::_('COM_UMART_ERROR_INVALID_METHOD'));
		}

		$this->methodType = $type;
		parent::display($tpl);
	}

	protected function addToolbar()
	{
		parent::addToolbar();
		ToolbarHelper::title(Text::_('COM_UMART_ADD_METHOD_' . strtoupper($this->methodType)));
	}

}
