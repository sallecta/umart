<?php

/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Controller\FormController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

class EasyshopControllerMethod extends FormController
{
	protected $pluginGroup;

	public function __construct(array $config)
	{
		parent::__construct($config);
		$type = $this->input->getCmd('filter_type');

		if (!in_array($type, ['shipping', 'payment'], true))
		{
			throw new RuntimeException('Easyshop plugin error: invalid filter_group.');
		}

		$this->pluginGroup = $type;
	}

	public function add()
	{
		$layout   = strtolower($this->input->getWord('layout'));
		$methodId = $this->input->getInt('method_id');

		if ($layout != 'select' && !$methodId)
		{
			$url     = Route::_('index.php?option=com_easyshop&view=methods&filter_type=' . $this->pluginGroup . '&layout=select', false);
			$message = Text::_('COM_EASYSHOP_WARNING_SELECT_A_METHOD');

			return $this->setRedirect($url, $message)
				->redirect();
		}

		return parent::add();

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

	protected function getMethod()
	{
		$methodId = $this->input->getInt('method_id');
		$model    = $this->getModel('Methods', 'EasyshopModel', ['ignore_request' => true]);
		$methods  = $model->getMethods();

		if (!isset($methods[$methodId]))
		{
			throw new RuntimeException(Text::sprintf('COM_EASYSHOP_ERROR_METHOD_ID_NOT_FOUND', $methodId), 404);
		}

		return $methods[$methodId];
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
}
