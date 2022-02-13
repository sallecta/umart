<?php

/**
 
 
 
 
 
 */

namespace Umart\Classes;

defined('_JEXEC') or die;

class Params
{
	protected $domDocument;
	protected $xpath;

	public function __construct()
	{
		$this->domDocument = new \DOMDocument;
		$this->domDocument->load(UMART_COMPONENT_ADMINISTRATOR . '/config.xml');
		$this->xpath = new \DOMXPath($this->domDocument);
		$fields      = $this->xpath->query('//fieldset[@name="product_listing" or @name="product_detail"]/field');

		foreach ($fields as $field)
		{
			/** @var $field \DOMElement */
			$name = $field->getAttribute('name');
			$type = $field->getAttribute('type');

			if ($name)
			{
				$field->setAttribute('default', '');
				$field->removeAttribute('filter');
			}

			if (in_array($type, ['list', 'radio', 'list', 'switcher']))
			{
				$option = $this->domDocument->createElement('option', 'JGLOBAL_USE_GLOBAL');
				$option->setAttribute('value', '');
				$field->insertBefore($option, $field->firstChild);
			}
		}
	}

	protected function getFieldset($name, $fieldsetNameAlias = null)
	{
		$fieldset = $this->xpath->query('/config/fieldset[@name="' . $name . '"]')[0];

		if (is_string($fieldsetNameAlias))
		{
			$fieldset->setAttribute('name', $fieldsetNameAlias);
		}

		return $fieldset->ownerDocument->saveXML($fieldset);
	}

	public function getData($fieldsetName = null, $fieldsetNameAlias = null)
	{
		if (null !== $fieldsetName && in_array($fieldsetName, ['product_listing', 'product_detail']))
		{
			$data = $this->getFieldset($fieldsetName, $fieldsetNameAlias);
		}
		else
		{
			$data = $this->getFieldset('product_listing', $fieldsetNameAlias);
			$data .= $this->getFieldset('product_detail', $fieldsetNameAlias);
		}

		return '<form><fields name="params">' . $data . '</fields></form>';
	}

}
