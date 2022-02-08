<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use ES\Classes\Utility;
use Joomla\CMS\Form\FormHelper;

defined('_JEXEC') or die;
FormHelper::loadFieldClass('list');

class JFormFieldEasyshopSort extends JFormFieldList
{
	protected $type = 'EasyshopSort';

	protected function getOptions()
	{
		static $options = null;

		if (null === $options)
		{
			$options = parent::getOptions();
			$values  = (array) $this->value;

			foreach (easyshop(Utility::class)->getOrderingData() as $data)
			{
				$option           = new stdClass;
				$option->value    = $data['value'];
				$option->text     = $data['text'];
				$option->selected = in_array($data['value'], $values);
				$options[]        = $option;
			}
		}

		return $options;
	}
}
