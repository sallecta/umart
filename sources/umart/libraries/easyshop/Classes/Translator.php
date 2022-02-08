<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ES\Classes;
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Factory as CMSFactory;

use ES\Form\Form;
use Exception;

class Translator
{
	public static function isTranslatable($refresh = false)
	{
		static $translatable = null;

		if (null === $translatable || $refresh)
		{
			$translatable = Multilanguage::isEnabled()
				&& easyshop('site')
				&& CMSFactory::getLanguage()->getTag() !== ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}

		return $translatable;
	}

	public static function getTranslationsData($resTable, $resKey, $langCode = null)
	{
		static $translationsData = [];

		if (null === $langCode)
		{
			$langCode = CMSFactory::getLanguage()->getTag();
		}

		$key = $resTable . ':' . $resKey . ':' . $langCode;

		if (!array_key_exists($key, $translationsData))
		{
			$db      = CMSFactory::getDbo();
			$query   = $db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__easyshop_translations', 'a'))
				->where('a.translationId LIKE ' . $db->quote($langCode . '.' . $resTable . '.' . $resKey . '.%'));
			$results = [];

			if ($rows = $db->setQuery($query)->loadObjectList())
			{
				foreach ($rows as $row)
				{
					$parts              = explode('.', $row->translationId, 4);
					$results[$parts[3]] = $row->translatedValue;
				}
			}

			$translationsData[$key] = $results;
		}

		return $translationsData[$key];
	}

	public static function translateObject($data, $resTable, $resKey)
	{
		if (!is_object($data)
			|| !static::isTranslatable()
			|| !($tranData = static::getTranslationsData($resTable, $resKey))
		)
		{
			return;
		}

		foreach ($tranData as $name => $value)
		{
			if (isset($data->{$name}))
			{
				$data->{$name} = $value;
			}
		}
	}

	public static function getCategoryForm()
	{
		$form = new Form('com_easyshop.translator.category', ['control' => 'jform']);
		$form->loadFile(ES_COMPONENT_ADMINISTRATOR . '/models/forms/category/translator.xml');

		return $form;
	}

	public static function validateTranslationsData($form, $group = null, array $translationsData = null)
	{
		$app    = easyshop('app');
		$result = [];

		if (null === $translationsData)
		{
			$translationsData = $app->input->post->get('ESTranslations', [], 'array');
		}

		if ($app->input->getMethod() === 'POST'
			&& !empty($translationsData)
			&& ($form instanceof Form)
			&& count(($languages = array_keys($form->getLanguagesList(true)))) > 0
			&& ($fields = $form->getXml()->xpath('//field[@ESMultiLanguage="true"]'))
		)
		{
			$input  = new Registry($translationsData);
			$output = new Registry;

			foreach ($fields as $field)
			{
				$newField = clone $field;
				$name     = (string) $newField['name'];
				unset($newField['required']);

				foreach ($languages as $langCode)
				{
					$key = $langCode . '.' . $name;

					if ($input->exists($key)
						&& (null !== ($value = $form->filterFieldXml($newField, $input->get($key, null))))
					)
					{
						$output->set($key, $value);
						$valid = $form->validateFieldXml($newField, $group, $value, $input);

						// Check for an error.
						if ($valid instanceof Exception)
						{
							$form->appendErrors([$valid->getMessage()]);
							$result = false;
						}
					}
				}
			}

			if (false !== $result)
			{
				$result = $output->toArray();
			}
		}

		return $result;
	}

	public static function saveTranslations($refTable, $refKey, array $requestData = [])
	{
		if (empty($requestData['ESTranslations'])
			|| !Multilanguage::isEnabled()
		)
		{
			return;
		}

		$db           = easyshop('db');
		$languages    = LanguageHelper::getLanguages('lang_code');
		$insertValues = [];

		foreach ($requestData['ESTranslations'] as $langCode => $translations)
		{
			if (isset($languages[$langCode]))
			{
				// Clear current data
				$query = $db->getQuery(true)
					->delete($db->quoteName('#__easyshop_translations'))
					->where($db->quoteName('translationId') . ' LIKE ' . $db->quote($langCode . '.' . $refTable . '.' . $refKey . '.%'));
				$db->setQuery($query)
					->execute();

				foreach ($translations as $name => $value)
				{
					if (!empty($value)
						&& isset($requestData[$name])
						&& $requestData[$name] !== $value
					)
					{
						$translationId  = $langCode . '.' . $refTable . '.' . $refKey . '.' . $name;
						$insertValues[] = $db->quote($translationId) . ',' . $db->quote($requestData[$name]) . ',' . $db->quote($value);
					}
				}
			}
		}

		if ($insertValues)
		{
			$query = $db->getQuery(true)
				->insert($db->quoteName('#__easyshop_translations'))
				->columns($db->quoteName(['translationId', 'originalValue', 'translatedValue']))
				->values(array_unique($insertValues));
			$db->setQuery($query)
				->execute();
		}
	}
}