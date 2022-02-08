<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ES\Plugin;
defined('_JEXEC') or die;

use DOMDocument;
use DOMElement;
use DOMXPath;
use ES\Classes\Addon;
use JForm;
use JHtml;
use Joomla\Registry\Registry;
use JText;

class PluginAddon extends PluginLegacy
{
	protected $addons = [];
	protected $addonDisplay = null;
	protected $addonIcon = null;
	/**
	 * @var $addonClass Addon
	 * @since 1.0.0
	 */
	protected $addonClass = null;

	public function __construct($subject, array $config = [])
	{
		parent::__construct($subject, $config);
		$this->addonClass = easyshop(Addon::class);

		if ($this->params->exists('display_on'))
		{
			$this->addonDisplay = (int) $this->params->get('display_on', 0);
		}
	}

	public function onEasyshopAddonRegister($addOnName)
	{
		if (in_array($addOnName, $this->addons, true))
		{
			return $this->_name;
		}
	}

	public function onEasyshopAddonPrepareForm(JForm $form, $data)
	{
		$formName = 'plg_easyshop_' . $this->_name . '.addon.' . $data->addOnName;

		if (empty($this->addons)
			|| $form->getName() != $formName
		)
		{
			return;
		}

		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->load(JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/' . $this->_name . '.xml');

		if (false === $dom)
		{
			return;
		}

		$xpath  = new DOMXPath($dom);
		$addOn  = $xpath->query('//addon/' . $data->addOnName);
		$fields = $xpath->query('//fields[@name="params"]/fieldset[@addon="' . $data->addOnName . '"]/field');

		if (!$addOn->length || !$fields->length)
		{
			return;
		}

		if ($fields->length)
		{
			foreach ($fields as $field)
			{
				/** @var $field DOMElement */
				$name    = $field->getAttribute('name');
				$type    = $field->getAttribute('type');
				$inherit = $field->getAttribute('optionInherit');

				if ($name)
				{
					$field->setAttribute('default', '');
					$field->removeAttribute('filter');

					if ($type === 'radio' || $type === 'switcher')
					{
						$type = 'list';
						$field->setAttribute('class', 'uk-select');
						$field->setAttribute('type', $type);
					}

					if ($type === 'list' || $inherit === 'true' || $inherit === '1')
					{
						$option = $dom->createElement('option', 'COM_EASYSHOP_INHERIT_FROM_PLUGIN_PARAMS');
						$option->setAttribute('value', '');
						$field->insertBefore($option, $field->firstChild);
					}
				}
			}

			$form->load($dom->saveXML(), true, '//fields[@name="params"]/fieldset[@addon="' . $data->addOnName . '"]');
		}
	}

	public function onProductAddonDisplay($product)
	{
		$buffer = $this->_name . 'Buffer';

		if ($this->addonDisplay === 2 && isset($product->{$buffer}))
		{
			$this->addonClass->setBuffer($this->_name, $product->{$buffer});
		}
	}

	public function onProductBeforeRenderTab($product)
	{
		$buffer = $this->_name . 'Buffer';

		if ($this->addonDisplay === 0 && isset($product->{$buffer}))
		{
			JHtml::_('ukui.addTab', JText::_('PLG_EASYSHOP_' . strtoupper($this->_name) . '_DISPLAY_TAB_TITLE'), $this->addonIcon);
			echo $product->{$buffer};
			JHtml::_('ukui.endTab');
		}
	}

	public function onProductAfterDisplay($product)
	{
		$buffer = $this->_name . 'Buffer';

		if ($this->addonDisplay === 1 && isset($product->{$buffer}))
		{
			return $product->{$buffer};
		}
	}

	protected function mergeDataParams(Registry $data)
	{
		if ($data->count())
		{
			foreach ($data->toArray() as $name => $value)
			{
				if ($this->params->exists($name) && trim($value) === '')
				{
					$data->set($name, $this->params->get($name));
				}
			}
		}
		else
		{
			$data = $this->params;
		}

		return $data;
	}
}
