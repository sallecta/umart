<?php
/**
 
 * @version     1.0.5
 
 
 
 */

defined('_JEXEC') or die;

use Umart\View\ItemView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class UmartViewUser extends ItemView
{
	protected $orderModelList = [];

	protected function addToolbar()
	{
		parent::addToolbar();
		$title = !empty($this->item->user->username)
			? Text::sprintf('COM_UMART_EDIT_USER_NAME', $this->item->user->name, $this->item->user->username)
			: Text::_('COM_UMART_ADD_USER');

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
