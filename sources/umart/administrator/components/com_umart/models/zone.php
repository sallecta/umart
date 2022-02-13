<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
use Umart\Model\AdminModel;

class UmartModelZone extends AdminModel
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
