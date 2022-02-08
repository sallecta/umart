<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ES\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Table\Table as CMSTable;
use Joomla\CMS\Factory as CMSFactory;

abstract class AbstractTable extends CMSTable
{
	abstract protected function getTableDBName();

	public function __construct(&$db)
	{
		parent::__construct($this->getTableDBName(), $this->getTableId(), $db);
		$this->setColumnAlias('published', 'state');
		$this->setColumnAlias('created', 'created_date');
	}

	public function check()
	{
		$fields = array_keys($this->getProperties());

		if (in_array('name', $fields) && trim($this->name) == '')
		{
			$this->setError(Text::_('COM_EASYSHOP_WARNING_PROVIDE_VALID_NAME'));

			return false;
		}

		if (in_array('title', $fields) && trim($this->title) == '')
		{
			$this->setError(Text::_('COM_EASYSHOP_WARNING_PROVIDE_VALID_TITLE'));

			return false;
		}

		if (in_array('alias', $fields) && trim($this->alias) == '')
		{
			if (isset($this->name))
			{
				$this->alias = $this->name;
			}
			elseif (isset($this->title))
			{
				$this->alias = $this->title;
			}

			$this->alias = ApplicationHelper::stringURLSafe($this->alias);

			if (trim(str_replace('-', '', $this->alias)) == '')
			{
				$this->alias = CMSFactory::getDate()->format('Y-m-d-H-i-s');
			}
		}

		return true;
	}

	public function store($updateNulls = false)
	{
		$fields      = array_keys($this->getProperties());
		$createdDate = $this->getColumnAlias('created');
		$createdBy   = $this->getColumnAlias('created_by');
		$db          = $this->getDbo();
		$date        = CMSFactory::getDate();

		if (in_array($createdDate, $fields))
		{
			if (empty($this->{$createdDate}) || $this->{$createdDate} == $db->getNullDate())
			{
				$this->{$createdDate} = $date->toSql();
			}
		}

		if (empty($this->{$createdBy}) && in_array($createdBy, $fields))
		{
			$this->{$createdBy} = CMSFactory::getUser()->id;
		}

		if (in_array('modified_date', $fields)
			&& (empty($this->modified_date) || $this->modified_date == $db->getNullDate())
		)
		{
			$this->modified_date = $date->toSql();
		}

		return parent::store($updateNulls);
	}

	protected function getTableId()
	{
		return 'id';
	}
}
