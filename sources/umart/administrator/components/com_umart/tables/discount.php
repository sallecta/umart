<?php

/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
use Umart\Table\AbstractTable;

class UmartTableDiscount extends AbstractTable
{
	protected function getTableDBName()
	{
		return '#__umart_discounts';
	}

	public function check()
	{
		if ((int) $this->type === 1)
		{
			if (empty($this->coupon_code))
			{
				$this->setError(JText::_('COM_UMART_ERROR_COUPON_CODE_EMPTY'));

				return false;
			}

			$clone = clone $this;

			if ($clone->load(['coupon_code' => $this->coupon_code]) && $clone->id != $this->id)
			{
				$this->setError(JText::_('COM_UMART_ERROR_COUPON_CODE_DUPLICATE'));

				return false;
			}
		}

		return true;
	}
}
