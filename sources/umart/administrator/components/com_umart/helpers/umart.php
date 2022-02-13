<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;

class UmartHelper
{
	const PICKER_RANGE_SEPARATOR = ' -> ';
	const PICKER_MULTIPLE_SEPARATOR = ', ';
	public static $extension = 'com_umart';

	public static function filterArrayToString($value)
	{
		$filter = InputFilter::getInstance();

		if (is_array($value) || is_object($value))
		{
			$value = (array) $value;
			$value = array_map(function ($v) {
				return str_replace(['[', ']'], '', trim($v));
			}, $value);

			$value = implode('][', (array) $value);
		}
		elseif (is_string($value))
		{
			$value = trim($value, '[]');
		}

		return '[' . $filter->clean($value) . ']';
	}

	public static function filterMediaImage($value)
	{
		return self::filterMedia($value, 'images');
	}

	public static function filterMedia($value, $type, $userId = 0)
	{
		$value = trim($value);

		if (empty($value))
		{
			return $value;
		}

		/** @var $app \JApplicationCms */
		$value = JPath::clean($value, '/');
		$app   = plg_sytem_umart_main('app');
		$base  = self::getMediaUserBasePath($type, $userId);

		$file = UMART_MEDIA . '/' . $base . '/' . $value;

		if (is_file($file))
		{
			$value = $base . '/' . $value;
		}
		elseif (!empty($value))
		{
			$app->enqueueMessage(Text::sprintf('COM_UMART_MEDIA_FILE_NO_EXISTS', $value), 'warning');
			$value = '';
		}

		return $value;
	}

	public static function getMediaUserBasePath($type, $userId = 0)
	{
		if ($userId < 1)
		{
			$userId = (int) JFactory::getUser()->id;
		}

		if (plg_sytem_umart_main('site'))
		{
			$base = 'assets/' . $type . '/user_customers/' . $userId;
		}
		else
		{
			$base = 'assets/' . $type;
		}

		return $base;
	}

	public static function filterMediaFile($value)
	{
		return self::filterMedia($value, 'files');
	}

	public static function filterOption($options)
	{
		$data = [];

		if (!empty($options->value) && !empty($options->text))
		{
			foreach ($options->value as $key => $value)
			{
				$value = trim($value);
				$text  = trim($options->text[$key]);

				if (strlen($value) === 0 && strlen($text) === 0)
				{
					continue;
				}

				$data[] = [
					'value' => $value,
					'text'  => $text,
				];
			}
		}

		return $data;
	}

	public static function getLanguageList()
	{
		static $languages = null;

		if (null === $languages)
		{
			$db    = plg_sytem_umart_main('db');
			$query = $db->getQuery(true)
				->select('a.*')
				->from($db->quoteName('#__languages', 'a'))
				->order('a.ordering ASC');
			$db->setQuery($query);

			$languages = $db->loadObjectList('lang_code');
		}

		return $languages;
	}

	public static function getAllLanguagesFiles($tag = 'en-GB')
	{
		static $allFiles = [];

		if (!isset($allFiles[$tag]))
		{
			$allFiles[$tag] = [];

			if (is_dir(UMART_COMPONENT_ADMINISTRATOR . '/language/' . $tag))
			{
				if ($files = Folder::files(UMART_COMPONENT_ADMINISTRATOR . '/language/' . $tag, '\.ini$', false, true))
				{
					$allFiles[$tag] = array_merge($allFiles[$tag], $files);
				}
			}

			$paths = Folder::folders(JPATH_ROOT . '/modules', '^mod_umart', false, true);
			$paths = array_merge(Folder::folders(JPATH_ADMINISTRATOR . '/modules', '^mod_umart', false, true), $paths);

			if (is_dir(JPATH_PLUGINS . '/umart'))
			{
				$paths = array_merge(Folder::folders(JPATH_ROOT . '/plugins/umart', '[a-zA-Z_0-9]', false, true), $paths);
			}

			// if (is_dir(JPATH_PLUGINS . '/umart_payment'))
			// {
				// $paths = array_merge(Folder::folders(JPATH_ROOT . '/plugins/umart_payment', '[a-zA-Z_0-9]', false, true), $paths);
			// }

			// if (is_dir(JPATH_PLUGINS . '/umartshipping'))
			// {
				// $paths = array_merge(Folder::folders(JPATH_ROOT . '/plugins/umartshipping', '[a-zA-Z_0-9]', false, true), $paths);
			// }

			if (is_dir(JPATH_PLUGINS . '/editors-xtd/umart_image'))
			{
				$paths[] = JPATH_PLUGINS . '/editors-xtd/umart_image';
			}

			if (!empty($paths))
			{
				foreach ($paths as $path)
				{
					if (is_dir($path . '/language/' . $tag))
					{
						if ($files = Folder::files($path . '/language/' . $tag, '\.ini$', false, true))
						{
							$allFiles[$tag] = array_merge($allFiles[$tag], $files);
						}
					}
				}
			}
		}

		return $allFiles[$tag];
	}

	public static function filterAlias($value)
	{
		return ApplicationHelper::stringURLSafe($value);
	}

	public static function filterDateSingle($value)
	{
		return static::filterDate($value, 'single');
	}

	public static function filterDate($value, $mode = 'single')
	{
		$nullDate = CMSFactory::getDbo()->getNullDate();

		if (!empty($value) && $value !== $nullDate)
		{
			$timezone  = CMSFactory::getUser()->getTimezone();
			$delimiter = null;

			if ('single' !== $mode)
			{
				$delimiter = $mode === 'range' ? UmartHelper::PICKER_RANGE_SEPARATOR : UmartHelper::PICKER_MULTIPLE_SEPARATOR;
				$dates     = [];

				foreach (array_map('trim', explode($delimiter, $value)) as $date)
				{
					if (!empty($date) && $date !== $nullDate)
					{
						$dates[] = CMSFactory::getDate($date, $timezone)->toSql();
					}
				}

				return implode($delimiter, array_unique($dates));
			}

			return CMSFactory::getDate($value, $timezone)->toSql();
		}

		return '';
	}

	public static function filterDateMultiple($value)
	{
		return static::filterDate($value, 'multiple');
	}

	public static function filterDateRange($value)
	{
		return static::filterDate($value, 'range');
	}
}
