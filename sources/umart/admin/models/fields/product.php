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

class JFormFieldProduct extends JFormFieldList
{
	protected $type = 'product';

	protected function getOptions()
	{
		static $options = null;

		if (null === $options)
		{
			$options = parent::getOptions();
			$model   = JModelLegacy::getInstance('Products', 'EasyshopModel', ['ignore_request' => true]);
			$model->setState('list.select', 'a.id AS value, a.name AS text');
			$model->setState('filter.published', 1);

			if ($products = $model->getItems())
			{
				$options = array_merge($options, $products);
			}
		}

		return $options;
	}
}
