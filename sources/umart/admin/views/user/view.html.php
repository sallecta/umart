<?php
/**
 * @package     com_easyshop
 * @version     1.0.5
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use ES\View\ItemView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class EasyshopViewUser extends ItemView
{
	protected $orderModelList = [];

	protected function addToolbar()
	{
		parent::addToolbar();
		$title = !empty($this->item->user->username)
			? Text::sprintf('COM_EASYSHOP_EDIT_USER_NAME', $this->item->user->name, $this->item->user->username)
			: Text::_('COM_EASYSHOP_ADD_USER');

		ToolbarHelper::title($title);

	}

	protected function beforeDisplay()
	{
		if ($this->item->id)
		{
			$this->orderModelList = $this->getModel()->getOrderModelList($this->item->id);
		}

	}
}
