<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\Translator;
use Joomla\CMS\Categories\Categories;

class UmartProductCategories extends Categories
{
	public function __construct($options = [])
	{
		if (empty($options['table']))
		{
			$options['table'] = '#__umart_products';
		}

		if (empty($options['extension']))
		{
			$options['extension'] = 'com_umart.product';
		}

		parent::__construct($options);
	}

	public function getByAlias($alias)
	{
		static $aliasIdMaps = [];

		if (!isset($aliasIdMaps[$alias]))
		{
			$db    = plg_sytem_umart_main('db');
			$query = $db->getQuery(true)
				->select('c.id')
				->from($db->quoteName('#__categories', 'c'))
				->where('c.extension LIKE ' . $db->quote('com_umart.%'))
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

class UmartBrandCategories extends UmartProductCategories
{
	public function __construct($options = [])
	{
		$options['extension'] = 'com_umart.brand';
		parent::__construct($options);
	}
}

class UmartCategories extends UmartProductCategories
{

}
