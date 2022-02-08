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

use JLoader;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\User as CMSUser;
use PrivacyPlugin;
use PrivacyRemovalStatus;
use PrivacyTableRequest;
use RuntimeException;

JLoader::register('PrivacyPlugin', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/plugin.php');
JLoader::register('PrivacyRemovalStatus', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/removal/status.php');

class Privacy extends PrivacyPlugin
{
	protected $db;

	public function onPrivacyCanRemoveData(PrivacyTableRequest $request, CMSUser $user = null)
	{
		$status = new PrivacyRemovalStatus;

		if (!$user)
		{
			return $status;
		}

		if ($user->authorise('core.admin', 'com_easyshop'))
		{
			$status->canRemove = false;
			$status->reason    = Text::_('COM_EASYSHOP_PRIVACY_DELETE_ADMIN_USER_DENIED');
		}

		return $status;
	}

	public function onPrivacyCollectAdminCapabilities()
	{
		$capabilities = [
			Text::_('COM_EASYSHOP_PRIVACY_BULLET_POINTS_DESC_1'),
			Text::_('COM_EASYSHOP_PRIVACY_BULLET_POINTS_DESC_2'),
			Text::_('COM_EASYSHOP_PRIVACY_BULLET_POINTS_DESC_3'),
		];

		PluginHelper::importPlugin('easyshop');
		PluginHelper::importPlugin('easyshoppayment');
		PluginHelper::importPlugin('easyshopshipping');
		easyshop('app')->triggerEvent('onEasyshopPrivacyCollectAdminCapabilities', [&$capabilities]);

		return [
			Text::_('COM_EASYSHOP') => $capabilities,
		];
	}

	public function onPrivacyExportRequest(PrivacyTableRequest $request, CMSUser $user = null)
	{
		$domains = [];

		if (!$user)
		{
			return $domains;
		}

		try
		{
			$utilityClass = easyshop(Utility::class);

			// Export Customer Data
			$userClass           = $this->loadUserClass($user->id);
			$domain              = $this->createDomain('easyshop_customer', 'easyshop_customer_data');
			$userData            = get_object_vars($userClass);
			$userData['address'] = $utilityClass->formatAddress($userClass->getFields());
			$domain->addItem($this->createItemFromArray($userData, $userClass->id));
			$domains[] = $domain;

			// Export Orders Data
			$query = $this->db->getQuery(true)
				->select('a.id')
				->from($this->db->quoteName('#__easyshop_orders', 'a'))
				->where('a.user_id = ' . $userClass->id);
			$this->db->setQuery($query);

			if ($orderIds = $this->db->loadColumn())
			{
				$orderClass = new Order;
				$domain     = $this->createDomain('easyshop_orders', 'easyshop_orders_data');

				foreach ($orderIds as $orderId)
				{
					if ($orderClass->load($orderId))
					{
						$orderData = [];

						foreach (get_object_vars($orderClass) as $name => $value)
						{
							if ($name == 'currency')
							{
								$value = $orderClass->currency->get('code');
							}
							elseif (is_array($value) || is_object($value))
							{
								$value = json_encode($value);
							}

							$orderData[$name] = $value;
						}

						$address                       = $orderClass->getAddress($orderClass->id);
						$orderData['billing_address']  = $utilityClass->formatAddress($address['billing']);
						$orderData['shipping_address'] = $utilityClass->formatAddress($address['shipping']);
						$domain->addItem($this->createItemFromArray($orderData, $orderClass->id));
					}
				}

				$domains[] = $domain;
			}

			// Export Logs Data
			$query->clear()
				->select('a.*')
				->from($this->db->quoteName('#__easyshop_logs', 'a'))
				->innerJoin($this->db->quoteName('#__easyshop_users', 'a2') . ' ON a2.user_id = a.juser_id')
				->where('a2.id = ' . $user->id);
			$this->db->setQuery($query);

			if ($logs = $this->db->loadAssocList())
			{
				$domain = $this->createDomain('easyshop_logs', 'easyshop_logs_data');

				foreach ($logs as $log)
				{
					$domain->addItem($this->createItemFromArray($log, $log['id']));
				}

				$domains[] = $domain;
			}
		}
		catch (RuntimeException $e)
		{

		}

		return $domains;
	}

	protected function loadUserClass($jUserId)
	{
		$userClass = easyshop(User::class);

		if (!$userClass->load(['user_id' => $jUserId]))
		{
			throw new RuntimeException('Unknown User ID: ' . $jUserId);
		}

		return $userClass;
	}

	public function onPrivacyRemoveData(PrivacyTableRequest $request, CMSUser $user = null)
	{
		if (!$user)
		{
			return;
		}

		try
		{
			$userClass = $this->loadUserClass($user->id);
			$userTable = $userClass->getTable();
			$userId    = (int) $userTable->id;
			$jUserId   = (int) $userClass->juser->id;
			$userTable->bind([
				'vendor'           => 0,
				'state'            => 0,
				'scores'           => 0,
				'level'            => 0,
				'created_by'       => 0,
				'checked_out'      => 0,
				'avatar'           => '',
				'secret_key'       => '',
				'created_date'     => '1970-01-01 00:00:00',
				'checked_out_time' => '1970-01-01 00:00:00',
			]);
			$userTable->store();

			// Pseudonymized order email
			$query = $this->db->getQuery(true)
				->update($this->db->quoteName('#__easyshop_orders'))
				->set($this->db->quoteName('user_email') . ' = ' . $this->db->quote('user' . $userId . '@example.com'))
				->where($this->db->quoteName('user_id') . ' = ' . $userId);
			$this->db->setQuery($query)
				->execute();

			// Delete customer fields
			$query->clear()
				->delete($this->db->quoteName('#__easyshop_customfield_values'))
				->where($this->db->quoteName('reflector') . ' = ' . $this->db->quote('com_easyshop.user'))
				->where($this->db->quoteName('reflector_id') . ' = ' . $userId);
			$this->db->setQuery($query)
				->execute();

			// Delete customer order fields
			$subQuery = $this->db->getQuery(true)
				->select($this->db->quoteName('id'))
				->from($this->db->quoteName('#__easyshop_orders'))
				->where($this->db->quoteName('user_id') . ' = ' . $userId);
			$query->clear()
				->delete($this->db->quoteName('#__easyshop_customfield_values'))
				->where('(' . $this->db->quoteName('reflector') . ' = ' . $this->db->quote('com_easyshop.order.billing_address') . ' OR ' . $this->db->quoteName('reflector') . ' = ' . $this->db->quote('com_easyshop.order.shipping_address') . ')')
				->where($this->db->quoteName('reflector_id') . ' IN (' . (string) $subQuery->__toString() . ')');
			$this->db->setQuery($query)
				->execute();

			// Delete user logs
			$query->clear()
				->delete($this->db->quoteName('#__easyshop_logs'))
				->where($this->db->quoteName('juser_id') . ' = ' . $jUserId);
			$this->db->setQuery($query)
				->execute();

		}
		catch (RuntimeException $e)
		{
			return $e;
		}
	}
}
