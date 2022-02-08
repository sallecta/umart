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
use Joomla\CMS\Language\Text;

class EasyshopTableCustomField extends AbstractTable
{
	public function check()
	{
		/** @var $customField CustomField */
		$customField = easyshop(CustomField::class);

		if (!$customField->isValidReflector($this->reflector))
		{
			$this->setError(Text::_('COM_EASYSHOP_WARNING_PROVIDE_VALID_REFLECTOR'));

			return false;
		}

		if (empty($this->field_name))
		{
			$this->field_name = preg_replace('/\-+/', '_', $this->name);
			$this->field_name = preg_replace('/[^0-9a-zA-Z_]/', '', $this->field_name);
		}

		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(a.id)')
			->from($db->quoteName('#__easyshop_customfields', 'a'))
			->where('a.reflector = ' . $db->quote($this->reflector))
			->where('a.field_name = ' . $db->quote($this->field_name));

		if ($this->id)
		{
			$query->where('a.id <> ' . (int) $this->id);
		}

		$db->setQuery($query);

		if ($db->loadResult())
		{
			$this->setError(Text::_('COM_EASYSHOP_ERROR_FIELD_NAME_UNIQUE'));

			return false;
		}

		return parent::check();
	}

	public function delete($pk = null)
	{
		$table = clone $this;

		if ($table->load($pk) && $table->get('protected'))
		{
			$this->setError(Text::sprintf('COM_EASYSHOP_ERROR_CUSTOMFIELD_HAS_PROTECTED', $table->get('name'), $pk));

			return false;
		}

		return parent::delete($pk);
	}

	public function store($updateNulls = false)
	{
		if ($this->protected)
		{
			if ((int) $this->state !== 1)
			{
				$this->state = 1;
			}

			$this->field_name = null;
		}

		return parent::store($updateNulls);
	}

	protected function getTableDBName()
	{
		return '#__easyshop_customfields';
	}

}
