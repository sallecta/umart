<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\CustomField;
use ES\Table\AbstractTable;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\String\StringHelper;

class EasyshopTableProduct extends AbstractTable
{
	public function check()
	{
		if (!empty($this->metakey))
		{
			$afterClean = StringHelper::str_ireplace(["\n", "\r", "\"", "<", ">"], '', $this->metakey);
			$keys       = explode(',', $afterClean);
			$cleanKeys  = [];

			foreach ($keys as $key)
			{
				if (trim($key))
				{
					$cleanKeys[] = trim($key);
				}
			}

			$this->metakey = implode(', ', $cleanKeys);
		}

		if (!$this->id)
		{
			if (!$this->getRules())
			{
				$rules = $this->getDefaultAssetValues('com_easyshop');
				$this->setRules($rules);
			}
		}

		if (empty($this->weight))
		{
			$this->weight = 0.00;
		}

		return parent::check();
	}

	protected function getDefaultAssetValues($component)
	{
		$db    = CMSFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__assets'))
			->where($db->quoteName('name') . ' = ' . $db->quote($component));
		$db->setQuery($query);
		$assetId = (int) $db->loadResult();

		return Access::getAssetRules($assetId);
	}

	public function store($updateNulls = false)
	{
		$table    = Table::getInstance('Product', 'EasyshopTable');
		$nullDate = $this->getDbo()->getNullDate();

		if ($table->load(['alias' => $this->alias, 'category_id' => $this->category_id]) && ($table->id != $this->id || $this->id == 0))
		{
			$this->setError(Text::_('COM_EASYSHOP_ERROR_PRODUCT_UNIQUE_ALIAS'));

			return false;
		}

		if (empty($this->sale_from_date)
			|| empty($this->sale_to_date)
			|| $nullDate === $this->sale_from_date
			|| $nullDate === $this->sale_to_date
		)
		{
			$this->sale_from_date = $nullDate;
			$this->sale_to_date   = $nullDate;
		}

		return parent::store($updateNulls);
	}

	public function delete($pk = null)
	{
		$result = parent::delete($pk);

		if ($result && $pk)
		{
			$pk    = (int) $pk;
			$db    = easyshop('db');
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__easyshop_medias'))
				->where($db->quoteName('product_id') . ' = ' . $pk);
			$db->setQuery($query)
				->execute();

			/** @var $customField CustomField */
			$customField = easyshop(CustomField::class);
			$customField->removeValues('com_easyshop.product.customfield', $pk);
		}

		return $result;
	}

	protected function getTableDBName()
	{
		return '#__easyshop_products';
	}

	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_easyshop.product.' . (int) $this->$k;
	}

	protected function _getAssetTitle()
	{
		return $this->name;
	}

	protected function _getAssetParentId(Table $table = null, $id = null)
	{
		$assetId = null;

		if ($this->category_id)
		{
			$query = $this->_db->getQuery(true)
				->select($this->_db->quoteName('asset_id'))
				->from($this->_db->quoteName('#__categories'))
				->where($this->_db->quoteName('id') . ' = ' . (int) $this->category_id);
			$this->_db->setQuery($query);

			if ($result = $this->_db->loadResult())
			{
				$assetId = (int) $result;
			}
		}

		if ($assetId)
		{
			return $assetId;
		}
		else
		{
			return parent::_getAssetParentId($table, $id);
		}
	}
}
