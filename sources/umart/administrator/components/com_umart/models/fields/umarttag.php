<?php
/**
 
 
 
 
 
 */

defined('_JEXEC') or die;
JFormHelper::loadFieldClass('list');

class JFormFieldUmartTag extends JFormFieldList
{
	protected $type = 'UmartTag';

	protected function getOptions()
	{
		static $options = null;

		if (null === $options)
		{
			$context = trim($this->getAttribute('context', 'com_umart.product'));
			$db      = plg_sytem_umart_main('db');
			$query   = plg_sytem_umart_main('db')->getQuery(true)
				->select('a.id AS value, a.name AS text')
				->from(plg_sytem_umart_main('db')->qn('#__umart_tags', 'a'))
				->where('a.state = 1 AND a.context = ' . $db->quote($context));
			$db->setQuery($query);
			$options = parent::getOptions();

			if ($rows = $db->loadObjectList())
			{
				$options = array_merge($options, $rows);
			}
		}

		return $options;
	}
}
