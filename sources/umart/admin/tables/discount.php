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

class EasyshopTableDiscount extends AbstractTable
{
	protected function getTableDBName()
	{
		return '#__easyshop_discounts';
	}

	public function check()
	{
		if ((int) $this->type === 1)
		{
			if (empty($this->coupon_code))
			{
				$this->setError(JText::_('COM_EASYSHOP_ERROR_COUPON_CODE_EMPTY'));

				return false;
			}

			$clone = clone $this;

			if ($clone->load(['coupon_code' => $this->coupon_code]) && $clone->id != $this->id)
			{
				$this->setError(JText::_('COM_EASYSHOP_ERROR_COUPON_CODE_DUPLICATE'));

				return false;
			}
		}

		return true;
	}
}
