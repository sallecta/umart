<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use ES\Classes\CustomField;
use ES\Classes\User;
use ES\View\ItemView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class EasyshopViewCustomfield extends ItemView
{
	public function display($tpl = null)
	{
		$reflector = easyshop('app')->input->getCmd('reflector');
		easyshop(CustomField::class)->check(false, $reflector);

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		easyshop('app')->input->set('hidemainmenu', true);
		$user       = easyshop(User::class);
		$name       = $this->getName();
		$userId     = $user->get()->id;
		$isNew      = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
		$title      = strtoupper($name);

		ToolbarHelper::title(
			Text::_('COM_EASYSHOP_' . ($checkedOut ? 'VIEW_' . $title : ($isNew ? 'ADD_' . $title : 'EDIT_' . $title))),
			'pencil-2'
		);

		if ($isNew && $user->core('create'))
		{
			ToolbarHelper::apply($name . '.apply');
			ToolbarHelper::save($name . '.save');
			ToolbarHelper::save2new($name . '.save2new');
			ToolbarHelper::cancel($name . '.cancel');
		}
		else
		{
			// Can't save the record if it's checked out.
			if (!$checkedOut)
			{
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($user->core('edit') || ($user->core('edit.own') && $this->item->created_by == $userId))
				{
					ToolbarHelper::apply($name . '.apply');
					ToolbarHelper::save($name . '.save');

					// We can save this record, but check the create permission to see if we can return to make a new one.
					if ($user->core('create'))
					{
						ToolbarHelper::save2new($name . '.save2new');
					}
				}
			}

			ToolbarHelper::cancel($name . '.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
