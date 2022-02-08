<?php

/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\CustomField;
use ES\Table\AbstractTable;
use Joomla\CMS\Crypt\Crypt;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

class EasyshopTableUser extends AbstractTable
{
	public function check()
	{
		if ($check = parent::check())
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->select('COUNT(a.id)')
				->from($db->quoteName($this->getTableDBName(), 'a'))
				->where('a.user_id = ' . (int) $this->user_id);

			if ($this->id)
			{
				$query->where('a.id <> ' . (int) $this->id);
			}

			$db->setQuery($query);

			if ($db->loadResult())
			{
				$this->setError(Text::sprintf('COM_EASYSHOP_ERROR_USER_DUPLICATE', $this->user_id));

				return false;
			}
		}

		return $check;
	}

	protected function getTableDBName()
	{
		return '#__easyshop_users';
	}

	public function delete($pk = null)
	{
		if ($result = parent::delete($pk))
		{
			/** @var $customField CustomField */

			$customField = easyshop(CustomField::class, [
				'reflector'    => 'com_easyshop.user',
				'reflector_id' => $pk
			]);

			$customField->removeValues();
		}

		return $result;
	}

	public function store($updateNulls = false)
	{
		$this->generateSecretKey();

		return parent::store($updateNulls);
	}

	public function generateSecretKey()
	{
		if (empty($this->id)
			|| empty($this->secret_key)
			|| strlen($this->secret_key) !== 32)
		{
			$table  = Table::getInstance('User', 'EasyshopTable');
			$secret = bin2hex(Crypt::genRandomBytes(16));

			while ($table->load(['secret_key' => $secret]))
			{
				$secret = bin2hex(Crypt::genRandomBytes(16));
			}

			$this->set('secret_key', $secret);

			return true;
		}

		$this->set('secret_key', null);

		return false;
	}
}
