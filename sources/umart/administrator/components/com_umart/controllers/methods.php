<?php

/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
use Umart\Controller\AdminController;

class UmartControllerMethods extends AdminController
{
	protected $pluginGroup;

	public function __construct(array $config)
	{
		parent::__construct($config);

		$type = plg_sytem_umart_main('app')->input->getCmd('filter_type');

		if (!in_array($type, ['shipping', 'payment'], true))
		{
			throw new Exception(JText::_('COM_UMART_ERROR_INVALID_METHOD'));
		}

		$this->pluginGroup = $type;
	}

	public function publish()
	{
		parent::publish();

		$groupURL = $this->view_list . '&filter_type=' . $this->pluginGroup;
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $groupURL, false));
	}

	public function checkin()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$ids             = JFactory::getApplication()->input->post->get('cid', array(), 'array');
		$model           = $this->getModel();
		$return          = $model->checkin($ids);
		$this->view_list = $this->view_list . '&filter_type=' . $this->pluginGroup;

		if ($return === false)
		{
			$message = JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message, 'error');

			return false;
		}
		else
		{
			$message = JText::plural($this->text_prefix . '_N_ITEMS_CHECKED_IN', count($ids));
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message);

			return true;
		}
	}

	public function delete()
	{
		parent::delete();
		$groupURL = $this->view_list . '&filter_type=' . $this->pluginGroup;
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $groupURL, false));
	}

	protected function getMethod()
	{
		$methodId = $this->input->getInt('method_id');
		$model    = $this->getModel('Methods', 'UmartModel', ['ignore_request' => true]);
		$methods  = $model->getMethods();

		if (!isset($methods[$methodId]))
		{
			throw new RuntimeException(JText::sprintf('COM_UMART_ERROR_METHOD_ID_NOT_FOUND', $methodId), 404);
		}

		return $methods[$methodId];
	}

	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$tmpl   = $this->input->get('tmpl');
		$layout = $this->input->get('layout', 'edit', 'string');
		$method = $this->getMethod();
		$append = '&filter_type=' . $this->pluginGroup . '&method_id=' . $method->method_id;

		if ($tmpl)
		{
			$append .= '&tmpl=' . $tmpl;
		}

		if ($layout)
		{
			$append .= '&layout=' . $layout;
		}

		if ($recordId)
		{
			$append .= '&' . $urlVar . '=' . $recordId;
		}

		return $append;
	}

	protected function getRedirectToListAppend()
	{
		$tmpl   = $this->input->get('tmpl');
		$append = '&filter_type=' . $this->pluginGroup;

		if ($tmpl)
		{
			$append .= '&tmpl=' . $tmpl;
		}

		return $append;
	}

	public function setDefault()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		JTable::addIncludePath(UMART_COMPONENT_ADMINISTRATOR . '/tables');
		$pks     = $this->input->get('cid', [], 'array');
		$table   = JTable::getInstance('Method', 'UmartTable');
		$pk      = (int) @$pks[0];
		$message = JText::_('COM_UMART_SET_DEFAULT_FAILURE');
		$type    = 'error';

		if ($table->load($pk) && $table->get('state'))
		{
			$table->set('is_default', 1);

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

		$this->setRedirect(JRoute::_('index.php?option=com_umart&view=' . $this->view_list . $this->getRedirectToListAppend(), false), $message, $type);
	}
}
