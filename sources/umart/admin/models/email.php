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
use Joomla\CMS\Plugin\PluginHelper;

class EasyshopModelEmail extends AdminModel
{
	public function getForm($data = [], $loadData = true)
	{
		PluginHelper::importPlugin('easyshoppayment');
		PluginHelper::importPlugin('easyshopshipping');

		if ($form = parent::getForm($data, $loadData))
		{
			$app = easyshop('app');
			$form->setFieldAttribute('send_from_name', 'default', $app->get('fromname'));
			$form->setFieldAttribute('send_from_email', 'default', $app->get('mailfrom'));
		}

		return $form;
	}

	public function save($data)
	{
		$data['order_status']  = json_encode($data['order_status']);
		$data['order_payment'] = json_encode($data['order_payment']);

		return parent::save($data);
	}

	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		if (!empty($item->order_status))
		{
			$item->order_status = json_decode($item->order_status);
		}

		if (!empty($item->order_payment))
		{
			$item->order_payment = json_decode($item->order_payment);
		}

		return $item;
	}
}
