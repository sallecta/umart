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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Registry\Registry;
use stdClass;

class Method
{
	protected static $shippingMethods = [];
	protected static $paymentMethods = [];

	public function get($pk = 0)
	{
		$methods = $this->load();

		return isset($methods[$pk]) ? $methods[$pk] : false;
	}

	public function load()
	{
		static $items = null;

		if (null === $items)
		{
			$items = [];
			$db    = easyshop('db');
			$query = $db->getQuery(true)
				->select('a.id, a.name, a.show_name, a.plugin_id, a.image, a.description, a.description_type, a.flat_fee, a.percentage_fee, a.taxes, a.params, e.element, e.folder AS plugin_group, a.is_default, a.order_status')
				->from($db->quoteName('#__easyshop_methods', 'a'))
				->innerJoin($db->quoteName('#__extensions', 'e') . ' ON a.plugin_id = e.extension_id')
				->where('a.state = 1 AND e.enabled = 1 AND e.type = ' . $db->quote('plugin'))
				->where('e.folder IN (' . $db->quote('easyshop') . ',' . $db->quote('easyshoppayment') . ',' . $db->quote('easyshopshipping') . ')')
				->order('a.ordering ASC');

			if (Multilanguage::isEnabled())
			{
				$query->where('a.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
			}

			easyshop('app')->triggerEvent('onEasyshopPrepareListQuery', ['com_easyshop.methods', $query]);
			$db->setQuery($query);

			if ($methods = $db->loadObjectList())
			{
				/** @var User $userClass */
				$userClass = easyshop(User::class);

				foreach ($methods as $method)
				{
					Translator::translateObject($method, 'easyshop_methods', $method->id);
					$registry = new Registry;
					$registry->loadString($method->params);
					$userAccessGroups = (array) $registry->get('access_user_groups', []);
					$method->taxes    = json_decode($method->taxes ?: '{}', true) ?: [];

					if (empty($userAccessGroups) || $userClass->accessGroups($userAccessGroups, true))
					{
						$method->params     = $registry;
						$items[$method->id] = $method;
					}
				}

			}
		}

		return $items;
	}

	public function getPaymentMethods()
	{
		return $this->getGroup('easyshoppayment');
	}

	public function getGroup($groupName)
	{
		static $groups = [];

		if (!isset($groups[$groupName]))
		{
			$groups[$groupName] = [];

			foreach ($this->load() as $id => $method)
			{
				if ($method->plugin_group == $groupName)
				{
					$groups[$groupName][$id] = $method;
				}
			}
		}

		return $groups[$groupName];
	}

	public function getShippingMethods()
	{
		return $this->getGroup('easyshopshipping');
	}

	public function addShippingMethod($class = null)
	{
		return static::addMethod($class, 'shipping');
	}

	protected function addMethod($class, $type)
	{
		if ($class instanceof stdClass)
		{
			if ($type == 'shipping')
			{
				static::$shippingMethods[$class->id] = $class;
			}
			else
			{
				static::$paymentMethods[$class->id] = $class;
			}
		}

		return $type == 'shipping' ? static::$shippingMethods : static::$paymentMethods;
	}

	public function addPaymentMethod($class = null)
	{
		return static::addMethod($class, 'payment');
	}
}
