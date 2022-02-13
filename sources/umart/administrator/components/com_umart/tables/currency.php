<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Table\AbstractTable;

class UmartTableCurrency extends AbstractTable
{
	public function store($updateNulls = false)
	{
		if ($this->is_default)
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->update($db->quoteName($this->_tbl))
				->set($db->quoteName('is_default') . ' = 0');

			if ($this->id)
			{
				$query->where($db->quoteName('id') . ' <> ' . (int) $this->id);
			}

			$db->setQuery($query)
				->execute();
			$this->set('rate', 1);
		}

		$this->set('code', strtoupper($this->code));

		return parent::store($updateNulls);
	}

	protected function getTableDBName()
	{
		return '#__umart_currencies';
	}
}
