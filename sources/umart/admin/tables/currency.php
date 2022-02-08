<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Table\AbstractTable;

class EasyshopTableCurrency extends AbstractTable
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
		return '#__easyshop_currencies';
	}
}
