<?php
/**
 
 * @version     1.0.5
 
 
 
 */

defined('_JEXEC') or die;
JFormHelper::loadFieldClass('list');

class JFormFieldDimension extends JFormFieldList
{
	protected $type = 'dimension';

	protected function getOptions()
	{
		static $options = null;

		if (null === $options)
		{
			$options    = parent::getOptions();
			$params     = JComponentHelper::getParams('com_umart');
			$dimensions = explode(',', $params->get('dimension_unit', 'm,dm,cm,mm,in,ft,yd'));

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
