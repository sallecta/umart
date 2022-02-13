<?php
/**
 
 
 
 
 
 */

namespace Umart\Classes;

defined('_JEXEC') or die;

class Tax
{
	public function calculate($taxId, $amount = 0.00)
	{
		static $taxes = null;

		if (null === $taxes)
		{
			$db    = plg_sytem_umart_main('db');
			$query = $db->getQuery(true)
				->select('t.id, t.name, t.flat, t.type, t.rate, t.vendor_id')
				->from($db->quoteName('#__umart_taxes', 't'))
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