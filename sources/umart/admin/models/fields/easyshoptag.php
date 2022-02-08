<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;
JFormHelper::loadFieldClass('list');

class JFormFieldEasyshopTag extends JFormFieldList
{
	protected $type = 'EasyshopTag';

	protected function getOptions()
	{
		static $options = null;

		if (null === $options)
		{
			$context = trim($this->getAttribute('context', 'com_easyshop.product'));
			$db      = easyshop('db');
			$query   = easyshop('db')->getQuery(true)
				->select('a.id AS value, a.name AS text')
				->from(easyshop('db')->qn('#__easyshop_tags', 'a'))
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
