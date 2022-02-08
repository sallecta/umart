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

use EasyshopHelper;
use Exception;
use JHttpFactory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use RuntimeException;

class Utility
{
	public function getZoneName($zoneId, $englishName = true)
	{
		static $zones = null;

		if (null === $zones)
		{
			$db    = easyshop('db');
			$query = $db->getQuery(true)
				->select('a.id, a.name, a.name_english')
				->from($db->quoteName('#__easyshop_zones', 'a'));
			$db->setQuery($query);
			$zones = $db->loadObjectList('id');
		}

		if (!isset($zones[$zoneId]))
		{
			return false;
		}

		return $englishName ? $zones[$zoneId]->name_english : $zones[$zoneId]->name;
	}

	public function getMethodName($methodId)
	{
		static $plugins = null;

		if (null === $plugins)
		{
			$db    = easyshop('db');
			$query = $db->getQuery(true)
				->select('a.id, a.name')
				->from($db->quoteName('#__easyshop_methods', 'a'));
			$db->setQuery($query);
			$plugins = $db->loadObjectList('id');
		}

		return isset($plugins[$methodId]) ? $plugins[$methodId]->name : false;
	}

	public function serializeToArrayData(array $inputs = [])
	{
		$registry = new Registry;

		foreach ($inputs as $input)
		{
			$name = preg_replace('/jform\[|\]$/i', '', $input['name']);

			$registry->set(str_replace('][', '.', $name), $input['value']);
		}

		return $registry->toArray();
	}

	public function formatAddress($fieldData)
	{
		$defaultFormat = '{user_name} {user_address}, {user_postcode}, {user_city}, {user_state}, {user_country}';
		$addressFormat = easyshop('config', 'address_format', $defaultFormat);

		foreach ($fieldData as $field)
		{
			$addressFormat = str_replace('{' . $field->field_name . '}', $field->display, $addressFormat);
		}

		// @since 1.1.5. Clean up format
		$addressFormat = preg_replace('/\{user_[0-9a-z_]+\}/i', '', $addressFormat);
		$addressFormat = preg_replace(['/,\s*,/', '/;\s*/', '/\|\s*\|/'], [',', ';', '|'], $addressFormat);

		return $addressFormat;
	}

	public function userAccess($accessGroups)
	{
		$user = CMSFactory::getUser();

		foreach ($accessGroups as $group)
		{
			if (in_array($group, $user->getAuthorisedGroups()))
			{
				return true;
			}
		}

		return false;
	}

	public function parseOrderingData($sort, &$ordering, &$direction)
	{
		switch (strtolower($sort))
		{
			case 'recent':
				$ordering  = 'a.created_date';
				$direction = 'desc';
				break;

			case 'hits':
				$ordering  = 'a.hits';
				$direction = 'desc';
				break;

			case 'name_asc':
				$ordering = 'a.name';
				break;

			case 'name_desc':
				$ordering  = 'a.name';
				$direction = 'desc';
				break;

			case 'price_asc':
				$ordering  = 'a.price';
				$direction = 'asc';
				break;

			case 'price_desc':
				$ordering  = 'a.price';
				$direction = 'desc';
				break;

			default:
				$ordering  = 'a.ordering';
				$direction = 'asc';
				break;
		}
	}

	public function getClientIp()
	{
		if (isset($_SERVER['HTTP_CLIENT_IP']))
		{
			return $_SERVER['HTTP_CLIENT_IP'];
		}

		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}

		return $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * @param $reload boolean
	 *
	 * @return array
	 * @since 1.1.0
	 */
	public function findUpdate($reload = false)
	{
		$app    = easyshop('app');
		$result = $app->getUserState('com_easyshop.update', null);

		if ($reload || !is_array($result))
		{
			$result = [];
			$file   = ES_COMPONENT_ADMINISTRATOR . '/easyshop.xml';

			if (is_file($file)
				&& ($xml = simplexml_load_file($file))
				&& !empty($xml->updateservers->server)
			)
			{
				$server = trim(str_replace('&amp;', '&', (string) $xml->updateservers->server));

				try
				{
					$http     = JHttpFactory::getHttp();
					$response = $http->get($server);
				}
				catch (RuntimeException $e)
				{
					$response = null;
				}

				if ($response !== null
					&& $response->code === 200
					&& ($update = simplexml_load_string($response->body))
					&& !empty($update->update[0]->version)
					&& !empty($update->update[0]->downloads->downloadurl)
				)
				{
					$version     = trim((string) $update->update[0]->version);
					$downloadUrl = trim((string) $update->update[0]->downloads->downloadurl);

					if (version_compare($version, ES_VERSION, 'gt'))
					{
						$result = [
							'version'     => $version,
							'downloadUrl' => $downloadUrl,
						];
					}
				}
			}

			$app->setUserState('com_easyshop.update', $result);
		}

		return $result;
	}

	/**
	 * Get shop information
	 *
	 * @param integer $vendorId
	 *
	 * @return array
	 * @since 1.1.3
	 */
	public function getShopInformation($vendorId = 0)
	{
		$config      = easyshop('config');
		$information = [];
		$fieldsMap   = [
			'shop_name'           => 'shopName',
			'shop_logo'           => 'shopLogo',
			'shop_website'        => 'shopWebsite',
			'shop_address_1'      => 'shopAddress1',
			'shop_address_2'      => 'shopAddress2',
			'shop_city'           => 'shopCity',
			'shop_postcode'       => 'shopPostcode',
			'shop_email'          => 'shopEmail',
			'shop_telephone'      => 'shopTelephone',
			'shop_mobile'         => 'shopMobile',
			'shop_fax'            => 'shopFax',
			'shop_country'        => 'shopCountry',
			'shop_address_format' => 'shopAddressFormat',
			'shop_description'    => 'shopDescription',
		];

		easyshop('app')->triggerEvent('onEasyshopPrepareShopInformation', [$vendorId, $fieldsMap, &$information]);

		if (empty($information))
		{
			foreach ($fieldsMap as $name => $map)
			{
				$information[$map] = $config->get($name);
			}
		}

		if ($information['shopCountry']
			&& is_numeric($information['shopCountry'])
			&& ($zoneTable = easyshop(Zone::class)->load($information['shopCountry']))
		)
		{
			$information['shopCountryId'] = $zoneTable->id;
			$information['shopCountry']   = $zoneTable->name;
			$information['shopCountryEn'] = $zoneTable->name_english;
		}

		if (!empty($information['shopAddressFormat']))
		{
			$hasFormattedData = false;

			foreach ($fieldsMap as $name => $map)
			{
				if ($map != 'shopAddressFormat'
					&& strpos($information['shopAddressFormat'], '{' . $name . '}') !== false
				)
				{
					$trimValue = trim($information[$map]);

					if (!empty($trimValue))
					{
						$hasFormattedData = true;
					}

					$information['shopAddressFormat'] = str_replace('{' . $name . '}', $trimValue, $information['shopAddressFormat']);
				}
			}

			if ($hasFormattedData)
			{
				$information['shopAddressFormat'] = preg_replace(['/,\s*,/', '/;\s*/', '/\|\s*\|/'], [',', ';', '|'], $information['shopAddressFormat']);
			}
			else
			{
				$information['shopAddressFormat'] = '';
			}
		}

		return $information;
	}

	/**
	 * @return array
	 * @since 1.1.4
	 */
	public function getOrderingData()
	{
		static $sortArray = null;

		if (!is_array($sortArray))
		{
			$sortArray = [
				[
					'value' => 'ordering',
					'text'  => Text::_('COM_EASYSHOP_SORT_BY'),
				],
				[
					'value' => 'recent',
					'text'  => Text::_('COM_EASYSHOP_SORT_RECENT'),
				],
				[
					'value' => 'name_asc',
					'text'  => Text::_('COM_EASYSHOP_SORT_NAME_ASC'),
				],
				[
					'value' => 'name_desc',
					'text'  => Text::_('COM_EASYSHOP_SORT_NAME_DESC'),
				],
				[
					'value' => 'price_asc',
					'text'  => Text::_('COM_EASYSHOP_SORT_PRICE_ASC'),
				],
				[
					'value' => 'price_desc',
					'text'  => Text::_('COM_EASYSHOP_SORT_PRICE_DESC'),
				],
				[
					'value' => 'hits',
					'text'  => Text::_('COM_EASYSHOP_TOP_HITS'),
				],
			];

			easyshop('app')->triggerEvent('onEasyshopPrepareOrderingData', [&$sortArray]);
		}

		return $sortArray;
	}

	/**
	 * @param Registry $config
	 * @param Boolean  $childWidth
	 *
	 * @return string
	 * @since 1.1.6
	 */
	public function parseColumnClassSizes(Registry $config, $childWidth = false)
	{
		$mColumns  = $config->get('product_list_columns', 3);
		$xsColumns = $config->get('product_list_columns_xs', '');
		$sColumns  = $config->get('product_list_columns_s', '');
		$lColumns  = $config->get('product_list_columns_l', '');
		$xlColumns = $config->get('product_list_columns_xl', '');

		$prepareSize = function ($size) {
			$size = (int) $size;

			if (!in_array($size, [1, 2, 3, 4, 5, 6, 10]))
			{
				$size = 3;
			}

			return $size;
		};

		$baseWidth = 'uk-' . ($childWidth ? 'child-width-1-' : 'width-1-');
		$className = $baseWidth . (is_numeric($xsColumns) ? $prepareSize($xsColumns) : '1');

		foreach ([
			         's'  => $sColumns,
			         'm'  => $mColumns,
			         'l'  => $lColumns,
			         'xl' => $xlColumns
		         ] as $screen => $size)
		{
			if (is_numeric($size))
			{
				$className .= ' ' . $baseWidth . $prepareSize($size) . '@' . $screen;
			}
		}

		return $className;
	}

	/**
	 * @param string $countryCode2
	 *
	 * @return string Emoji flag or an empty string ('')
	 * @since 1.1.6
	 */

	public function getCountryFlagEmoji($countryCode2)
	{
		$strEmojiUnicode = '';

		if (!function_exists('mb_convert_encoding'))
		{
			return $strEmojiUnicode;
		}

		foreach (str_split(strtoupper($countryCode2)) as $char)
		{
			$stringRegional  = ord($char) + 127397;
			$strEmojiUnicode .= mb_convert_encoding('&#' . $stringRegional . ';', 'UTF-8', 'HTML-ENTITIES');
		}

		return $strEmojiUnicode;
	}

	public function convertPHPToMomentFormat($format)
	{
		$replacements = [
			'd' => 'DD',
			'D' => 'ddd',
			'j' => 'D',
			'l' => 'dddd',
			'N' => 'E',
			'S' => 'o',
			'w' => 'e',
			'z' => 'DDD',
			'W' => 'W',
			'F' => 'MMMM',
			'm' => 'MM',
			'M' => 'MMM',
			'n' => 'M',
			't' => '', // no equivalent
			'L' => '', // no equivalent
			'o' => 'YYYY',
			'Y' => 'YYYY',
			'y' => 'YY',
			'a' => 'a',
			'A' => 'A',
			'B' => '', // no equivalent
			'g' => 'h',
			'G' => 'H',
			'h' => 'hh',
			'H' => 'HH',
			'i' => 'mm',
			's' => 'ss',
			'u' => 'SSS',
			'e' => 'zz', // deprecated since version 1.6.0 of moment.js
			'I' => '', // no equivalent
			'O' => '', // no equivalent
			'P' => '', // no equivalent
			'T' => '', // no equivalent
			'Z' => '', // no equivalent
			'c' => '', // no equivalent
			'r' => '', // no equivalent
			'U' => 'X',
		];

		return strtr($format, $replacements);
	}

	public function convertPHPToJSDateTimeFormat($phpFormat, $reverse = false)
	{
		// PHP
		$search = [
			// Date format
			'd', // Day of the month, 2 digits with leading zeros. Eg: 01 to 31
			'j', // Day of the month without leading zeros. Eg: 0 to 31
			'z', // The day of the year (starting from 0). Eg: 0 through 365
			'l', // A full textual representation of the day of the week. Eg: Sunday through Saturday
			//'D', // A textual representation of a day, three letters. Eg: Mon through Sun
			'm', // Numeric representation of a month, with leading zeros. Eg: 01 through 12
			'n', // Numeric representation of a month, without leading zeros. Eg: 1 through 12
			'F', // A full textual representation of a month, such as January or March. Eg: January through December
			'M', // A short textual representation of a month, three letters. Eg: Jan through Dec
			'Y', // A full numeric representation of a year, 4 digits. Eg: 1999 or 2003
			//'y', // A two digit representation of a year. Eg: 99 or 03

			// Time format
			'H', // 24-hour format of an hour with leading zeros. Eg: 00 through 23
			'G', // 24-hour format of an hour without leading zeros. Eg: 0 through 23
			'h', // 12-hour format of an hour with leading zeros. Eg: 01 through 12
			'g', // 12-hour format of an hour without leading zeros. Eg: 1 through 12
			'i', // Minutes with leading zeros. Eg: 00 to 59
			's', // Seconds, with leading zeros. Eg: 00 through 59
			'a', // Lowercase Ante meridiem and Post meridiem. Eg: am or pm
			'A', // Uppercase Ante meridiem and Post meridiem. Eg: AM or PM
		];

		// UI JS
		$replace = [
			// Date format
			'dd', // day of month (two digit)
			'd', // day of month (no leading zero)
			'oo', // day of the year (three digit)
			'DD', // day name long
			//'D', // day name short
			'mm', // month of year (two digit)
			'm', // month of year (no leading zero)
			'MM', // month name long
			'M', // month name short
			'yy', // year (four digit)
			//'y', // year (two digit)

			// Time format
			'HH', // Hour with leading 0 (24 hour)
			'H', // Hour with no leading 0 (24 hour)
			'hh', // Hour with leading 0 (12 hour)
			'h', // Hour with no leading 0 (12 hour)
			'mm', // Minute with leading 0
			'ss', // Second with leading 0
			'tt', // am or pm for AM/PM
			'TT', // AM or PM for AM/PM
		];

		if ($reverse)
		{
			return str_replace($replace, $search, $phpFormat);
		}

		return str_replace($search, $replace, $phpFormat);
	}

	public function convertRelativeToAbsoluteUrl($buffer)
	{
		$httpFullUrl = Uri::root();
		$httpPathUrl = Uri::root(true) . '/';
		preg_match_all('#href="([^"]+)"#m', $buffer, $matches);

		foreach ($matches[0] as $i => $match)
		{
			$href = trim($matches[1][$i]);

			if (!empty($href)
				&& strpos($href, '#') !== 0
				&& stripos($href, 'javascript') !== 0
				&& strpos($href, $httpFullUrl) !== 0
			)
			{
				if (strpos($href, $httpPathUrl) === 0)
				{
					$buffer = str_replace($match, 'href="' . preg_replace('#' . preg_quote($httpPathUrl, '#') . '#', $httpFullUrl, $href, 1) . '"', $buffer);
				}
				elseif (strpos($href, 'http') !== 0)
				{
					$buffer = str_replace($match, 'href="' . $httpFullUrl . (ltrim($href, '/')) . '"', $buffer);
				}
			}
		}

		preg_match_all('#src="([^"]+)"#m', $buffer, $matches);

		foreach ($matches[0] as $i => $match)
		{
			$src = trim($matches[1][$i]);

			if (!empty($src)
				&& strpos($src, $httpFullUrl) !== 0
				&& strpos($src, 'data:image') !== 0
			)
			{
				if (strpos($src, $httpPathUrl) === 0)
				{
					$buffer = str_replace($match, 'src="' . preg_replace('#' . preg_quote($httpPathUrl, '#') . '#', $httpFullUrl, $src, 1) . '"', $buffer);
				}
				elseif (strpos($src, 'http') !== 0)
				{
					$buffer = str_replace($match, 'src="' . $httpFullUrl . (ltrim($src, '/')) . '"', $buffer);
				}
			}
		}

		return $buffer;
	}

	public function displayPicker($value, $options = [])
	{
		$options = array_merge(
			[
				'mode'     => 'single',
				'showTime' => true,
			],
			$options
		);

		if ('single' === $options['mode'])
		{
			try
			{
				return $this->displayDate($value, $options['showTime']);
			}
			catch (Exception $e)
			{
				return '';
			}
		}

		$delimiter = 'range' === $options['mode'] ? EasyshopHelper::PICKER_RANGE_SEPARATOR : EasyshopHelper::PICKER_MULTIPLE_SEPARATOR;
		$dates     = [];

		foreach (explode($delimiter, $value) as $date)
		{
			try
			{
				$dates[] = $this->displayDate($date, $options['showTime']);
			}
			catch (Exception $e)
			{

			}
		}

		return implode($delimiter, $dates);
	}

	public function displayDate($date = 'now', $time = true, $relative = false)
	{
		$config = easyshop('getConfig');
		$format = $config->get('php_date_format', 'Y-m-d');

		if ($time)
		{
			$format .= ' ' . $config->get('php_time_format', 'H:i:s');
		}

		if ($relative)
		{
			return HTMLHelper::_('date.relative', $date, null, null, $format);
		}

		if ($date instanceof Date)
		{
			return $date->format($format, true);
		}

		return $this->getDate($date)->format($format, true);
	}

	public function getDate($date = 'now', $userOffset = true)
	{
		$date = CMSFactory::getDate($date, 'UTC');

		if ($userOffset)
		{
			$date->setTimezone(CMSFactory::getUser()->getTimezone());
		}

		return $date;
	}
}
