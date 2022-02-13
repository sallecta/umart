<?php
/**
 
 
 
 
 
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('text');

class JFormFieldCallname extends JFormFieldText
{
	protected $type = 'callname';

	protected function getInput()
	{
		$callType = (int) $this->getAttribute('call_name_type', 1);
		$lastName = '';

		if (is_array($this->value))
		{
			$this->value = '[' . join('][', array_values($this->value)) . ']';
		}

		if (strpos($this->value, '][') !== false)
		{
			$parts     = explode('][', trim($this->value, '[]'), 2);
			$firstName = trim($parts[0]);

			if (isset($parts[1]))
			{
				$lastName = trim($parts[1]);
			}
		}
		else
		{
			$firstName = trim($this->value);
		}

		if (!$callType)
		{
			$value       = $firstName . ($lastName ? ' ' . $lastName : '');
			$this->value = trim($value, '[]');
			$this->class = trim('uk-input ' . $this->class);

			return parent::getInput();
		}

		$required = $this->getAttribute('required');
		$required = $required == 'true' || $required == '1' || $required == 'required' ? ' data-rule-required="true" required' : '';
		$classes  = $this->class;
		$html     = [];
		$html[]   = '<div class="umartui_width-1-2 es-multi-input es-firstname-input">';
		$html[]   = '<input type="text" name="' . $this->name . '[0]" id="' . $this->id . '-firstname"' . $required;
		$html[]   = ' class="field-callname uk-input field-callname-firstname ' . $classes . '"';
		$html[]   = ' placeholder="' . JText::_('COM_UMART_FIRSTNAME') . '" value="' . htmlspecialchars($firstName, ENT_COMPAT, 'UTF-8') . '"/></div>';
		$html[]   = '<div class="umartui_width-1-2 es-multi-input es-lastname-input">';
		$html[]   = '<input type="text" name="' . $this->name . '[1]" id="' . $this->id . '-lastname"' . $required;
		$html[]   = ' class="field-callname uk-input field-callname-lastname"';
		$html[]   = ' placeholder="' . JText::_('COM_UMART_LASTNAME') . '" value="' . htmlspecialchars($lastName, ENT_COMPAT, 'UTF-8') . '"/></div>';

		return '<div class="uk-flex">' . implode(PHP_EOL, $html) . '</div>';
	}
}
