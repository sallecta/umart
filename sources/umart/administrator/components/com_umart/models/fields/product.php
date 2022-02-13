<?php
/**
 
 * @version     1.0.5
 
 
 
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
			$model   = JModelLegacy::getInstance('Products', 'UmartModel', ['ignore_request' => true]);
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
