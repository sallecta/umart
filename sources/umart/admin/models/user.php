<?php

/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\User;
use ES\Model\AdminModel;
use Joomla\CMS\Table\Table;

class EasyshopModelUser extends AdminModel
{
	public function save($data)
	{
		if ($result = parent::save($data))
		{
			$user = easyshop(User::class);

			if ($user->load($this->getState('user.id', 0)))
			{
				$jUser = Table::getInstance('User');
				$jUser->load($user->user_id);
				$jUser->name = $user->getName();
				$jUser->store();
			}
		}

		return $result;
	}

	public function getOrderModelList($userId)
	{
		$app         = easyshop('app');
		$start       = (int) $app->getUserStateFromRequest('com_easyshop.user_order.list.start', 'start', 0, 'uint');
		$ordersModel = easyshop('getModel', 'Orders', ES_COMPONENT_ADMINISTRATOR, ['ignore_request' => true]);
		$ordersModel->setState('filter.user_id', $userId);
		$ordersModel->setState('list.start', $start);
		$ordersModel->setState('list.limit', 15);
		$ordersModel->setState('list.ordering', 'a.created_date');
		$ordersModel->setState('list.direction', 'DESC');

		return $ordersModel;
	}
}
