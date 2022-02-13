<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
use Umart\Controller\AdminController;

class UmartControllerCurrencies extends AdminController
{
	public function setDefault()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		JTable::addIncludePath(UMART_COMPONENT_ADMINISTRATOR . '/tables');

		$pks     = $this->input->get('cid', [], 'array');
		$table   = JTable::getInstance('Currency', 'UmartTable');
		$pk      = (int) @$pks[0];
		$message = JText::_('COM_UMART_SET_DEFAULT_FAILURE');
		$type    = 'error';

		if ($table->load($pk) && $table->get('state'))
		{
			$table->set('is_default', 1);
			$table->set('rate', 1);

			if ($table->store())
			{
				$message = JText::sprintf('COM_UMART_SET_DEFAULT_SUCCESS', $table->get('name'));
				$type    = 'message';
			}
		}
		elseif (!$table->get('state'))
		{
			$message = JText::sprintf('COM_UMART_SET_DEFAULT_STATE_FAILURE', $table->get('name'));
		}

		$this->setRedirect(JRoute::_('index.php?option=com_umart&view=' . $this->view_list, false), $message, $type);
	}
}
