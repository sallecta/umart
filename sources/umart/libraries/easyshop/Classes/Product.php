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

use DOMDocument;
use DOMElement;
use EasyshopHelperRoute;
use JLoader;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use stdClass;

JLoader::register('EasyshopHelperRoute', ES_COMPONENT_SITE . '/helpers/route.php');

class Product
{
	public function getItem($pk, $reload = false, $prepare = true)
	{
		static $products = [];
		$pk = (int) $pk;

		if (!isset($products[$pk]) || $reload)
		{
			/**
			 * @var Currency       $currency
			 * @var Cart           $cart
			 * @var Discount       $discountClass
			 * @var stdClass       $product
			 * @var CMSApplication $app
			 */

			$app   = easyshop('app');
			$table = $this->getTable();

			if ($pk < 1 || !$table->load($pk))
			{
				$app->enqueueMessage(Text::sprintf('COM_EASYSHOP_PRODUCT_NOT_FOUND', $pk));

				return false;
			}

			$product = ArrayHelper::toObject($table->getProperties());
			$stock   = (int) $product->stock;
			Translator::translateObject($product, 'easyshop_products', $product->id);
			$product->params        = new Registry($product->params);
			$product->images        = $this->getImages($pk);
			$product->prices        = $this->getPrices($pk);
			$product->weekPriceDays = $this->getWeekPriceDays($pk);
			$product->productTags   = $this->getTags($pk);
			$product->taxes         = $this->getTaxes($product->taxes);
			$product->outOfStock    = $stock !== -1 && $stock < 1;
			$unit                   = $product->dimension_unit;
			$product->dimension     = $product->length . $unit . ' x ' . $product->width . $unit . ' x ' . $product->height . $unit;

			if (empty($product->option_fields))
			{
				$product->option_fields = '{}';
			}

			$registry = new Registry;
			$registry->loadString((string) $product->option_fields);
			$product->option_fields      = $registry->toArray();
			$product->extraDetailDisplay = [];
			$product->extraBlockDisplay  = [];

			// Category
			$product->category = Categories::getInstance('easyshop.product')->get($product->category_id);
			$currency          = easyshop(Currency::class)->getActive();

			if (easyshop('site'))
			{
				if (!empty($product->weekPriceDays))
				{
					$weekDay = (int) HTMLHelper::_('date', 'now', 'w');

					foreach ($product->weekPriceDays as $weekPriceDay)
					{
						if ($weekDay === (int) $weekPriceDay->week_day)
						{
							$weekDayPrice = (float) $weekPriceDay->price;

							if ($weekDayPrice != $product->price)
							{
								$product->oldPriceFormat = $currency->toFormat($product->price, true);
								$product->price          = $weekDayPrice;
							}

							break;
						}
					}
				}

				$product->category->link = Route::_(EasyshopHelperRoute::getCategoryRoute($product->category, $product->category->language), false);
				$product->discount       = 0.00;

				// Brand
				if ($product->brand_id)
				{
					$product->brand       = Categories::getInstance('easyshop.brand')->get($product->brand_id);
					$product->brand->link = Route::_(EasyshopHelperRoute::getSearchRoute(['task' => 'search', 'brand' => $product->brand_id], $product->brand->language), false);
				}

				$product->currency = $currency->get('code');
				$discountClass     = easyshop(Discount::class);
				$discountClass->applyOnProduct($product);
				$product->totalTaxes = $this->getTotalTaxes($product, $product->price);
				$cartItems           = easyshop(Cart::class)->getItems();
				$product->cart       = [
					'quantity' => 1,
					'options'  => [],
				];

				if (isset($cartItems[$pk]))
				{
					$cartItem = array_pop($cartItems[$pk]);

					if (!empty($cartItem))
					{
						unset($cartItem['product']);
						$product->cart = $cartItem;
					}
				}

				$product->taxesFormat = $currency->toFormat($product->totalTaxes, true);

				if (easyshop('config', 'zero_as_free', 0) && (float) $product->price < 0.01)
				{
					$product->priceFormat = Text::_('COM_EASYSHOP_FREE_PRODUCT');
				}
				else
				{
					if (easyshop('config', 'price_include_taxes', 0))
					{
						$product->priceFormat = $currency->toFormat($product->price + $product->totalTaxes, true);
					}
					else
					{
						$product->priceFormat = $currency->toFormat($product->price, true);
					}
				}

				$this->loadCustomFields($product);
				$this->loadOptions($product);

				// Route
				$link          = EasyshopHelperRoute::getProductRoute($product->id, $product->category_id, $product->language);
				$product->link = Route::_($link, false);

				// Params inherit
				$config = easyshop('config');

				foreach ($product->params->toArray() as $name => $value)
				{
					if (trim($value) === '' && $config->exists($name))
					{
						$product->params->set($name, $config->get($name));
					}
				}

				// Max quantity and stock
				$maxQuantity = (int) $product->params->get('product_detail_max_quantity', 0);
				$stock       = (int) $product->stock;

				if ($maxQuantity < 1)
				{
					$maxQuantity = (int) $config->get('product_detail_max_quantity', 0);
				}

				if ($stock !== -1)
				{
					if ($stock < 1)
					{
						$product->params->set('product_detail_add_to_cart', 0);
					}

					$maxQuantity = $stock;
				}

				$product->params->set('product_detail_max_quantity', $maxQuantity);

				// @since 1.1.6
				$product->extraBlockFlexDisplay  = [];
				$product->extraDetailFlexDisplay = [];

				if ($prepare)
				{
					$app->triggerEvent('onEasyshopProductPrepare', [$product]);
				}

				// @since 1.1.0
				$nullDate            = easyshop('db')->getNullDate();
				$product->expireDate = false;

				if (!empty($product->sale_from_date)
					&& $product->sale_from_date != $nullDate
					&& !empty($product->sale_to_date)
					&& $product->sale_to_date != $nullDate
				)
				{
					$nowDate = CMSFactory::getDate('now', 'UTC')->format('Y-m-d H:i:s');

					if ($nowDate >= $product->sale_from_date && $nowDate < $product->sale_to_date)
					{
						$product->expireDate = easyshop(Utility::class)->getDate($product->sale_to_date);
					}
					else
					{
						$product->expireDate = true;
						$product->params->set('product_list_add_to_cart', 0);
						$product->params->set('product_detail_add_to_cart', 0);
					}
				}
			}
			elseif ($prepare)
			{
				$app->triggerEvent('onEasyshopAdminProductPrepare', [$product]);
			}

			$products[$pk] = $product;
		}

		return $products[$pk];
	}

	public function getTable()
	{
		static $table = null;

		if (null === $table)
		{
			Table::addIncludePath(ES_COMPONENT_ADMINISTRATOR . '/tables');
			$table = Table::getInstance('Product', 'EasyshopTable');
		}

		return $table;
	}

	public function getImages($productId = 0)
	{
		$db    = easyshop('db');
		$query = $db->getQuery(true)
			->select('a.file_path, a.mime_type, a.title, a.description, a.ordering')
			->from($db->quoteName('#__easyshop_medias', 'a'))
			->where('a.type = ' . $db->quote('IMAGE') . ' AND a.product_id = ' . (int) $productId)
			->order('a.ordering ASC');
		$db->setQuery($query);

		if ($images = $db->loadObjectList())
		{
			/** @var Media $mediaClass */
			$mediaClass = easyshop(Media::class);

			foreach ($images as &$image)
			{
				foreach ($mediaClass->getFullImages($image->file_path) as $size => $src)
				{
					$image->{$size} = $src;
				}
			}
		}

		return $images;
	}

	public function getPrices($productId = 0)
	{
		$db    = easyshop('db');
		$query = $db->getQuery(true);
		$query->select('p.price_value, p.currency_id, p.min_quantity, p.valid_from_date, p.valid_to_date')
			->from($db->quoteName('#__easyshop_prices', 'p'))
			->order('p.min_quantity')
			->where('p.product_id = ' . (int) $productId);
		$db->setQuery($query);

		if ($prices = $db->loadObjectList())
		{
			return $prices;
		}

		return [];
	}

	public function getWeekPriceDays($productId = 0)
	{
		$db    = easyshop('db');
		$query = $db->getQuery(true);
		$query->select('a.week_day, a.price')
			->from($db->quoteName('#__easyshop_price_days', 'a'))
			->where('a.product_id = ' . (int) $productId);
		$db->setQuery($query);

		return $db->loadObjectList() ?: [];
	}

	public function getTags($productId)
	{
		return easyshop(Tags::class)->getProductTags($productId);
	}

	public function getTaxes($taxes)
	{
		$taxes = explode('][', trim(preg_replace('/^\[|\]$/', '', $taxes)));

		if (count($taxes))
		{
			$taxes = ArrayHelper::toInteger($taxes);
			$db    = easyshop('db');
			$query = $db->getQuery(true)
				->select('t.id, t.name, t.flat, t.type, t.rate, t.vendor_id')
				->from($db->quoteName('#__easyshop_taxes', 't'))
				->where('t.id IN (' . implode(',', $taxes) . ')');
			$db->setQuery($query);

			$taxes = $db->loadObjectList('id');
		}

		return $taxes;
	}

	public function getTotalTaxes($product, $price = null)
	{
		$taxesAmount = 0.00;

		if (count($product->taxes))
		{
			if (null === $price)
			{
				$price = (float) $product->price;
			}

			foreach ($product->taxes as $tax)
			{
				if ((int) $tax->type)
				{
					$rate        = (float) $tax->rate;
					$taxesAmount += (($price * $rate) / 100);
				}
				else
				{
					$taxesAmount += (float) $tax->flat;
				}
			}
		}

		return $taxesAmount;
	}

	protected function loadCustomFields($product)
	{
		/**
		 * @var CustomField $customField
		 * @var Renderer    $layoutHelper
		 */
		$customField  = easyshop(CustomField::class, [
			'reflector'    => 'com_easyshop.product.customfield',
			'reflector_id' => $product->id,
		]);
		$layoutHelper = easyshop('renderer');
		$layoutHelper->refreshDefaultPaths();
		$product->customfields = [];

		foreach ($customField->getGroups() as $group)
		{
			$params  = new Registry((string) $group->params);
			$assigns = (array) $params->get('product_categories', []);

			if (empty($assigns) || in_array($product->category_id, $assigns))
			{
				$fields           = (array) $group;
				$fields['params'] = $params;
				$fields['fields'] = $customField->getItemsByGroup($fields['id'], $product->id);
				$search           = '{fields}' . $fields['alias'] . '{/fields}';

				if (stripos($product->description, $search) !== false)
				{
					$replace              = $layoutHelper->render('product.field.' . $params->get('fields_output_layout', 'table'), [
						'fields' => $fields['fields'],
					]);
					$product->description = str_ireplace($search, $replace, $product->description);
				}
				else
				{
					$product->customfields[] = $fields;
				}
			}
		}
	}

	public function loadOptions($product, $layoutId = 'product.option')
	{
		// Options
		$product->options = '';

		/**
		 * @var CustomField $optionField
		 * @var Renderer    $layoutHelper
		 */

		$optionField = easyshop(CustomField::class, [
			'reflector'    => 'com_easyshop.product.option',
			'reflector_id' => $product->id,
		]);
		$currency    = easyshop(Currency::class)->getActive();
		$xml         = new DOMDocument('1.0', 'UTF-8');
		$form        = $xml->appendChild(new DOMElement('form'));
		$fieldSet    = $form->appendChild(new DOMElement('fieldset'));
		$fieldSet->setAttribute('name', 'option');
		$options = [];

		foreach ($optionField->load() as $groupId => $items)
		{
			$options += $items;
		}

		foreach ($product->option_fields as $fieldId => $fieldValue)
		{
			if (isset($options[$fieldId]))
			{
				// Fix pointer
				$option              = (array) $options[$fieldId];
				$type                = strtolower($option['type']);
				$fieldValue['value'] = @json_decode($fieldValue['value'], true);

				if (!empty($fieldValue['options'])
					&& !empty($fieldValue['value'])
					&& in_array($type, ['list', 'radio', 'colors', 'inline'])
				)
				{
					$params                = new Registry($option['params']);
					$optionParams          = $params->get('options', []);
					$fieldValue['options'] = array_map(function ($opt) {
						return $opt['value'];
					}, $fieldValue['options']);
					$newOptions            = [];

					foreach ($optionParams as $optionParam)
					{

						if (isset($optionParam->value)
							&& isset($fieldValue['value'][$optionParam->value])
							&& $fieldValue['value'][$optionParam->value] === 'disabled'
						)
						{
							continue;
						}

						if (in_array($optionParam->value, $fieldValue['options']))
						{
							$newOptions[] = $optionParam;
						}
					}

					if (empty($newOptions))
					{
						continue;
					}

					$params->set('options', $newOptions);
					$option['params'] = (string) $params->toString();
				}

				$optionField->prepareField($fieldSet, (object) $option, $fieldId);
			}
		}

		Form::addFieldPath(ES_COMPONENT_ADMINISTRATOR . '/models/fields');
		$form     = Form::getInstance('com_easyshop.option.' . $product->id, $xml->saveXML(), ['control' => 'product_option']);
		$renderer = easyshop('renderer');

		foreach ($form->getFieldset('option') as $field)
		{
			$name   = $field->getAttribute('name');
			$value  = $field->getAttribute('default');
			$prefix = '';

			if (isset($product->cart['options'][$name]['value']))
			{
				$value  = $product->cart['options'][$name]['value'];
				$prefix = (float) $product->cart['options'][$name]['prefix'];

				if ($prefix > 0.00 || $prefix < 0.00)
				{
					if ($prefix > 0.00)
					{
						$prefix = '+' . $currency->toFormat(abs($prefix), true);
					}
					else
					{
						$prefix = '-' . $currency->toFormat(abs($prefix), true);
					}
				}
				else
				{
					$prefix = '';
				}
			}

			$field->__set('id', $name . '_' . $product->id);
			$field->__set('value', $value);
			$input = $renderer->render($layoutId, [
				'field'  => $field,
				'prefix' => $prefix,
			]);

			if (strcasecmp($field->getAttribute('type'), 'checkbox') === 0 && empty($value))
			{
				$input = str_replace('checked', '', $input);
			}

			$product->options .= $input;
		}
	}

	public function isEnabled($productId = 0, $checkIsExpired = true)
	{
		$db    = easyshop('db');
		$query = $db->getQuery(true)
			->select('COUNT(a.id)')
			->from($db->quoteName('#__easyshop_products', 'a'))
			->where('a.state = 1 AND a.id = ' . (int) $productId);

		if ($checkIsExpired)
		{
			$nowDate  = $db->quote(CMSFactory::getDate('now', 'UTC')->toSql());
			$nullDate = $db->quote($db->getNullDate());
			$query->where(
				<<<SQL_WHERE
CASE 
	WHEN 
		a.sale_from_date IS NULL 
		OR a.sale_from_date = {$nullDate}
		OR a.sale_to_date IS NULL 
		OR a.sale_to_date = {$nullDate}
		OR ({$nowDate} BETWEEN a.sale_from_date AND a.sale_to_date)
	THEN 1 
	ELSE 0
END
SQL_WHERE
			);
		}

		$db->setQuery($query);

		return $db->loadResult();
	}
}
