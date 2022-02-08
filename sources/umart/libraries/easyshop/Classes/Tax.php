<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ES\Classes;

defined('_JEXEC') or die;

class Tax
{
	public function calculate($taxId, $amount = 0.00)
	{
		static $taxes = null;

		if (null === $taxes)
		{
			$db    = easyshop('db');
			$query = $db->getQuery(true)
				->select('t.id, t.name, t.flat, t.type, t.rate, t.vendor_id')
				->from($db->quoteName('#__easyshop_taxes', 't'))
				->where('t.state = 1');
			$taxes = $db->setQuery($query)->loadObjectList('id');
		}

		$taxAmount = 0.00;

		if (is_array($taxId))
		{
			foreach ($taxId as $id)
			{
				$taxAmount += $this->calculate($id, $amount);
			}
		}
		elseif (isset($taxes[$taxId]))
		{
			$taxAmount = $taxes[$taxId]->type ? (((float) $amount * (float) $taxes[$taxId]->rate) / 100) : (float) $taxes[$taxId]->flat;
		}

		return $taxAmount;
	}
}