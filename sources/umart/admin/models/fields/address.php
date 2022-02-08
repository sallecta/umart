<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\Form\FormHelper;

defined('_JEXEC') or die;

FormHelper::loadFieldClass('text');

class JFormFieldAddress extends JFormFieldText
{
	protected $type = 'address';

	protected function getInput()
	{
		$line2 = $this->getAttribute('address_line_2');

		if (is_array($this->value))
		{
			$this->value = '[' . implode('][', array_values($this->value)) . ']';
		}

		$address2 = '';

		if (strpos($this->value, '][') !== false)
		{
			$parts    = explode('][', trim($this->value, '[]'), 2);
			$address1 = trim($parts[0]);

			if (isset($parts[1]))
			{
				$address2 = trim($parts[1]);
			}
		}
		else
		{
			$address1 = trim($this->value, '[]');
		}

		if (!$line2 || $line2 === '0' || $line2 === 'false')
		{
			$this->value = $address1;
			$this->class = trim('uk-input ' . $this->class);

			return parent::getInput();
		}

		$required = $this->getAttribute('required');
		$required = $required == 'true' || $required == '1' || $required == 'required' ? ' data-rule-required="true" required' : '';
		$classes  = $this->class;
		$html     = [];
		$html[]   = '<div class="uk-width-3-5@m es-multi-input es-address1-input uk-width-1-2">';
		$html[]   = '<input type="text" name="' . $this->name . '[0]" id="' . $this->id . '-line1"' . $required;
		$html[]   = ' class="field-address uk-input field-address-line1 ' . $classes . '"';
		$html[]   = ' placeholder="' . JText::_('COM_EASYSHOP_ADDRESS') . '" value="' . htmlspecialchars($address1, ENT_COMPAT, 'UTF-8') . '"/></div>';
		$html[]   = '<div class="uk-width-2-5@m es-multi-input es-address2-input uk-width-1-2">';
		$html[]   = '<input type="text" name="' . $this->name . '[1]" id="' . $this->id . '-line2"';
		$html[]   = ' class="field-address uk-input field-address-line2"';
		$html[]   = ' placeholder="' . JText::_('COM_EASYSHOP_ADDRESS2') . '" value="' . htmlspecialchars($address2, ENT_COMPAT, 'UTF-8') . '"/></div>';

		return '<div class="uk-flex">' . implode(PHP_EOL, $html) . '</div>';
	}
}
