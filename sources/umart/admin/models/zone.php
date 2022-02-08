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

class EasyshopModelZone extends AdminModel
{
	public function save($data)
	{
		if ($data['type'] === 'state')
		{
			$data['parent_id'] = (int) $data['country_id'];
		}
		elseif ($data['type'] === 'subzone')
		{
			$data['parent_id'] = (int) $data['state_id'];
		}
		else
		{
			$data['parent_id'] = 0;
		}

		return parent::save($data);
	}

	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		if ($item->id && in_array($item->type, ['state', 'subzone'], true))
		{
			if ($item->type === 'state')
			{
				$item->country_id = $item->parent_id;
			}
			else
			{
				$table = $this->getTable('Zone');
				$table->load($item->parent_id);
				$item->country_id = $table->parent_id;
				$item->state_id   = $item->parent_id;
			}
		}

		return $item;
	}
}
