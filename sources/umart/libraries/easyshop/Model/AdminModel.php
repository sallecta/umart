<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ES\Model;

use ES\Classes\Addon;
use ES\Classes\CustomField;
use ES\Classes\Translator;
use ES\Classes\User;
use ES\Form\Form;
use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\Model\AdminModel as CMSAdminModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\String\Inflector;
use Joomla\Utilities\ArrayHelper;
use ReflectionClass;
use SimpleXMLElement;

defined('_JEXEC') or die;

class AdminModel extends CMSAdminModel
{
	protected $fieldReflector = null;
	protected $translationRefTable = null;

	public function __construct(array $config)
	{
		$config['events_map'] = [
			'delete'       => 'easyshop',
			'save'         => 'easyshop',
			'change_state' => 'easyshop'
		];

		$config['event_before_delete'] = 'onEasyshopBeforeDelete';
		$config['event_after_delete']  = 'onEasyshopAfterDelete';
		$config['event_before_save']   = 'onEasyshopBeforeSave';
		$config['event_after_save']    = 'onEasyshopAfterSave';
		$config['event_change_state']  = 'onEasyshopChangeState';
		$config['dbo']                 = easyshop('db');
		parent::__construct($config);
	}

	public function getForm($data = [], $loadData = true)
	{
		/** @var Form $form */
		$name = $this->getName();
		$form = $this->loadForm($this->option . '.' . $name, $name, ['control' => 'jform', 'load_data' => $loadData]);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	protected function loadForm($name, $source = null, $options = [], $clear = false, $xpath = false)
	{
		$options['control'] = ArrayHelper::getValue((array) $options, 'control', false);
		$hash               = md5($source . serialize($options));

		if (isset($this->_forms[$hash]) && !$clear)
		{
			return $this->_forms[$hash];
		}

		$reflection = new ReflectionClass($this);
		$path       = dirname($reflection->getFileName());
		Form::addFormPath($path . '/forms');
		Form::addFieldPath($path . '/fields');
		Form::addRulePath($path . '/rules');

		try
		{
			$form = Form::getInstance($name, $source, $options, false, $xpath);

			if (!Multilanguage::isEnabled() && $form->getField('language'))
			{
				$form->setFieldAttribute('language', 'description', 'COM_EASYSHOP_ENABLE_LANGUAGE_FILTER_DESC');
				$form->setFieldAttribute('language', 'readonly', 'true');
				$form->setFieldAttribute('language', 'default', '*');
			}

			if (!empty($options['load_data']))
			{
				$data = $this->loadFormData();
				$app  = easyshop('app');
				$view = $this->getName();
				$id   = isset($data['id']) ? (int) $data['id'] : 0;

				if (empty($id))
				{
					$filters = (array) $app->getUserState('com_easyshop.' . Inflector::getInstance()->toPlural($view) . '.filter');

					foreach ($filters as $property => $value)
					{
						if (!empty($value) && $form->getField($property))
						{
							$data[$property] = $value;
						}
					}
				}
				elseif ($this->translationRefTable)
				{
					$db        = $this->getDbo();
					$query     = $db->getQuery(true)
						->select('a.translationId, a.translatedValue')
						->from($db->quoteName('#__easyshop_translations', 'a'))
						->where('a.translationId LIKE ' . $db->quote('%.' . $this->translationRefTable . '.' . $id . '.%'));
					$transData = [];

					if ($rows = $db->setQuery($query)->loadObjectList())
					{
						foreach ($rows as $row)
						{
							list($langCode, $refTable, $refKey, $refField) = explode('.', $row->translationId, 4);
							$transData[$langCode][$refField] = $row->translatedValue;
						}
					}

					$form->setTranslationsData($transData);
				}
			}
			else
			{
				$data = [];
			}

			$this->preprocessESForm($form, $data);
			$form->bind($data);
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		$this->_forms[$hash] = $form;

		return $form;
	}

	protected function loadFormData()
	{
		$app  = easyshop('app');
		$name = $this->getName();
		$data = $app->getUserState($this->option . '.edit.' . $name . '.data', []);

		if (empty($data))
		{
			$item = new Registry($this->getItem());
			$data = $item->toArray();
		}

		$this->preprocessData($this->option . '.' . $name, $data);

		return $data;
	}

	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		if (!empty($item->id))
		{
			easyshop('app')->triggerEvent('onEasyshopModelGetItem', ['com_easyshop.' . strtolower($this->getName()), $item]);
		}

		return $item;
	}

	protected function preprocessData($context, &$data, $group = 'easyshop')
	{
		easyshop('app')->triggerEvent('onEasyshopPrepareData', [$context, &$data]);
	}

	protected function preprocessESForm(Form $form, $data, $group = 'easyshop')
	{
		/** @var CustomField $customField */
		$customField = easyshop(
			CustomField::class,
			[
				'reflector'    => isset($this->fieldReflector) ? $this->fieldReflector : $form->getName(),
				'reflector_id' => isset($data['id']) ? (int) $data['id'] : 0,
			]
		);

		if ($customField->isValidReflector())
		{
			$formData = $customField->getFormFieldData();

			if (!empty($formData['form'])
				&& $formData['form'] instanceof SimpleXMLElement
			)
			{
				if ($form->load($formData['form']))
				{
					$form->bind(['customfields' => $formData['data']]);
				}
			}
		}

		$this->postFormHook($form, $data);
	}

	protected function postFormHook(Form $form, $data)
	{
		easyshop('app')->triggerEvent('onEasyshopPrepareForm', [$form, $data]);
	}

	public function save($data)
	{
		if (!empty($data['params']))
		{
			$registry = new Registry;
			$registry->loadArray($data['params']);
			$data['params'] = (string) $registry->toString();
		}

		/**
		 * @var CMSApplication $app
		 * @var CustomField    $customField
		 */
		$name          = $this->getName();
		$app           = easyshop('app');
		$addOns        = easyshop('administrator') ? easyshop(Addon::class)->getAddons($name) : [];
		$jform         = $app->input->get('jform', [], 'array');
		$addonData     = empty($jform['addon']) ? [] : $jform['addon'];
		$data['addon'] = [];

		if (!empty($addonData))
		{
			foreach ($addonData as $element => $array)
			{
				if (isset($addOns[$element]))
				{
					$dataArray = $this->validate($addOns[$element], $array);

					if (false === $dataArray)
					{
						return false;
					}

					$data['addon'][$element] = $dataArray;
				}
			}
		}

		/** @var CustomField $customField */
		$fieldReflector = 'com_easyshop.' . ($this->fieldReflector ?: $name);
		$customField    = easyshop(CustomField::class, ['reflector' => $fieldReflector]);
		$fieldsData     = !empty($data['customfields']) && $customField->isValidReflector($fieldReflector) ? $data['customfields'] : [];

		if ($result = parent::save($data))
		{
			$itemId = (int) $this->getState($name . '.id');
			$db     = $this->getDbo();
			$customField->setReflectorId($itemId);

			if (empty($fieldsData))
			{
				$customField->removeValues($fieldReflector, $itemId);
			}
			else
			{
				$customField->save($fieldsData, null, false, 0);
			}

			if (count($addOns))
			{
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__easyshop_params'))
					->where($db->quoteName('context') . ' LIKE ' . $db->quote('%.' . $name) . ' AND ' . $db->quoteName('item_id') . ' = ' . $itemId);
				$db->setQuery($query)
					->execute();

				if (!empty($data['addon']))
				{
					$values = [];

					foreach ($data['addon'] as $element => $array)
					{
						$context   = $element . '.' . $name;
						$dataArray = $this->validate($addOns[$element], $array);

						if (false === $dataArray)
						{
							return false;
						}

						$addonData[$element] = $dataArray;
						$registry            = new Registry;
						$registry->loadArray($dataArray);
						$values[] = $db->quote($context) . ',' . $itemId . ',' . $db->quote($registry->toString());
					}

					if (count($values))
					{
						$query->clear()
							->insert($db->quoteName('#__easyshop_params'))
							->columns(['context', 'item_id', 'data'])
							->values($values);
						$db->setQuery($query)
							->execute();
					}
				}

				$app->triggerEvent('onEasyshopAddonAfterSave', ['com_easyshop.' . $name, $addonData, $itemId]);
			}

			if (!empty($data['ESTranslations']))
			{
				$table    = $this->getTable();
				$refTable = str_replace('#__', '', $table->getTableName());
				Translator::saveTranslations($refTable, $itemId, $data);
			}
		}

		return $result;
	}

	public function validate($form, $data, $group = null)
	{
		/**
		 * @var Form           $form
		 * @var CMSApplication $app
		 */

		$app = easyshop('app');
		PluginHelper::importPlugin($this->events_map['validate']);
		$app->triggerEvent('onUserBeforeDataValidation', [$form, &$data]);
		$data      = $form->filter($data);
		$return    = $form->validate($data, $group);
		$transData = Translator::validateTranslationsData($form, $group);

		if (false !== $transData)
		{
			$data['ESTranslations'] = $transData;
		}

		// Check for an error.
		if ($return instanceof Exception)
		{
			$this->setError($return->getMessage());

			return false;
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message)
			{
				$this->setError($message);
			}

			return false;
		}

		return $data;
	}

	public function getState($property = null, $default = null)
	{
		$name  = str_ireplace('EasyshopModel', '', strtolower(get_class($this)));
		$key   = 'model.' . $name . '.state.' . $property;
		$state = easyshop('state')->get($key, null);

		if (null !== $state)
		{
			return $state;
		}

		return parent::getState($property, $default);
	}

	public function getTable($type = null, $prefix = 'EasyshopTable', $config = [])
	{
		if (null === $type)
		{
			$type = ucfirst($this->getName());
		}

		$reflection = new ReflectionClass($this);
		$path       = dirname(dirname($reflection->getFileName()));
		Table::addIncludePath(ES_COMPONENT_ADMINISTRATOR . '/tables');
		Table::addIncludePath($path . '/tables');

		return Table::getInstance($type, $prefix, $config);
	}

	public function setState($property, $value = null)
	{
		$name  = str_ireplace('EasyshopModel', '', strtolower(get_class($this)));
		$key   = 'model.' . $name . '.state.' . $property;
		$state = easyshop('state')->get($key, null);

		if (null !== $state)
		{
			$value = $state;
		}

		return parent::setState($property, $value);
	}

	public function publish(&$pks, $value = 1)
	{
		$context = 'com_easyshop.' . strtolower($this->getName());
		$results = easyshop('app')->triggerEvent('onEasyshopBeforeChangeState', [$context, &$pks, $value]);

		if (in_array(false, $results, true))
		{
			return false;
		}

		return parent::publish($pks, $value);
	}

	protected function canEditState($record)
	{
		return easyshop(User::class)->core('edit.state');
	}

	protected function canDelete($record)
	{
		return easyshop(User::class)->core('delete');
	}
}
