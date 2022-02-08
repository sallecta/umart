<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;
use ES\Controller\AdminController;

class EasyshopControllerCurrencies extends AdminController
{
	public function setDefault()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		JTable::addIncludePath(ES_COMPONENT_ADMINISTRATOR . '/tables');

		$pks     = $this->input->get('cid', [], 'array');
		$table   = JTable::getInstance('Currency', 'EasyshopTable');
		$pk      = (int) @$pks[0];
		$message = JText::_('COM_EASYSHOP_SET_DEFAULT_FAILURE');
		$type    = 'error';

		if ($table->load($pk) && $table->get('state'))
		{
			$table->set('is_default', 1);
			$table->set('rate', 1);

			if ($table->store())
			{
				$message = JText::sprintf('COM_EASYSHOP_SET_DEFAULT_SUCCESS', $table->get('name'));
				$type    = 'message';
			}
		}
		elseif (!$table->get('state'))
		{
			$message = JText::sprintf('COM_EASYSHOP_SET_DEFAULT_STATE_FAILURE', $table->get('name'));
		}

		$this->setRedirect(JRoute::_('index.php?option=com_easyshop&view=' . $this->view_list, false), $message, $type);
	}
}
