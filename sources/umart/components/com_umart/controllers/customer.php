<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\Cart;
use Umart\Classes\CustomField;
use Umart\Classes\User;
use Umart\Controller\BaseController;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Router\Router;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User as CmsUser;
use Joomla\Registry\Registry;

class UmartControllerCustomer extends BaseController
{
	public function login()
	{
		$this->checkToken('post');
		$jform = $this->app->input->post->get('jform', [], 'array');
		$form  = $this->getModel('Customer')->getForm();
		$jform = $form->filter($jform, 'login');
		$data  = [
			'username' => $jform['login']['username'],
			'password' => $jform['login']['password'],
		];

		if (isset($jform['login']['secretkey']))
		{
			$data['secretkey'] = $jform['login']['secretkey'];
		}

		$return  = $this->app->input->getBase64('return');
		$options = [
			'remember' => $this->app->input->post->getBool('remember', false),
		];

		if ($return && Uri::isInternal(base64_decode($return)))
		{
			$redirect = base64_decode($return);
		}
		else
		{
			$redirect = Route::_(UmartHelperRoute::getCustomerRoute(), false);
		}

		$options['return'] = $redirect;

		// Success
		if (true === $this->app->login($data, $options))
		{
			if ($options['remember'] == true)
			{
				$this->app->setUserState('rememberLogin', true);
			}
		}

		$this->app->redirect($redirect);
	}

	public function register()
	{
		/**
		 * @var $model             UmartModelCustomer
		 * @var $registrationModel UsersModelRegistration
		 * @var $form              Form
		 * @var $cart              Cart
		 */
		$this->checkToken('post');

		if (!plg_sytem_umart_main('config', 'enable_registration', 1))
		{
			$this->redirectBackPage();
		}

		$model = $this->getModel('Customer');
		$form  = $model->getForm();
		$data  = $this->app->input->post->get('jform', [], 'array');
		$form->removeGroup('login');
		$data = $form->filter($data);
		$this->app->setUserState('com_umart.customer.registration.data', $data);

		if (!$form->validate($data))
		{
			$errors = $form->getErrors();

			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$this->app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$this->app->enqueueMessage($errors[$i], 'warning');
				}
			}

			$this->redirectBackPage();
		}

		$registrationData = [
			'username'     => $data['registration']['username'],
			'email1'       => $data['registration']['email1'],
			'email2'       => $data['registration']['email2'],
			'password1'    => $data['registration']['password1'],
			'password2'    => $data['registration']['password2'],
			'customfields' => $data['customfields'],
		];

		$userId = $model->register($registrationData);

		if (false === $userId)
		{
			$this->app->enqueueMessage($model->getError(), 'warning');
		}

		$this->app->enqueueMessage(Text::sprintf('COM_UMART_REGISTRATION_SUCCESSFUL', $registrationData['username']), 'success');
		$cart         = plg_sytem_umart_main(Cart::class);
		$checkoutData = $cart->getCheckoutData();

		if (!isset($checkoutData['billing_address']))
		{
			$checkoutData['billing_address'] = $registrationData['customfields'];
		}

		if (!isset($checkoutData['shipping_address']))
		{
			$checkoutData['shipping_address'] = $checkoutData['billing_address'];
		}

		$cart->setCheckoutData($checkoutData, true);

		if ((int) $userId > 0)
		{
			$this->app->login([
				'username' => $registrationData['username'],
				'password' => $registrationData['password1'],
			]);
		}

		$this->redirectBackPage();
	}

	public function save()
	{
		/**
		 * @var $model       UmartModelCustomer
		 * @var $form        JForm
		 * @var $user        User
		 * @var $customField CustomField
		 */
		$this->checkToken('post');
		$user  = plg_sytem_umart_main(User::class);
		$jform = $this->app->input->post->get('jform', [], 'array');
		$model = $this->getModel('Customer');
		$form  = $model->getForm([], false);
		$form->removeGroup('login');

		if (empty($jform['password1']) && empty($jform['password2']))
		{
			$form->removeField('password1');
			$form->removeField('password2');
		}

		$data = $form->filter($jform);

		if (!$form->validate($data))
		{
			$errors = $form->getErrors();

			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$this->app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$this->app->enqueueMessage($errors[$i], 'warning');
				}
			}

			$this->redirectBackPage();
		}

		if (!$user->load()
			|| !$data['id']
			|| (int) $user->id !== $data['id']
			|| (int) $user->state !== 1
		)
		{
			$this->redirectBackPage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
		}

		$customField = plg_sytem_umart_main(CustomField::class, [
			'reflector'    => 'com_umart.user',
			'reflector_id' => $user->id,
		]);
		$jUser       = new CmsUser($user->user_id);
		$profileData = [
			'email' => PunycodeHelper::emailToPunycode($data['email1']),
		];

		if (!empty($data['password1']))
		{
			$profileData['password']  = $data['password1'];
			$profileData['password2'] = $data['password2'];
		}

		$userParams = $jUser->params;

		if (!($userParams instanceof Registry))
		{
			$userParams = new Registry($userParams);
		}

		$userParams->set('timezone', isset($data['timezone']) ? $data['timezone'] : '');
		$profileData['params'] = $userParams->toArray();

		if (!$jUser->bind($profileData))
		{
			$this->redirectBackPage(Text::sprintf('COM_UMART_PROFILE_BIND_FAILED', $user->getError()), 'error');
		}

		PluginHelper::importPlugin('user');

		// Retrieve the user groups so they don't get overwritten
		unset ($jUser->groups);
		$jUser->groups = Access::getGroupsByUser($jUser->id, false);

		// Store the data.
		if (!$jUser->save() || !$customField->save($data))
		{
			$this->redirectBackPage($user->getError(), 'error');
		}

		$userTable = $user->getTable();
		$userTable->set('avatar', $data['avatar']);
		$userTable->store();
		$this->app->triggerEvent('onUmartCustomerAfterSave', [$user, $data]);
		$this->redirectBackPage(Text::_('COM_UMART_PROFILE_SAVE_SUCCESS'), 'success');
	}

	public function page()
	{
		$user = plg_sytem_umart_main(User::class);

		if (!$user->load() || !$user->isCustomer())
		{
			$this->redirectBackPage();
		}

		if ($page = $this->app->input->getWord('page'))
		{
			$this->app->setUserState('com_umart.customer.page', $page);
		}

		if ($orderId = $this->app->input->get('orderId', 0, 'uint'))
		{
			$this->app->setUserState('com_umart.customer.order_id', $orderId);
			$this->app->setUserState('com_umart.customer.guest_order_id', 0);
		}

		foreach ($this->app->input->getArray() as $name => $value)
		{
			$this->app->setUserState('com_umart.customer.' . $page . '.' . $name, $value);
		}

		$this->redirectBackPage();
	}

	public function goBackPage()
	{
		$page = $this->app->getUserState('com_umart.customer.page', 'orders');

		if ($page === 'order')
		{
			$page = 'orders';
		}
		else
		{
			$this->app->setUserState('com_umart.customer.guest_order_id', 0);
		}

		$this->app->setUserState('com_umart.customer.page', $page);
		$this->redirectBackPage();
	}

	public function cancelOrder()
	{
		$this->checkToken('post');
		/**@var $user User */
		$user    = plg_sytem_umart_main(User::class);
		$orderId = (int) $this->app->getUserState('com_umart.customer.order_id', 0);
		$user->load();
		$checkOwner = true;

		if ($orderId < 1)
		{
			$orderId    = (int) $this->app->getUserState('com_umart.customer.guest_order_id', 0);
			$checkOwner = false;
		}

		if ($orderId > 0)
		{
			Table::addIncludePath(UMART_COMPONENT_ADMINISTRATOR . '/tables');
			$orderTable = Table::getInstance('Order', 'UmartTable');

			if ($orderTable->load($orderId) && (int) $orderTable->state === UMART_ORDER_CREATED)
			{
				if (!$checkOwner || (int) $orderTable->user_id === (int) $user->id)
				{
					$orderTable->state = UMART_ORDER_CANCELLED;

					if ($orderTable->store())
					{
						$this->app->setUserState('com_umart.customer.guest_order_id', 0);
						$this->app->enqueueMessage(Text::sprintf('COM_UMART_ORDER_CANCELLED_SUCCESS', $orderTable->order_code), 'success');
						$this->app->triggerEvent('onUmartChangeState', ['com_umart.order', [$orderId], 5]);
					}
				}
			}
		}

		$this->redirectBackPage();
	}

	public function trackOrder()
	{
		if (plg_sytem_umart_main('config', 'enable_track_order', 1))
		{
			$email     = $this->app->input->get('email', '', 'TRIM');
			$orderCode = $this->app->input->get('orderCode', '', 'TRIM');
			$found     = false;

			if (!empty($email) && !empty($orderCode))
			{
				Table::addIncludePath(UMART_COMPONENT_ADMINISTRATOR . '/tables');
				$orderTable = Table::getInstance('Order', 'UmartTable');

				if ($orderTable->load(['order_code' => $orderCode, 'user_email' => $email])
					&& (int) $orderTable->state !== -2
				)
				{
					$found = true;
					$this->app->setUserState('com_umart.customer.guest_order_id', $orderTable->id);
					$this->app->enqueueMessage(Text::sprintf('COM_UMART_ORDER_CODE_FOUND', $orderCode), 'success');
				}
				else
				{
					$this->app->enqueueMessage(Text::sprintf('COM_UMART_ORDER_CODE_NOT_EXISTS', $orderCode), 'warning');
				}
			}

			if ($found)
			{
				$this->app->setUserState('com_umart.customer.page', 'order');
				$this->app->redirect(Route::_(UmartHelperRoute::getCustomerRoute(), false));
			}
		}

		$this->redirectBackPage();
	}
}
