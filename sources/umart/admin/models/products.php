<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use ES\Classes\Product;
use ES\Classes\Translator;
use ES\Model\ListModel;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;

class EasyshopModelProducts extends ListModel
{
	protected $searchField = 'name';
	protected $searchMetaData = true;

	public function __construct(array $config)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = [
				'id', 'a.id',
				'category_id', 'a.category_id',
				'sku', 'a.sku',
				'name', 'a.name',
				'state', 'a.state',
				'stock', 'a.stock',
				'ordering', 'a.ordering',
				'hits', 'a.hit',
				'created_date', 'a.created_date',
				'modified', 'a.modified',
				'price', 'a.price',
				'hits', 'a.hits',
				'language', 'a.language',
				'vendor_id', 'a.vendor_id',
				'approved', 'a.approved',
				'published', 'category_name',
			];
		}
		parent::__construct($config);
	}

	public function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.category_id');
		$id .= ':' . $this->getState('filter.include_sub_categories');
		$id .= ':' . $this->getState('filter.brand_id');
		$id .= ':' . $this->getState('filter.language');
		$id .= ':' . $this->getState('filter.prices');
		$id .= ':' . $this->getState('filter.vendor_id');
		$id .= ':' . $this->getState('filter.approved');
		$id .= ':' . serialize($this->getState('filter.type'));
		$id .= ':' . serialize($this->getState('filter.tags'));

		return parent::getStoreId($id);
	}

	public function getItems()
	{
		$products = [];

		if ($items = parent::getItems())
		{
			/** @var $product Product */
			$product = easyshop(Product::class);

			foreach ($items as $item)
			{
				$fields = (array) $item;
				$item   = $product->getItem($item->id);

				foreach ($fields as $name => $value)
				{
					if (!isset($item->{$name}))
					{
						$item->{$name} = $value;
					}
				}

				$products[] = $item;
			}
		}

		return $products;
	}

	protected function populateState($ordering = null, $direction = null)
	{
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$categoryId = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id', '');
		$this->setState('filter.category_id', $categoryId);

		$brandId = $this->getUserStateFromRequest($this->context . '.filter.brand_id', 'filter_brand_id', '');
		$this->setState('filter.brand_id', $brandId);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		$prices = $this->getUserStateFromRequest($this->context . '.filter.prices', 'filter_prices', '');
		$this->setState('filter.prices', $prices);

		$tags = $this->getUserStateFromRequest($this->context . '.filter.tags', 'filter_tags', [], 'array');
		$this->setState('filter.tags', $tags);

		$vendorId = $this->getUserStateFromRequest($this->context . '.filter.vendor_id', 'filter_vendor_id', '');
		$this->setState('filter.vendor_id', $vendorId);

		$approved = $this->getUserStateFromRequest($this->context . '.filter.approved', 'filter_approved', '');
		$this->setState('filter.approved', $approved);

		$type = $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type', '');
		$this->setState('filter.type', $type);

		parent::populateState('a.id', 'desc');
	}

	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = parent::getListQuery();
		$query->select(
			$this->getState('list.select',
				'DISTINCT a.id, a.language, a.category_id, a.ordering, a.price, a.created_date, a.hits, u.name AS author, '
				. 'uu.name AS editor, l.title AS language_title, l.image AS language_image, c.title AS category_name'
			)
		)
			->from($db->quoteName('#__easyshop_products', 'a'))
			->join('LEFT', $db->quoteName('#__easyshop_price_days', 'a2') . ' ON a2.product_id = a.id AND a2.week_day = ' . (int) CMSFactory::getDate()->format('w'))
			->join('LEFT', $db->quoteName('#__users', 'u') . ' ON u.id = a.created_by')
			->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON c.id = a.category_id')
			->join('LEFT', $db->quoteName('#__users', 'uu') . ' ON uu.id = a.checked_out')
			->join('LEFT', $db->quoteName('#__languages', 'l') . ' ON l.lang_code = a.language');
		$nowDate  = $db->quote(CMSFactory::getDate('now', 'UTC')->toSql());
		$nullDate = $db->quote($db->getNullDate());
		$query->select(
			<<<SQL_SELECT
(
	CASE 
		WHEN 
			a.sale_from_date IS NULL
			OR a.sale_from_date = {$nullDate}
			OR a.sale_to_date IS NULL
			OR a.sale_to_date = {$nullDate}
			OR ({$nowDate} BETWEEN a.sale_from_date AND a.sale_to_date)
		THEN 0 
		ELSE 1
	END
) AS expired
SQL_SELECT
		);
		$categoryId           = $this->getState('filter.category_id');
		$includeSubCategories = $this->getState('filter.include_sub_categories');

		if (is_numeric($categoryId))
		{
			if (!is_numeric($includeSubCategories) || (int) $includeSubCategories > 0)
			{
				$categoryTable = Table::getInstance('Category', 'JTable');
				$categoryTable->load($categoryId);
				$rgt = $categoryTable->rgt;
				$lft = $categoryTable->lft;
				$query->where('c.lft >= ' . (int) $lft)
					->where('c.rgt <= ' . (int) $rgt);
			}
			else
			{
				$query->where('a.category_id = ' . (int) $categoryId);
			}
		}

		$brandId = $this->getState('filter.brand_id');

		if (is_numeric($brandId))
		{
			$query->where('a.brand_id = ' . (int) $brandId);
		}

		$language = $this->getState('filter.language');
		$langTag  = CMSFactory::getLanguage()->getTag();
		$view     = easyshop('app')->input->get('view');

		if (easyshop('site') && $view !== 'products')
		{
			$query->where('a.display_in_search = 1 AND a.approved = 1');

			if (easyshop('config', 'hide_product_off_sale', 0))
			{
				$query->having('expired = 0');
			}

			if ($language)
			{
				$query->where('a.language in (' . $db->quote($langTag) . ',' . $db->quote('*') . ')');
			}

			$prices = $this->getState('filter.prices');

			if (!empty($prices) && strpos($prices, '-') !== false)
			{
				$parts = explode('-', $prices, 2);
				$min   = (float) $parts[0];
				$max   = (float) $parts[1];

				if ($min > 0.00 && $max < 0.01)
				{
					$query->where('(a.price > ' . $min . ' OR a2.price > ' . $min . ')');
				}
				else
				{
					$query->where('((a.price BETWEEN ' . $min . ' AND ' . $max . ') OR (a2.price BETWEEN ' . $min . ' AND ' . $max . '))');
				}
			}
		}
		elseif ($language)
		{
			$query->where('a.language = ' . $db->quote($language));
		}

		$tags = $this->getState('filter.tags');

		if (!empty($tags))
		{
			if (!is_array($tags) && strpos($tags, '|') !== false)
			{
				$tags = explode('|', $tags);
			}
			else
			{
				$tags = [$tags];
			}

			$filter = InputFilter::getInstance();
			$tags   = array_map(function ($tag) use ($db, $filter) {
				return $db->quote($filter->clean($tag));
			}, $tags);

			if (Translator::isTranslatable())
			{
				$transQuery = $db->getQuery(true)
					->select('a.originalValue')
					->from($db->quoteName('#__easyshop_translations', 'a'))
					->where('a.translationId LIKE ' . $db->quote($langTag . '.easyshop_tags.%.alias'))
					->where('a.translatedValue IN (' . implode(',', $tags) . ')');

				if ($translatedTags = $db->setQuery($transQuery)->loadColumn())
				{
					$translatedTags = array_map(function ($tag) use ($db, $filter) {
						return $db->quote($filter->clean($tag));
					}, $translatedTags);
					$tags           = array_merge($tags, $translatedTags);
				}
			}

			$query->join('INNER', $db->quoteName('#__easyshop_tag_items', 'tagItem') . ' ON tagItem.item_id = a.id')
				->join('INNER', $db->quoteName('#__easyshop_tags', 'tag') . ' ON tag.id = tagItem.tag_id')
				->where('tag.context = ' . $db->quote('com_easyshop.product'))
				->where('tag.alias IN (' . implode(',', array_unique($tags)) . ')');
		}

		$vendorId = $this->getState('filter.vendor_id');

		if (is_numeric($vendorId))
		{
			$query->where('a.vendor_id = ' . (int) $vendorId);
		}

		$approved = $this->getState('filter.approved');

		if (is_numeric($approved))
		{
			$query->where('a.approved = ' . (int) $approved);
		}

		$type = $this->getState('filter.type');

		if (!empty($type))
		{
			settype($type, 'array');
			$inTypes = [];

			foreach (ArrayHelper::arrayUnique($type) as $t)
			{
				if (!empty($t))
				{
					$inTypes[] = $db->quote($t);
				}
			}

			if ($inTypes)
			{
				$query->where('a.type IN (' . implode(',', $inTypes) . ')');
			}
		}

		$this->standardFilter($db, $query);

		return $query;

	}
}
