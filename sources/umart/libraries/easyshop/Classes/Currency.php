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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use RuntimeException;

class Currency
{
	protected $currencyTable = null;
	protected $context = 'com_easyshop.currency';

	public function __construct()
	{
		$items = $this->getList();

		if ($this->isMultiMode())
		{
			$id = (int) easyshop('app')->input->post->getInt('easyshop_currency_id', 0);

			if ($id > 0 && isset($items[$id]))
			{
				$this->setActiveId($id);
			}
		}
	}

	public function getList()
	{
		static $items = null;

		if (null === $items)
		{
			$db    = easyshop('db');
			$query = $db->getQuery(true)
				->select(
					'a.id, a.symbol, a.code, a.format, a.name, a.state, a.ordering, a.rate, a.point, a.decimals, a.separator, '
					. 'a.is_default, a.checked_out, a.checked_out_time, a.created_date, a.created_by, a.params')
				->from($db->quoteName('#__easyshop_currencies', 'a'))
				->select('u.name AS author')
				->leftJoin($db->quoteName('#__users', 'u') . ' ON u.id = a.created_by')
				->select('uu.name AS editor')
				->leftJoin($db->quoteName('#__users', 'uu') . ' ON uu.id = a.checked_out')
				->where('a.state = 1');
			$db->setQuery($query);
			$items = $db->loadObjectList('id');
		}

		return $items;
	}

	public function isMultiMode()
	{
		return easyshop('config', 'multi_currencies_mode', 0);
	}

	public function setActiveId($pk = 0)
	{
		easyshop('app')->setUserState($this->context . '.active', $pk);
	}

	public function getActive()
	{
		static $active = null;

		if (!is_object($active))
		{
			$active = new Currency;
			$active->load($this->getActiveId());
		}

		return $active;
	}

	public function load($pk)
	{
		$table = $this->getTable();

		if ($table->load($pk))
		{
			$this->currencyTable = $table;
		}
		else
		{
			throw new RuntimeException(Text::_('COM_EASYSHOP_ERROR_CURRENCY_NOT_FOUND'));
		}

		return $this;
	}

	public function getTable()
	{
		if ($this->currencyTable instanceof Table)
		{
			return $this->currencyTable;
		}

		Table::addIncludePath(ES_COMPONENT_ADMINISTRATOR . '/tables');

		return Table::getInstance('Currency', 'EasyshopTable');
	}

	public function getActiveId()
	{
		$defaultId = $this->getDefault()->get('id');

		if ($this->isMultiMode())
		{
			$activeId = easyshop('app')->getUserState($this->context . '.active', $defaultId);
		}
		else
		{
			$activeId = $defaultId;
		}

		return $activeId;
	}

	public function get($property, $default = null)
	{
		return $this->currencyTable->get($property, $default);
	}

	public function getDefault()
	{
		static $defaultCurrency = null;

		if (!is_object($defaultCurrency))
		{
			$defaultCurrency = new Currency;
			$defaultCurrency->load(['is_default' => '1']);
		}

		return $defaultCurrency;
	}

	public function toFormat($numeric, $convert = false)
	{
		static $formats = [];
		$key = '_' . $numeric . (int) $convert;

		if (!isset($formats[$key]))
		{
			if ($convert)
			{
				$numeric = $this->convert($numeric);
			}

			if (!is_object($this->currencyTable)
				|| empty($this->currencyTable->format)
				|| !preg_match('/\{value\}/i', $this->currencyTable->format)
			)
			{
				$formats[$key] = $numeric;
			}
			else
			{
				$code          = $this->currencyTable->get('code');
				$symbol        = $this->currencyTable->get('symbol', $code);
				$format        = $this->currencyTable->get('format', '{symbol}{value}');
				$decimals      = $this->currencyTable->get('decimals', 2);
				$separator     = $this->currencyTable->get('separator', ',');
				$point         = $this->currencyTable->get('point', '.');
				$value         = number_format((float) $numeric, $decimals, $point, $separator);
				$formats[$key] = preg_replace(
					['/\{symbol\}/i', '/\{value\}/i', '/\{code\}/i'],
					[$symbol, $value, $code],
					$format, 1);
			}
		}

		return $formats[$key];
	}

	public function convert($numeric)
	{
		if ($this->isMultiMode())
		{
			$default = $this->getDefault();

			if ((int) $this->get('id') !== (int) $default->get('id'))
			{
				$rate    = (float) $this->get('rate');
				$numeric = $numeric * $rate;
			}
		}

		return $numeric;
	}
}
