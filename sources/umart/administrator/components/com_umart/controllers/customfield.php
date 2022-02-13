<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\User;
use Umart\Controller\FormController;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Table\Table;

class UmartControllerCustomfield extends FormController
{
	protected $reflector;

	public function __construct(array $config)
	{
		parent::__construct($config);

		if (empty($this->reflector))
		{
			$this->reflector = $this->input->getCmd('reflector', 'com_umart');
		}
	}

	public function checkoutField()
	{
		Session::checkToken('get') or jexit(Text::_('JINVALID_TOKEN'));

		if (!plg_sytem_umart_main(User::class)->can('edit'))
		{
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 404);
		}

		$pk = $this->input->getInt('id');

		Table::addIncludePath(UMART_COMPONENT_ADMINISTRATOR . '/tables');
		$table = Table::getInstance('Customfield', 'UmartTable');
		$app   = CMSFactory::getApplication();

		if ($table->load($pk))
		{
			$table->set('checkout_field', $table->get('checkout_field') ? 0 : 1);

			if ($table->store())
			{
				$app->enqueueMessage(Text::_('COM_UMART_FIELD_CHECKOUT_TOGGLE_SUCCESS'));
			}
			else
			{
				$app->enqueueMessage($table->getError(), 'error');
			}
		}
		else
		{
			$app->enqueueMessage(Text::_('COM_UMART_ERROR_FIELD_NOT_FOUND'), 'error');
		}

		$this->setRedirect(Route::_('index.php?option=com_umart&view=customfields&reflector=' . $this->input->get('reflector'), false))
			->redirect();
	}

	public function loadAssignByGroup()
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$dataName = 'com_umart.edit.customfield.data';
		$oldData  = (array) plg_sytem_umart_main('app')->getUserState($dataName, []);
		$data     = array_merge($oldData, (array) $this->input->get('jform', [], 'array'));
		plg_sytem_umart_main('app')->setUserState($dataName, $data);
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
