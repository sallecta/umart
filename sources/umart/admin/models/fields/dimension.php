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

class JFormFieldDimension extends JFormFieldList
{
	protected $type = 'dimension';

	protected function getOptions()
	{
		static $options = null;

		if (null === $options)
		{
			$options    = parent::getOptions();
			$params     = JComponentHelper::getParams('com_easyshop');
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
