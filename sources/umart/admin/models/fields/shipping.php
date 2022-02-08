<?php
/**
 * @package     com_easyshop
 * @version     1.0.5
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;
JFormHelper::loadFieldClass('list');

class JFormFieldShipping extends JFormFieldList
{
	protected $type = 'shipping';

	protected function getOptions()
	{
		static $options = null;

		if (null === $options)
		{
			$options      = parent::getOptions();
			$methodsModel = easyshop('model', 'methods', ES_COMPONENT_ADMINISTRATOR);
			$methodsModel->setState('list.select', 'a.id AS value, a.name AS text');
			$methodsModel->setState('filter.vendor_id', $this->getAttribute('vendor_id', 0));
			$methodsModel->setState('filter.published', 1);
			$methodsModel->setState('filter.type', 'shipping');

			if ($items = $methodsModel->getItems())
			{
				$options = array_merge($options, $items);
			}
		}

		return $options;
	}
}
