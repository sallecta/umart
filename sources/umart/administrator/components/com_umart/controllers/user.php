<?php
/**
 
 * @version     1.0.5
 
* @copyright   Copyright (C) 2015 - 2019 github.com/sallecta/umart All Rights Reserved.
 
 */
defined('_JEXEC') or die;

use Umart\Controller\FormController;

class UmartControllerUser extends FormController
{
	public function loadOrderList()
	{
		try
		{
			$userId      = (int) $this->input->get('userId', 0, 'uint');
			$userModel   = $this->getModel('User', 'UmartModel', ['ignore_request' => true]);
			$ordersModel = $userModel->getOrderModelList($userId);

			if ($orders = $ordersModel->getItems())
			{
				$response = plg_sytem_umart_main('renderer')->render('order.summary', [
					'orders'     => $orders,
					'pagination' => $ordersModel->getPagination(),
				]);
			}
			else
			{
				$response = '';
			}
		}
		catch (RuntimeException $e)
		{
			$response = $e;
		}

		echo new JResponseJson($response);

		plg_sytem_umart_main('app')->close();
	}
}
