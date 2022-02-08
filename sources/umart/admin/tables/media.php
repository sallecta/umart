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

class EasyshopTableMedia extends AbstractTable
{
	protected function getTableDBName()
	{
		return '#__easyshop_medias';
	}

	public function check()
	{
		if ((int) $this->product_id < 1)
		{
			$this->setError(JText::_('COM_EASYSHOP_WARNING_PROVIDE_VALID_PRODUCT'));

			return false;
		}

		if (empty($this->file_path) || !is_file(ES_MEDIA . '/' . $this->file_path))
		{
			$this->setError(JText::_('COM_EASYSHOP_WARNING_PROVIDE_VALID_MEDIA'));

			return false;
		}

		return true;
	}
}
