<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use ES\Classes\Order;
use Joomla\CMS\Form\FormHelper;

defined('_JEXEC') or die;
FormHelper::loadFieldClass('list');

class JFormFieldOrderStatus extends JFormFieldList
{
	protected $type = 'OrderStatus';

	protected function getOptions()
	{
		$options  = parent::getOptions();
		$order    = easyshop(Order::class);
		$excludes = trim($this->getAttribute('excludes', ''));

		if (!empty($excludes))
		{
			$excludes = explode(',', $this->getAttribute('excludes', ''));
		}

		foreach ($order->getOrderStatus() as $value => $text)
		{
			if (empty($excludes) || !in_array($value, $excludes))
			{
				$option        = new stdClass;
				$option->value = $value;
				$option->text  = $text;
				$options[]     = $option;
			}
		}

		if ($this->multiple
			&& !empty($this->value)
			&& !is_array($this->value)
		)
		{
			$this->value = explode(',', $this->value);
		}

		return $options;
	}
}
