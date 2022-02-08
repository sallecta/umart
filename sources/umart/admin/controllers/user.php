<?php
/**
 * @package     com_easyshop
 * @version     1.0.5
 * @author      JoomTech Team
* @copyright   Copyright (C) 2015 - 2019 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Controller\FormController;

class EasyshopControllerUser extends FormController
{
	public function loadOrderList()
	{
		try
		{
			$userId      = (int) $this->input->get('userId', 0, 'uint');
			$userModel   = $this->getModel('User', 'EasyshopModel', ['ignore_request' => true]);
			$ordersModel = $userModel->getOrderModelList($userId);

			if ($orders = $ordersModel->getItems())
			{
				$response = easyshop('renderer')->render('order.summary', [
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

		easyshop('app')->close();
	}
}
