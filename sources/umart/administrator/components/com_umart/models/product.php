<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\CustomField;
use Umart\Classes\Media;
use Umart\Classes\Params;
use Umart\Classes\Product;
use Umart\Form\Form;
use Umart\Model\AdminModel;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

class UmartModelProduct extends AdminModel
{
	protected $fieldReflector = 'product.customfield';
	protected $translationRefTable = 'umart_products';

	public function save($data)
	{
		$isNew = empty($data['id']);

		// Dimensions
		@$volumes = explode('x', $data['volume'], 3);
		@$data['length'] = (float) $volumes[0];
		@$data['width'] = (float) $volumes[1];
		@$data['height'] = (float) $volumes[2];

		// Taxes
		if (!empty($data['taxes']))
		{
			$data['taxes'] = '[' . implode('][', (array) $data['taxes']) . ']';
		}
		else
		{
			$data['taxes'] = '[]';
		}

		// Option fields
		$data['option_fields'] = [];
		$fieldTable            = $this->getTable('CustomField', 'UmartTable');

		if (!empty($data['options']))
		{
			foreach ($data['options'] as $optionId => $value)
			{
				if ($fieldTable->load($optionId) && $fieldTable->state == '1')
				{
					$registry = new Registry($fieldTable->params);
					$checkbox = $fieldTable->type === 'checkbox';
					$options  = $registry->get('options', []);

					if ($checkbox || $options)
					{
						$data['option_fields'][$optionId] = [
							'name'    => $fieldTable->name,
							'value'   => $value,
							'options' => $options,
						];
					}
				}
			}
		}

		$data['option_fields'] = json_encode($data['option_fields']);
		$input                 = plg_sytem_umart_main('app')->input;

		if ($input->get('task') == 'save2copy')
		{
			$origTable = clone $this->getTable('Product');
			$origTable->load($input->getInt('id'));

			if ($data['name'] == $origTable->name)
			{
				list($name, $alias) = $this->generateNewName($data['category_id'], $data['alias'], $data['name']);
				$data['name']  = $name;
				$data['alias'] = $alias;
			}
			else
			{
				if ($data['alias'] == $origTable->alias)
				{
					$data['alias'] = '';
				}
			}

			$data['state'] = 0;
		}

		if (in_array($input->get('task'), ['apply', 'save', 'save2new']) && (!isset($data['id']) || (int) $data['id'] == 0))
		{
			if (empty($data['alias']))
			{
				$data['alias'] = ApplicationHelper::stringURLSafe($data['name']);
				$table         = $this->getTable('Product');

				if ($table->load(['alias' => $data['alias'], 'category_id' => $data['category_id']]))
				{
					$msg = Text::_('COM_UMART_SAVE_WARNING');
				}

				list($name, $alias) = $this->generateNewName($data['category_id'], $data['alias'], $data['name']);
				$data['alias'] = $alias;

				if (isset($msg))
				{
					CMSFactory::getApplication()->enqueueMessage($msg, 'warning');
				}
			}
		}

		if ($result = parent::save($data))
		{
			$productId = $isNew ? (int) $this->getState($this->getName() . '.id') : (int) $data['id'];

			// Process images
			$this->processImages($productId, (array) $data['images']);

			// Process prices
			$this->processPrices($productId, (array) $data['prices']);
			$db       = $this->getDbo();
			$subQuery = $db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__umart_tags'))
				->where($db->quoteName('context') . ' = ' . $db->quote('com_umart.product'));
			$query    = $db->getQuery(true)
				->delete($db->quoteName('#__umart_tag_items'))
				->where($db->quoteName('tag_id') . ' IN (' . $subQuery->__toString() . ')')
				->where($db->quoteName('item_id') . ' = ' . $productId);
			$db->setQuery($query)
				->execute();

			if (!empty($data['productTags']))
			{
				$tags = ArrayHelper::arrayUnique(explode(',', $data['productTags']));

				foreach ($tags as $tag)
				{
					$query->clear()
						->select('a.id')
						->from($db->quoteName('#__umart_tags', 'a'))
						->where('a.context = ' . $db->quote('com_umart.product'))
						->where('(a.name = ' . $db->quote($tag) . ' OR a.alias = ' . $db->quote($tag) . ')');
					$db->setQuery($query);
					$tagId = $db->loadResult();

					if (!$tagId)
					{
						$tagTable = Table::getInstance('Tag', 'UmartTable');
						$tagTable->set('context', 'com_umart.product');
						$tagTable->set('name', $tag);
						$tagTable->set('state', 1);

						if (!$tagTable->check() || !$tagTable->store())
						{
							continue;
						}

						$tagId = $tagTable->id;
					}

					$query->clear()
						->insert($db->quoteName('#__umart_tag_items'))
						->columns(['tag_id', 'item_id'])
						->values((int) $tagId . ',' . $productId);
					$db->setQuery($query)
						->execute();
				}
			}
		}

		return $result;
	}

	protected function generateNewName($categoryId, $alias, $name)
	{
		$table = $this->getTable('Product');

		while ($table->load(array('alias' => $alias, 'category_id' => $categoryId)))
		{
			$name  = StringHelper::increment($name);
			$alias = StringHelper::increment($alias, 'dash');
		}

		return [$name, $alias];
	}

	protected function processImages($productId, array $dataImages)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__umart_medias'))
			->where($db->quoteName('product_id') . ' = ' . $productId)
			->where($db->quoteName('type') . ' = ' . $db->quote('IMAGE'));
		$db->setQuery($query)
			->execute();

		if (!empty($dataImages['file_path']))
		{
			/** @var Media $mediaClass */
			$mediaClass  = plg_sytem_umart_main(Media::class);
			$imageTitles = (array) $dataImages['title'];
			$imageDesc   = (array) $dataImages['description'];
			$imageFiles  = (array) $dataImages['file_path'];

			$query->clear()
				->insert($db->quoteName('#__umart_medias'))
				->columns([
					'product_id',
					'type',
					'file_path',
					'title',
					'description',
					'mime_type',
					'ordering',
				]);
			$values = [];

			foreach ($imageFiles as $i => $file)
			{
				$file     = Path::clean($file, '/');
				$file     = str_replace(Path::clean(UMART_MEDIA_URL, '/'), '', $file);
				$file     = trim($file, '/');
				$ordering = (int) $i + 1;
				$key      = 'FILE_' . $productId . '_' . $file;
				$mime     = $mediaClass->getMimeByFile(UMART_MEDIA . '/' . $file);

				$values[$key] = $productId . ','
					. $db->quote('IMAGE') . ','
					. $db->quote($file) . ','
					. $db->quote($imageTitles[$i]) . ','
					. $db->quote($imageDesc[$i]) . ','
					. $db->quote($mime) . ','
					. $ordering;
			}

			$query->values(array_values($values));
			$db->setQuery($query)
				->execute();
		}
	}

	protected function processPrices($productId, array $dataPrices)
	{
		$price     = (array) $dataPrices['price'];
		$currency  = (array) isset($dataPrices['currency_id']) ? $dataPrices['currency_id'] : [];
		$minQty    = (array) $dataPrices['min_quantity'];
		$productId = (int) $productId;
		$fromDate  = $dataPrices['valid_from_date'];
		$toDate    = $dataPrices['valid_to_date'];
		$weekDays  = (array) $dataPrices['week_price_days'];

		try
		{
			// Remove old prices
			$db        = $this->getDbo();
			$tblPrices = $db->quoteName('#__umart_prices');
			$query     = $db->getQuery(true);
			$query->delete($tblPrices)
				->where($db->quoteName('product_id') . ' = ' . $productId);
			$db->setQuery($query)
				->execute();

			$query->clear()
				->delete($db->quoteName('#__umart_price_days'))
				->where($db->quoteName('product_id') . ' = ' . $productId);
			$db->setQuery($query)
				->execute();
			$weekDaysValue = [];

			foreach ($weekDays as $weekDay => $weekDayPrice)
			{
				$weekDay = (int) $weekDay;

				if (is_numeric($weekDayPrice) && in_array($weekDay, [0, 1, 2, 3, 4, 5, 6]))
				{
					$weekDaysValue[] = $productId . ',' . $weekDay . ',' . (float) $weekDayPrice;
				}
			}

			if ($weekDaysValue)
			{
				$query->clear()
					->insert($db->quoteName('#__umart_price_days'))
					->columns(
						[
							'product_id',
							'week_day',
							'price',
						]
					)->values($weekDaysValue);
				$db->setQuery($query)
					->execute();
			}

			$query->clear()
				->insert($tblPrices)
				->columns(
					[
						'product_id',
						'price_value',
						'min_quantity',
						'currency_id',
						'valid_from_date',
						'valid_to_date',
					]
				);

			$execute  = false;
			$nullDate = $db->getNullDate();
			$tz       = CMSFactory::getUser()->getTimezone();

			foreach ($minQty as $i => $qty)
			{
				$value         = (float) $price[$i];
				$qty           = (int) $qty;
				$currencyId    = isset($currency[$i]) ? (int) $currency[$i] : 0;
				$validFromDate = trim($fromDate[$i]);
				$validToDate   = trim($toDate[$i]);

				if ($value < 0.01 || $qty < 1)
				{
					continue;
				}

				$execute = true;

				if (empty($validFromDate)
					|| empty($validToDate)
					|| $validFromDate == $nullDate
					|| $validToDate == $nullDate
				)
				{
					$validFromDate = $nullDate;
					$validToDate   = $validFromDate;
				}
				else
				{
					try
					{
						$validFromDate = CMSFactory::getDate($validFromDate, $tz)->toSql();
						$validToDate   = CMSFactory::getDate($validToDate, $tz)->toSql();
					}
					catch (Exception $ex)
					{
						$validFromDate = $nullDate;
						$validToDate   = $nullDate;
					}
				}

				$query->values($productId . ',' . $value . ',' . $qty . ',' . $currencyId . ',' . $db->quote($validFromDate) . ',' . $db->quote($validToDate));
			}

			if ($execute)
			{
				$db->setQuery($query)
					->execute();
			}
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}
	}

	protected function loadFormData()
	{
		$app  = plg_sytem_umart_main('app');
		$data = $app->getUserState($this->option . '.edit.product.data', []);

		if (empty($data))
		{
			$data = $this->getItem();

			if ($data->params instanceof Registry)
			{
				$data->params = $data->params->toArray();
			}

			$registry = new Registry($data);
			$data     = $registry->toArray();

			if (!empty($data['taxes']))
			{
				$data['taxes'] = array_keys($data['taxes']);
			}
		}

		$app->triggerEvent('onUmartPrepareData', [$this->option . '.product', &$data]);

		return $data;
	}

	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');

		if ($pk > 0)
		{
			$item         = clone plg_sytem_umart_main(Product::class)->getItem($pk);
			$item->volume = $item->length . 'x' . $item->width . 'x' . $item->height;
			plg_sytem_umart_main('app')->triggerEvent('onUmartModelGetItem', ['com_umart.' . strtolower($this->getName()), $item]);

			return $item;
		}

		return parent::getItem($pk);
	}

	protected function preprocessUmartForm(Form $form, $data, $group = 'umart')
	{
		$input      = plg_sytem_umart_main('app')->input;
		$registry   = new Registry($data);
		$jform      = $input->post->get('jform', [], 'array');
		$categoryId = $registry->get('category_id', isset($jform['category_id']) ? $jform['category_id'] : 0);

		if ($categoryId)
		{
			$categoryId = (int) $categoryId;
			$productId  = (int) $registry->get('id');

			$this->loadProductFields($form, 'customfields', $categoryId, $productId);
			$this->loadProductFields($form, 'options', $categoryId, $productId);

			if (in_array($input->get('task'), ['save', 'apply', 'save2copy']))
			{
				foreach ($form->getGroup('options') as $field)
				{
					$name = $field->getAttribute('name');
					$form->setFieldAttribute($name, 'required', 'false', 'options');
				}
			}
		}

		/** @var Params $params */
		$params = plg_sytem_umart_main(Params::class);
		$form->load($params->getData('product_detail'));
		$form->bind($data);
		$this->postFormHook($form, $data);
	}

	protected function loadProductFields(Form $form, $groupName, $groupId, $reflectorId)
	{
		$reflector = 'com_umart.product.' . substr($groupName, 0, strlen($groupName) - 1);
		/** @var CustomField $customField */
		$customField = plg_sytem_umart_main(CustomField::class, [
			'reflector'    => $reflector,
			'reflector_id' => $reflectorId,
		]);
		$options     = [
			'table'     => '#__umart_customfields',
			'extension' => $reflector,
		];
		$categories  = Categories::getInstance('umart.product', $options);

		foreach ($customField->getGroups() as $group)
		{
			$category = $categories->get($group->id);

			if (!$category instanceof CategoryNode)
			{
				continue;
			}

			$params  = new Registry($category->params);
			$assigns = (array) $params->get('product_categories');

			if (!empty($assigns) && !in_array($groupId, $assigns))
			{
				continue;
			}

			$formData = $customField->getFormFieldData($group->id, [], $groupName);

			if (!empty($formData['form']) && $formData['form'] instanceof SimpleXMLElement)
			{
				if ($form->load($formData['form']) && $groupName == 'customfields')
				{
					$form->bind([$groupName => $formData['data']]);
				}
			}
		}
	}

	protected function cleanCache($group = null, $client_id = 0)
	{
		parent::cleanCache('com_umart', 0);
		parent::cleanCache('mod_umart_products', 0);
		parent::cleanCache('mod_umart_search', 0);
		parent::cleanCache('mod_umart_tags', 0);
		parent::cleanCache('mod_umart_addon', 0);
		parent::cleanCache('mod_umart_currencies', 0);
	}
}
