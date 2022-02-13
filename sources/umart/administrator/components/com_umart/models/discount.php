<?php

/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Form\Form;
use Umart\Model\AdminModel;

class UmartModelDiscount extends AdminModel
{
	public function save($data)
	{
		$data['user_groups']    = implode(',', !empty($data['user_groups']) ? $data['user_groups'] : []);
		$data['currencies']     = implode(',', !empty($data['currencies']) ? $data['currencies'] : []);
		$data['categories']     = implode(',', !empty($data['categories']) ? $data['categories'] : []);
		$data['products']       = implode(',', !empty($data['products']) ? $data['products'] : []);
		$data['zone_countries'] = implode(',', !empty($data['zone_countries']) ? $data['zone_countries'] : []);
		$data['zone_states']    = implode(',', !empty($data['zone_states']) ? $data['zone_states'] : []);

		return parent::save($data);
	}

	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			if (!empty($item->user_groups))
			{
				$item->user_groups = (array) explode(',', trim($item->user_groups));
			}

			if (!empty($item->currencies))
			{
				$item->currencies = (array) explode(',', trim($item->currencies));
			}

			if (!empty($item->categories))
			{
				$item->categories = (array) explode(',', trim($item->categories));
			}

			if (!empty($item->products))
			{
				$item->products = (array) explode(',', trim($item->products));
			}

			if (!empty($item->zone_countries))
			{
				$item->zone_countries = (array) explode(',', trim($item->zone_countries));
			}

			if (!empty($item->zone_states))
			{
				$item->zone_states = (array) explode(',', trim($item->zone_states));
			}
		}

		return $item;
	}

	protected function preprocessUmartForm(Form $form, $data, $group = 'umart')
	{
		parent::preprocessUmartForm($form, $data, $group);
		$jform = plg_sytem_umart_main('app')->input->get('jform', [], 'array');

		if (isset($jform['type']) && (int) $jform['type'] === 1)
		{
			$form->setFieldAttribute('coupon_code', 'required', 'required');
		}
	}
}
