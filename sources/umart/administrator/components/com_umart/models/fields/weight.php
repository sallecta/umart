<?php
/**
 
 * @version     1.0.5
 
 
 
 */

defined('_JEXEC') or die;
JFormHelper::loadFieldClass('list');

class JFormFieldWeight extends JFormFieldList
{
	protected $type = 'weight';

	protected function getOptions()
	{
		static $options = null;

		if (null === $options)
		{
			$options    = parent::getOptions();
			$params     = JComponentHelper::getParams('com_umart');
			$dimensions = explode(',', $params->get('weight_unit', 'kg,g,mg,lb,oz,ozt'));

			foreach ($dimensions as $dimension)
			{
				$value         = trim(strtolower($dimension));
				$option        = new stdClass;
				$option->value = $value;
				$option->text  = $value;
				$options[]     = $option;
			}
		}

		return $options;
	}
}
