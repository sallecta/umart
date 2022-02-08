<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ES\Form;

defined('_JEXEC') or die;

use ES\Classes\Html;
use InvalidArgumentException;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Form as CMSForm;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use RuntimeException;
use SimpleXMLElement;

class Form extends CMSForm
{
	/**
	 * @var array
	 * @since 1.3.1
	 */
	protected $languagesList = [];

	/**
	 * @var boolean
	 * @since 1.3.1
	 */
	protected $isMultiLanguage = false;

	/**
	 * @var CMSForm
	 * @since 1.3.1
	 */
	protected $translationForm = null;

	/**
	 * @var Registry
	 * @since 1.3.1
	 */
	protected $translationsData = null;

	public function __construct($name, array $options = [])
	{
		parent::__construct($name, $options);
		$this->isMultiLanguage = Multilanguage::isEnabled();
		$this->languagesList   = $this->getLanguagesList();
		$this->translationForm = new CMSForm('com_easyshop.translation', ['control' => 'ESTranslations']);
	}

	public function getLanguagesList($ignoreDefault = false)
	{
		$defaultLangCode = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		static $languages = null;

		if (null === $languages)
		{
			easyshop(Html::class)->addCss('multi-language.css');
			$languagesList = LanguageHelper::getLanguages('lang_code');
			$languages     = array_merge([$defaultLangCode => $languagesList[$defaultLangCode]], $languagesList);
		}

		if ($ignoreDefault)
		{
			$tempLanguages = $languages;
			unset($tempLanguages[$defaultLangCode]);

			return $tempLanguages;
		}

		return $languages;
	}

	public static function getInstance($name, $data = null, $options = [], $replace = true, $xpath = false)
	{
		if (IS_JOOMLA_V4)
		{
			$forms = &static::$forms;

			// Only instantiate the form if it does not already exist.
			if (!isset($forms[$name]))
			{
				$data = trim($data);

				if (empty($data))
				{
					throw new InvalidArgumentException(sprintf('%1$s(%2$s, *%3$s*)', __METHOD__, $name, gettype($data)));
				}

				// Instantiate the form.
				$forms[$name] = new static($name, $options);

				// Load the data.
				if (substr($data, 0, 1) == '<')
				{
					if ($forms[$name]->load($data, $replace, $xpath) == false)
					{
						throw new RuntimeException(sprintf('%s() could not load form', __METHOD__));
					}
				}
				else
				{
					if ($forms[$name]->loadFile($data, $replace, $xpath) == false)
					{
						throw new RuntimeException(sprintf('%s() could not load file', __METHOD__));
					}
				}
			}

			return $forms[$name];
		}

		return parent::getInstance($name, $data, $options, $replace, $xpath);
	}

	public function setTranslationsData($translationsData)
	{
		$this->translationsData = new Registry($translationsData);

		return $this;
	}

	public function getInput($name, $group = null, $value = null)
	{
		// Attempt to get the form field.
		if ($field = $this->getField($name, $group, $value))
		{
			if ($this->isMultiLanguage && 'true' === $field->getAttribute('ESMultiLanguage'))
			{
				return $this->renderMultiLanguageInput($field);
			}

			return $field->__get('input');
		}

		return '';
	}

	protected function renderMultiLanguageInput(FormField $field)
	{
		$i               = 0;
		$name            = $field->__get('fieldname');
		$group           = $field->__get('group') ?: null;
		$fieldXml        = parent::getFieldXml($name, $group);
		$translationData = $this->translationsData;

		if (!($translationData instanceof Registry))
		{
			$translationData = new Registry;
		}

		HTMLHelper::_('ukui.openTab', 'es-language-tab-' . $name);

		foreach ($this->languagesList as $langCode => $language)
		{
			HTMLHelper::_('ukui.addTab', HTMLHelper::_('image', 'mod_languages/' . $language->image . '.gif', '', null, true));

			if ($i++ < 1)
			{
				echo $field->__get('input');
			}
			else
			{
				$tranXml   = clone $fieldXml;
				$tranField = clone $field;
				unset($tranXml['id'], $tranXml['required'], $tranXml['default']);
				$key   = ($group ? $group . '.' : '') . $langCode . '.' . $name;
				$value = $translationData->get($key, null);
				$tranField->setForm($this->translationForm);
				$tranField->setup($tranXml, $value, $langCode);
				echo $tranField->__get('input');
			}

			HTMLHelper::_('ukui.endTab');
		}

		return '<div class="es-language-tabs">' . HTMLHelper::_('ukui.renderTab', 'tab-bottom') . '</div>';
	}

	public function renderField($name, $group = null, $default = null, $options = [])
	{
		if ($field = $this->getField($name, $group, $default))
		{
			return $this->renderOutputField($field, $options);
		}

		return '';
	}

	protected function renderOutputField(FormField $field, $options = [])
	{
		$options['helpText'] = '';
		$description         = $field->description;

		if (!empty($description)
			&& strtolower($field->type) != 'note'
			&& 'true' !== $field->getAttribute('hiddenDescription')
		)
		{
			$options['helpText'] = '<div class="uk-text-small uk-text-muted uk-margin-small-top uk-margin-small-bottom uk-text-italic" id="' . $field->id . '-desc">' . Text::_($description) . '</div>';
		}

		$output = $field->renderField($options);

		if ($this->isMultiLanguage && 'true' === $field->getAttribute('ESMultiLanguage'))
		{
			$output = str_replace($field->__get('input'), $this->renderMultiLanguageInput($field), $output);
		}

		return $output;
	}

	public function renderFieldset($name, $options = array())
	{
		$html = '';

		if ($fields = $this->getFieldset($name))
		{
			foreach ($fields as $field)
			{
				$html .= $this->renderOutputField($field, $options);
			}
		}

		return $html;
	}

	public function filterFieldXml($element, $value)
	{
		return parent::filterField($element, $value);
	}

	public function validateFieldXml(SimpleXMLElement $element, $group = null, $value = null, Registry $input = null)
	{
		return parent::validateField($element, $group, $value, $input);
	}

	public function appendErrors($errors)
	{
		if (!is_array($errors))
		{
			$errors = [$errors];
		}

		foreach ($errors as $error)
		{
			$this->errors[] = $error;
		}

		return $this;
	}
}