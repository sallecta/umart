<?php

/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\User;
use Umart\Model\AdminModel;
use Joomla\CMS\Table\Table;

class UmartModelUser extends AdminModel
{
	public function save($data)
	{
		if ($result = parent::save($data))
		{
			$user = plg_sytem_umart_main(User::class);

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
		$app         = plg_sytem_umart_main('app');
		$start       = (int) $app->getUserStateFromRequest('com_umart.user_order.list.start', 'start', 0, 'uint');
		$ordersModel = plg_sytem_umart_main('getModel', 'Orders', UMART_COMPONENT_ADMINISTRATOR, ['ignore_request' => true]);
		$ordersModel->setState('filter.user_id', $userId);
		$ordersModel->setState('list.start', $start);
		$ordersModel->setState('list.limit', 15);
		$ordersModel->setState('list.ordering', 'a.created_date');
		$ordersModel->setState('list.direction', 'DESC');

		return $ordersModel;
	}
}
