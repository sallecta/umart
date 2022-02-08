<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\User;
use ES\Controller\FormController;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Table\Table;

class EasyshopControllerCustomfield extends FormController
{
	protected $reflector;

	public function __construct(array $config)
	{
		parent::__construct($config);

		if (empty($this->reflector))
		{
			$this->reflector = $this->input->getCmd('reflector', 'com_easyshop');
		}
	}

	public function checkoutField()
	{
		Session::checkToken('get') or jexit(Text::_('JINVALID_TOKEN'));

		if (!easyshop(User::class)->can('edit'))
		{
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 404);
		}

		$pk = $this->input->getInt('id');

		Table::addIncludePath(ES_COMPONENT_ADMINISTRATOR . '/tables');
		$table = Table::getInstance('Customfield', 'EasyshopTable');
		$app   = CMSFactory::getApplication();

		if ($table->load($pk))
		{
			$table->set('checkout_field', $table->get('checkout_field') ? 0 : 1);

			if ($table->store())
			{
				$app->enqueueMessage(Text::_('COM_EASYSHOP_FIELD_CHECKOUT_TOGGLE_SUCCESS'));
			}
			else
			{
				$app->enqueueMessage($table->getError(), 'error');
			}
		}
		else
		{
			$app->enqueueMessage(Text::_('COM_EASYSHOP_ERROR_FIELD_NOT_FOUND'), 'error');
		}

		$this->setRedirect(Route::_('index.php?option=com_easyshop&view=customfields&reflector=' . $this->input->get('reflector'), false))
			->redirect();
	}

	public function loadAssignByGroup()
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$dataName = 'com_easyshop.edit.customfield.data';
		$oldData  = (array) easyshop('app')->getUserState($dataName, []);
		$data     = array_merge($oldData, (array) $this->input->get('jform', [], 'array'));
		easyshop('app')->setUserState($dataName, $data);
		$this->setRedirect(base64_decode($this->input->getBase64('returnPage')))
			->redirect();
	}

	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId);
		$append .= '&reflector=' . $this->reflector;

		return $append;
	}

	protected function getRedirectToListAppend()
	{
		$append = parent::getRedirectToListAppend();
		$append .= '&reflector=' . $this->reflector;

		return $append;
	}

}
