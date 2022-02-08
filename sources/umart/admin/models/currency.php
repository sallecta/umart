<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;
use ES\Model\AdminModel;

class EasyshopModelCurrency extends AdminModel
{
	public function canDelete($record)
	{
		if ($record->is_default)
		{
			return false;
		}

		return parent::canDelete($record);
	}

	public function publish(&$pks, $value = 1)
	{
		$table = parent::getTable('Currency');

		foreach ($pks as $i => $pk)
		{
			if (!$table->load($pk) || ($table->is_default && (int) $value !== 1))
			{
				unset($pks[$i]);
			}
		}

		return parent::publish($pks, $value);
	}
}
