<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\CustomField;
use Umart\Table\AbstractTable;
use Joomla\CMS\Language\Text;

class UmartTableCustomField extends AbstractTable
{
	public function check()
	{
		/** @var $customField CustomField */
		$customField = plg_sytem_umart_main(CustomField::class);

		if (!$customField->isValidReflector($this->reflector))
		{
			$this->setError(Text::_('COM_UMART_WARNING_PROVIDE_VALID_REFLECTOR'));

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
			->from($db->quoteName('#__umart_customfields', 'a'))
			->where('a.reflector = ' . $db->quote($this->reflector))
			->where('a.field_name = ' . $db->quote($this->field_name));

		if ($this->id)
		{
			$query->where('a.id <> ' . (int) $this->id);
		}

		$db->setQuery($query);

		if ($db->loadResult())
		{
			$this->setError(Text::_('COM_UMART_ERROR_FIELD_NAME_UNIQUE'));

			return false;
		}

		return parent::check();
	}

	public function delete($pk = null)
	{
		$table = clone $this;

		if ($table->load($pk) && $table->get('protected'))
		{
			$this->setError(Text::sprintf('COM_UMART_ERROR_CUSTOMFIELD_HAS_PROTECTED', $table->get('name'), $pk));

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
		return '#__umart_customfields';
	}

}
