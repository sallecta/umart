<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
use Umart\Model\AdminModel;

class UmartModelCurrency extends AdminModel
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
