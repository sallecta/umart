<?php

/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\Currency;
use ES\Classes\CustomField;
use ES\Table\AbstractTable;

class EasyshopTableOrder extends AbstractTable
{
	public function store($updateNulls = false)
	{
		$user   = JFactory::getUser();
		$date   = JFactory::getDate();
		$isSite = easyshop('site');
		$db     = easyshop('db');
		$isNew  = empty($this->id);

		if ($user->id)
		{
			if (empty($this->user_id) && $isSite)
			{
				$userTable = JTable::getInstance('User', 'EasyshopTable');

				if ($userTable->load(['user_id' => $user->id]))
				{
					$this->set('user_id', $userTable->id);
				}
			}

			if (empty($this->created_by))
			{
				$this->set('created_by', $user->id);
			}
		}

		if (empty($this->created_date) || $this->created_date == $db->getNullDate())
		{
			$this->set('created_date', $date->toSql());
		}

		if (!$isNew && (empty($this->modified_date) || $this->modified_date == $db->getNullDate()))
		{
			$this->set('modified_date', $date->toSql());
		}

		if (empty($this->currency_id))
		{
			$this->set('currency_id', easyshop(Currency::class)->getDefault()->get('id'));
		}

		$result = parent::store($updateNulls);

		if ($result)
		{
			$id = (int) $this->id;

			if (empty($this->order_code))
			{
				$code = easyshop('config', 'order_code_format', '{random:7}');
				$rand = null;

				if (preg_match('/\{random:([1-9])\}/i', $code, $matches))
				{
					JLoader::import('joomla.crypt.crypt');
					$rand = substr(bin2hex(JCrypt::genRandomBytes(8)), 0, (int) $matches[1]);
					$code = preg_replace('/\{random:([1-9])\}/i', $rand, $code);
				}

				if (stripos($code, '{orderID}') !== false)
				{
					$code = str_ireplace('{orderID}', $id, $code);
				}

				$db    = $this->getDbo();
				$query = $db->getQuery(true)
					->select('COUNT(id)')
					->from($db->quoteName($this->_tbl))
					->where($db->quoteName('order_code') . ' = ' . $db->quote($code))
					->where($db->quoteName('id') . ' <> ' . $id);
				$db->setQuery($query);

				while ($db->loadResult())
				{
					$newRand = substr(bin2hex(JCrypt::genRandomBytes(4)), 0, -1);
					$code    = $rand ? str_replace($rand, $newRand, $code) : $newRand;
					$query->clear('where')
						->where($db->quoteName('order_code') . ' = ' . $db->quote($code))
						->where($db->quoteName('id') . ' <> ' . $id);
					$db->setQuery($query);
				}

				$query->clear()
					->update($db->quoteName($this->_tbl))
					->set($db->quoteName('order_code') . ' = ' . $db->quote($code))
					->where($db->quoteName('id') . ' = ' . $id);
				$db->setQuery($query)
					->execute();
				$this->set('order_code', $code);

				if (empty($this->token) || strlen($this->token) !== 40)
				{
					$token = sha1(serialize($this->getProperties()));
					$db    = $this->getDbo();
					$query = $db->getQuery(true)
						->select('COUNT(id)')
						->from($db->quoteName($this->_tbl))
						->where($db->quoteName('token') . ' = ' . $db->quote($token))
						->where($db->quoteName('id') . ' <> ' . $id);
					$db->setQuery($query);

					while ($db->loadResult())
					{
						$token = sha1($token . ':' . time());
						$query->clear('where')
							->where($db->quoteName('token') . ' = ' . $db->quote($token))
							->where($db->quoteName('id') . ' <> ' . $id);
						$db->setQuery($query);
					}

					$query->clear()
						->update($db->quoteName($this->_tbl))
						->set($db->quoteName('token') . ' = ' . $db->quote($token))
						->where($db->quoteName('id') . ' = ' . $id);
					$db->setQuery($query)
						->execute();
					$this->set('token', $token);
				}
			}
		}

		return $result;
	}

	public function delete($pk = null)
	{
		$result = parent::delete($pk);

		if ($result && $pk)
		{
			$pk = (int) $pk;

			/** @var $customField CustomField */
			$customField = easyshop(CustomField::class);
			$customField->removeValues('com_easyshop.order.billing_address', $pk);
			$customField->removeValues('com_easyshop.order.shipping_address', $pk);
			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->select('a.id')
				->from($db->quoteName('#__easyshop_order_products', 'a'))
				->where('a.order_id = ' . $pk);
			$db->setQuery($query);

			if ($orderProductIds = $db->loadColumn())
			{
				$orderProductIds = implode(',', $orderProductIds);
				$query->clear()
					->delete($db->quoteName('#__easyshop_order_product_options'))
					->where($db->quoteName('order_product_id') . ' IN (' . $orderProductIds . ')');
				$db->setQuery($query)
					->execute();

				$query->clear()
					->delete($db->quoteName('#__easyshop_order_products'))
					->where($db->quoteName('id') . ' IN (' . $orderProductIds . ')');
				$db->setQuery($query)
					->execute();
			}
		}

		return $result;
	}

	protected function getTableDBName()
	{
		return '#__easyshop_orders';
	}
}
