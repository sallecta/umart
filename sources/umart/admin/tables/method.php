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

class EasyshopTableMethod extends AbstractTable
{
	protected $_jsonEncode = ['taxes'];

	protected function getTableDBName()
	{
		return '#__easyshop_methods';
	}

	public function store($updateNulls = false)
	{
		if ($this->is_default)
		{
			$db       = $this->getDbo();
			$query    = $db->getQuery(true)
				->update($db->quoteName($this->_tbl, 'a'))
				->set('a.is_default = 0')
				->innerJoin($db->quoteName('#__extensions', 'a2') . ' ON a2.extension_id = a.plugin_id');
			$subQuery = $db->getQuery(true)
				->select('a3.folder')
				->from($db->quoteName('#__extensions', 'a3'))
				->where('a3.extension_id = ' . (int) $this->plugin_id);
			$query->where('a2.folder = (' . (string) $subQuery->__toString() . ')');

			if ($this->id)
			{
				$query->where($db->quoteName('id') . ' <> ' . (int) $this->id);
			}

			$db->setQuery($query)
				->execute();
		}

		return parent::store($updateNulls);
	}
}
