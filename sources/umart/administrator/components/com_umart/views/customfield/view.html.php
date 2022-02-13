<?php
/**
 
 
 
 
 
 */

defined('_JEXEC') or die;

use Umart\Classes\CustomField;
use Umart\Classes\User;
use Umart\View\ItemView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class UmartViewCustomfield extends ItemView
{
	public function display($tpl = null)
	{
		$reflector = plg_sytem_umart_main('app')->input->getCmd('reflector');
		plg_sytem_umart_main(CustomField::class)->check(false, $reflector);

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		plg_sytem_umart_main('app')->input->set('hidemainmenu', true);
		$user       = plg_sytem_umart_main(User::class);
		$name       = $this->getName();
		$userId     = $user->get()->id;
		$isNew      = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
		$title      = strtoupper($name);

		ToolbarHelper::title(
			Text::_('COM_UMART_' . ($checkedOut ? 'VIEW_' . $title : ($isNew ? 'ADD_' . $title : 'EDIT_' . $title))),
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
