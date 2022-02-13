<?php

/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\CustomField;
use Umart\Table\AbstractTable;
use Joomla\CMS\Crypt\Crypt;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

class UmartTableUser extends AbstractTable
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
				$this->setError(Text::sprintf('COM_UMART_ERROR_USER_DUPLICATE', $this->user_id));

				return false;
			}
		}

		return $check;
	}

	protected function getTableDBName()
	{
		return '#__umart_users';
	}

	public function delete($pk = null)
	{
		if ($result = parent::delete($pk))
		{
			/** @var $customField CustomField */

			$customField = plg_sytem_umart_main(CustomField::class, [
				'reflector'    => 'com_umart.user',
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
			$table  = Table::getInstance('User', 'UmartTable');
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
