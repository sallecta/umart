<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ES\Classes;
defined('_JEXEC') or die;

use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\User as CMSUser;
use Joomla\Registry\Registry;
use RuntimeException;

class User
{
	/**
	 * @var Registry $dataParams
	 * @since 1.2.9
	 */
	protected static $dataParams;
	protected $fields = [];
	protected $loaded = false;
	/**
	 * @var CMSUser $juser
	 * @since 1.0.0
	 */

	protected $juser;

	public function can($core, $asset = 'com_easyshop')
	{
		return $this->core($core, $asset);
	}

	public function core($core, $asset = 'com_easyshop', $stateOverride = true)
	{
		if ($stateOverride)
		{
			$stateCore = easyshop('state')->get('user.core.' . $asset . '.' . str_replace('.', '_', $core));

			if (is_bool($stateCore))
			{
				return $stateCore;
			}
		}

		return $this->get()->authorise('core.' . $core, $asset);
	}

	public function get($id = null)
	{
		return CMSFactory::getUser($id);
	}

	public function getName($full = false)
	{
		if (empty($this->id))
		{
			$this->load();
		}

		if (empty($this->id))
		{
			return null;
		}

		$nameField = null;

		foreach ($this->fields as $field)
		{
			if ($field->field_name === 'user_name')
			{
				return $full ? $field : $field->display;
			}

			if ($field->type === 'callname' && null === $nameField)
			{
				$nameField = $field;
			}
		}

		if ($nameField)
		{
			return $full ? $nameField : $nameField->display;
		}

		return $this->__get('name');
	}

	public function load($id = null, $publish = true)
	{
		$table = $this->getTable();

		if (is_array($id))
		{
			$keys = $id;
		}
		else
		{
			$keys = ['id' => $id];

			if (null === $id)
			{
				$userId = (int) CMSFactory::getUser()->id;

				if ($userId < 1)
				{
					return false;
				}

				$keys = ['user_id' => $userId];
			}
			elseif (is_string($id) && strlen($id) === 32)
			{
				$keys = ['secret_key' => $id];
			}
		}

		if ($publish)
		{
			$keys['state'] = 1;
		}

		if ($table->load($keys))
		{
			foreach ($table->getProperties() as $name => $value)
			{
				$this->{$name} = $value;
			}

			$this->fields = $this->getFields($this->id);
			$this->juser  = $this->get($this->user_id);
			$this->loaded = true;

			return true;
		}

		return false;
	}

	public function getTable()
	{
		static $table = null;

		if (null === $table)
		{
			Table::addIncludePath(ES_COMPONENT_ADMINISTRATOR . '/tables');
			$table = Table::getInstance('User', 'EasyshopTable');
		}

		return $table;
	}

	public function getFields($id = 0)
	{
		if (!$id)
		{
			if (empty($this->id))
			{
				return false;
			}

			$id = $this->id;
		}
		/** @var $customField CustomField */
		$customField = easyshop(CustomField::class, [
			'reflector'    => 'com_easyshop.user',
			'reflector_id' => $id,
		]);

		return $customField->getValues($id, true);
	}

	public function __get($name)
	{
		switch ($name)
		{
			case 'id':

				if (!property_exists($this, 'id'))
				{
					$this->load();
				}

				break;
		}

		if (property_exists($this, $name))
		{
			return $this->{$name};
		}

		if (is_object($this->juser)
			&& property_exists($this->juser, $name)
		)
		{
			return $this->juser->{$name};
		}

		return null;
	}

	public function getAddress($full = false)
	{
		if (empty($this->id))
		{
			$this->load();
		}

		if (empty($this->id))
		{
			return null;
		}

		$addressField = null;

		foreach ($this->fields as $field)
		{
			if ($field->field_name === 'user_address')
			{
				return $full ? $field : $field->display;
			}

			if ($field->type === 'address' && null === $addressField)
			{
				$addressField = $field;
			}
		}

		if ($addressField)
		{
			return $full ? $addressField : $addressField->display;
		}

		return $addressField;
	}

	public function isVendor()
	{
		return $this->isCustomer() && (int) $this->__get('vendor');
	}

	public function isCustomer()
	{
		return (int) $this->__get('id') > 0;
	}

	public function stop()
	{
		throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
	}

	public function getAvatar()
	{
		if (empty($this->avatar))
		{
			return ES_MEDIA_URL . '/images/no-avatar.jpg';
		}

		return ES_MEDIA_URL . '/' . $this->avatar;
	}

	public function accessGroups($accessGroups, $forceJUser = false)
	{
		if ($this->loaded && !$forceJUser)
		{
			$user = $this->juser;
		}
		else
		{
			$user = CMSFactory::getUser();
		}

		if (true === $user->get('isRoot'))
		{
			return true;
		}

		foreach ($accessGroups as $group)
		{
			if (in_array($group, $user->getAuthorisedGroups()))
			{
				return true;
			}
		}

		return false;
	}

	public function getParam($path, $default = null)
	{
		$id = $this->__get('id');

		if (empty($id))
		{
			return false;
		}

		$result = $this->getDataRegistry()->get('id' . $id . '.' . $path, $default);

		if (null === $result
			&& ($this->juser instanceof CMSUser)
		)
		{
			return $this->juser->getParam($path, $default);
		}

		return $result;
	}

	protected function getDataRegistry()
	{
		if (!(self::$dataParams instanceof Registry))
		{
			self::$dataParams = new Registry;
		}

		return self::$dataParams;
	}

	public function setParam($path, $value)
	{
		$id = $this->__get('id');

		if (empty($id))
		{
			return false;
		}

		return $this->getDataRegistry()->set('id' . $id . '.' . $path, $value);
	}
}
