<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\Translator;
use Joomla\CMS\Categories\Categories;

class EasyshopProductCategories extends Categories
{
	public function __construct($options = [])
	{
		if (empty($options['table']))
		{
			$options['table'] = '#__easyshop_products';
		}

		if (empty($options['extension']))
		{
			$options['extension'] = 'com_easyshop.product';
		}

		parent::__construct($options);
	}

	public function getByAlias($alias)
	{
		static $aliasIdMaps = [];

		if (!isset($aliasIdMaps[$alias]))
		{
			$db    = easyshop('db');
			$query = $db->getQuery(true)
				->select('c.id')
				->from($db->quoteName('#__categories', 'c'))
				->where('c.extension LIKE ' . $db->quote('com_easyshop.%'))
				->where('c.alias = ' . $db->quote($alias));
			$db->setQuery($query);
			$aliasIdMaps[$alias] = (int) ($db->loadResult() ?: 0);
		}

		if ($aliasIdMaps[$alias])
		{
			return $this->get($aliasIdMaps[$alias]);
		}

		return false;
	}

	public function get($id = 'root', $forceLoad = false)
	{
		if ($category = parent::get($id, $forceLoad))
		{
			Translator::translateObject($category, 'categories', $category->id);
		}

		return $category;
	}
}

class EasyshopBrandCategories extends EasyshopProductCategories
{
	public function __construct($options = [])
	{
		$options['extension'] = 'com_easyshop.brand';
		parent::__construct($options);
	}
}

class EasyshopCategories extends EasyshopProductCategories
{

}
