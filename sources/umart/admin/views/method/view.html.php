<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use ES\View\ItemView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class EasyshopViewMethod extends ItemView
{
	protected $methodType;

	public function display($tpl = null)
	{
		$type = easyshop('app')->input->getCmd('filter_type');

		if (!in_array($type, ['shipping', 'payment'], true))
		{
			throw new Exception(Text::_('COM_EASYSHOP_ERROR_INVALID_METHOD'));
		}

		$this->methodType = $type;
		parent::display($tpl);
	}

	protected function addToolbar()
	{
		parent::addToolbar();
		ToolbarHelper::title(Text::_('COM_EASYSHOP_ADD_METHOD_' . strtoupper($this->methodType)));
	}

}
