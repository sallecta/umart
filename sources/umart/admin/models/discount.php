<?php

/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Form\Form;
use ES\Model\AdminModel;

class EasyshopModelDiscount extends AdminModel
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

	protected function preprocessESForm(Form $form, $data, $group = 'easyshop')
	{
		parent::preprocessESForm($form, $data, $group);
		$jform = easyshop('app')->input->get('jform', [], 'array');

		if (isset($jform['type']) && (int) $jform['type'] === 1)
		{
			$form->setFieldAttribute('coupon_code', 'required', 'required');
		}
	}
}
