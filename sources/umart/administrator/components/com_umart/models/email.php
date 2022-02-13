<?php

/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Model\AdminModel;
use Joomla\CMS\Plugin\PluginHelper;

class UmartModelEmail extends AdminModel
{
	public function getForm($data = [], $loadData = true)
	{
		PluginHelper::importPlugin('umart_payment');
		PluginHelper::importPlugin('umartshipping');

		if ($form = parent::getForm($data, $loadData))
		{
			$app = plg_sytem_umart_main('app');
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
