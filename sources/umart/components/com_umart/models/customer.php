<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\CustomField;
use Umart\Classes\User;
use Umart\Form\Form;
use Umart\Model\AdminModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Helper\AuthenticationHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\User as CMSUser;

class UmartModelCustomer extends AdminModel
{
	public function getForm($data = [], $loadData = true)
	{
		Form::addFieldPath(UMART_COMPONENT_ADMINISTRATOR . '/models/fields');
		Form::addFormPath(UMART_COMPONENT_SITE . '/models/forms');
		Form::addRulePath(UMART_COMPONENT_ADMINISTRATOR . '/models/rules');
		/**@var $customField CustomField */
		$user        = $this->getUser();
		$customField = plg_sytem_umart_main(CustomField::class, [
			'reflector'    => 'com_umart.user',
			'reflector_id' => $user->id,
		]);
		$addressForm = $customField->getFormFieldData(0, ['checkout_field' => 1]);
		$input       = plg_sytem_umart_main('app')->input;
		$view        = $input->get('view');
		$form        = new Form('com_umart.customer', ['control' => 'jform']);
		$formData    = [];

		if ($user->id && (int) $user->state !== 1)
		{
			throw new RuntimeException(Text::sprintf('COM_UMART_USER_WAS_BANNED_WARNING', $user->getName()));
		}

		if ($user->user_id)
		{
			$form->loadFile('profile');
		}
		else
		{
			$form->loadFile('guest');

			if ($view === 'customer')
			{
				$form->removeGroup('registration');
				$form->loadFile('registration');
			}

			$tfa = AuthenticationHelper::getTwoFactorMethods();

			if (!is_array($tfa) || count($tfa) <= 1)
			{
				$form->removeField('secretkey', 'login');
			}

			if (plg_sytem_umart_main('config', 'guest_checkout', 1))
			{
				$form->setFieldAttribute('password1', 'required', 'false', 'registration');
				$form->setFieldAttribute('password2', 'required', 'false', 'registration');
			}
			else
			{
				$form->setFieldAttribute('register', 'type', 'hidden', 'registration');
				$form->setValue('register', 'registration', '1');
			}
		}

		if ($form->load($addressForm['form']))
		{
			if ($loadData)
			{
				if ($user->id)
				{
					CMSFactory::getLanguage()->load('com_users');
					$jUser    = $user->get();
					$formData = [
						'id'           => $user->id,
						'avatar'       => $user->avatar,
						'name'         => $jUser->name,
						'username'     => $jUser->username,
						'email1'       => $jUser->email,
						'email2'       => $jUser->email,
						'customfields' => $addressForm['data'],
						'timezone'     => $jUser->getParam('timezone', ''),
						'secret_key'   => $user->secret_key,
					];
				}
				else
				{
					$formData = plg_sytem_umart_main('app')->getUserState('com_umart.customer.registration.data', []);
				}

				$form->bind($formData);
			}
		}

		$this->postFormHook($form, $formData);

		if ($form->getField('recaptcha', 'registration')
			&& $view === 'cart'
			&& $input->get('layout') === 'checkout'
		)
		{
			$form->removeField('recaptcha', 'registration');
		}

		return $form;
	}

	public function getUser($load = true)
	{
		static $user = null;

		if (null === $user)
		{
			/** @var $user User */
			$user = plg_sytem_umart_main(User::class);
		}

		if ($load)
		{
			if (!$user->load())
			{
				$jUserId = (int) $user->get()->id;

				if ($jUserId > 0)
				{
					Table::addIncludePath(UMART_COMPONENT_ADMINISTRATOR . '/tables');
					$userTable = Table::getInstance('User', 'UmartTable');

					if (!$userTable->load(['user_id' => $jUserId]))
					{
						$userTable->bind([
							'user_id' => $jUserId,
							'state'   => 1,
						]);
						$userTable->store();
					}
				}
			}
		}

		return $user;
	}

	public function register(&$data, $forceActivate = false)
	{
		/** @var $customField CustomField */
		$customField = plg_sytem_umart_main(CustomField::class, [
			'reflector' => 'com_umart.user',
		]);

		if (!empty($data['customfields']))
		{
			$fieldName = $customField->findFieldByName('user_name');

			if ($fieldName && isset($data['customfields'][$fieldName->id]))
			{
				$name         = trim($data['customfields'][$fieldName->id], '[]');
				$data['name'] = str_replace('][', ' ', $name);
			}
			else
			{
				$data['name'] = $data['email'];
			}
		}

		if (isset($data['timezone']))
		{
			$data['params'] = [
				'admin_style'    => '',
				'admin_language' => '',
				'language'       => '',
				'editor'         => '',
				'helpsite'       => '',
				'timezone'       => $data['timezone'],
			];
			unset($data['timezone']);
		}

		CMSFactory::getLanguage()->load('com_users');
		$config       = plg_sytem_umart_main('config');
		$autoActivate = $forceActivate ?: $config->get('user_auto_activate', 1);
		$userId       = null;

		if ($autoActivate)
		{
			PluginHelper::importPlugin('user');
			$userConfig = ComponentHelper::getParams('com_users');
			$user       = new CMSUser;
			$userData   = [
				'id'       => 0,
				'name'     => $data['name'],
				'username' => $data['username'],
				'email'    => PunycodeHelper::emailToPunycode($data['email1']),
				'password' => $data['password1'],
				'params'   => isset($data['params']) ? $data['params'] : [],
				'groups'   => [$userConfig->get('new_usertype', 2)], // Get the default new user group, Registered if not specified.
				'block'    => 0,
			];

			$result = ($user->bind($userData) && $user->save());

			if (!$result)
			{
				$this->setError(Text::sprintf('COM_USERS_REGISTRATION_SAVE_FAILED', $user->getError()));
			}

			$userId = (int) $user->id;
		}
		else
		{
			if (IS_JOOMLA_V4)
			{
				BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_users/Model', 'Joomla\Component\Users\Site\Model\\');
				Form::addFormPath(JPATH_SITE . '/components/com_users/forms');
				$registrationModel = BaseDatabaseModel::getInstance('RegistrationModel', 'Joomla\Component\Users\Site\Model\\');
			}
			else
			{
				BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_users/models', 'UsersModel');
				Form::addFormPath(JPATH_SITE . '/components/com_users/models/forms');
				$registrationModel = BaseDatabaseModel::getInstance('Registration', 'UsersModel');
			}

			$result = $registrationModel->register($data);

			if (false === $result)
			{
				$this->setError($registrationModel->getError());
			}
			else
			{
				$result = true;
			}
		}

		if ($result && !empty($data['customfields']))
		{
			if (null === $userId)
			{
				$userId = plg_sytem_umart_main('state')->get('customer.juser_register_id');
			}

			$user       = $this->getUser(false);
			$data['id'] = $userId;

			if ($user->load(['user_id' => $userId]))
			{
				$customField->setUp(['reflector_id' => $user->id]);

				if ($result = $customField->save($data))
				{
					plg_sytem_umart_main('app')->triggerEvent('onUmartCustomerRegister', [$user, $data]);
				}
			}
		}

		return $result;
	}
}
