<?php
/**
 
 
 
 
 
 */

namespace Umart\Model;

use Umart\Classes\Addon;
use Umart\Classes\CustomField;
use Umart\Classes\Translator;
use Umart\Classes\User;
use Umart\Form\Form;
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
			'delete'       => 'umart',
			'save'         => 'umart',
			'change_state' => 'umart'
		];

		$config['event_before_delete'] = 'onUmartBeforeDelete';
		$config['event_after_delete']  = 'onUmartAfterDelete';
		$config['event_before_save']   = 'onUmartBeforeSave';
		$config['event_after_save']    = 'onUmartAfterSave';
		$config['event_change_state']  = 'onUmartChangeState';
		$config['dbo']                 = plg_sytem_umart_main('db');
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
				$form->setFieldAttribute('language', 'description', 'COM_UMART_ENABLE_LANGUAGE_FILTER_DESC');
				$form->setFieldAttribute('language', 'readonly', 'true');
				$form->setFieldAttribute('language', 'default', '*');
			}

			if (!empty($options['load_data']))
			{
				$data = $this->loadFormData();
				$app  = plg_sytem_umart_main('app');
				$view = $this->getName();
				$id   = isset($data['id']) ? (int) $data['id'] : 0;

				if (empty($id))
				{
					$filters = (array) $app->getUserState('com_umart.' . Inflector::getInstance()->toPlural($view) . '.filter');

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
						->from($db->quoteName('#__umart_translations', 'a'))
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

			$this->preprocessUmartForm($form, $data);
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
		$app  = plg_sytem_umart_main('app');
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
			plg_sytem_umart_main('app')->triggerEvent('onUmartModelGetItem', ['com_umart.' . strtolower($this->getName()), $item]);
		}

		return $item;
	}

	protected function preprocessData($context, &$data, $group = 'umart')
	{
		plg_sytem_umart_main('app')->triggerEvent('onUmartPrepareData', [$context, &$data]);
	}

	protected function preprocessUmartForm(Form $form, $data, $group = 'umart')
	{
		/** @var CustomField $customField */
		$customField = plg_sytem_umart_main(
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
		plg_sytem_umart_main('app')->triggerEvent('onUmartPrepareForm', [$form, $data]);
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
		$app           = plg_sytem_umart_main('app');
		$addOns        = plg_sytem_umart_main('administrator') ? plg_sytem_umart_main(Addon::class)->getAddons($name) : [];
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
		$fieldReflector = 'com_umart.' . ($this->fieldReflector ?: $name);
		$customField    = plg_sytem_umart_main(CustomField::class, ['reflector' => $fieldReflector]);
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
				$query->delete($db->quoteName('#__umart_params'))
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
							->insert($db->quoteName('#__umart_params'))
							->columns(['context', 'item_id', 'data'])
							->values($values);
						$db->setQuery($query)
							->execute();
					}
				}

				$app->triggerEvent('onUmartAddonAfterSave', ['com_umart.' . $name, $addonData, $itemId]);
			}

			if (!empty($data['UmartTranslations']))
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

		$app = plg_sytem_umart_main('app');
		PluginHelper::importPlugin($this->events_map['validate']);
		$app->triggerEvent('onUserBeforeDataValidation', [$form, &$data]);
		$data      = $form->filter($data);
		$return    = $form->validate($data, $group);
		$transData = Translator::validateTranslationsData($form, $group);

		if (false !== $transData)
		{
			$data['UmartTranslations'] = $transData;
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
		$name  = str_ireplace('UmartModel', '', strtolower(get_class($this)));
		$key   = 'model.' . $name . '.state.' . $property;
		$state = plg_sytem_umart_main('state')->get($key, null);

		if (null !== $state)
		{
			return $state;
		}

		return parent::getState($property, $default);
	}

	public function getTable($type = null, $prefix = 'UmartTable', $config = [])
	{
		if (null === $type)
		{
			$type = ucfirst($this->getName());
		}

		$reflection = new ReflectionClass($this);
		$path       = dirname(dirname($reflection->getFileName()));
		Table::addIncludePath(UMART_COMPONENT_ADMINISTRATOR . '/tables');
		Table::addIncludePath($path . '/tables');

		return Table::getInstance($type, $prefix, $config);
	}

	public function setState($property, $value = null)
	{
		$name  = str_ireplace('UmartModel', '', strtolower(get_class($this)));
		$key   = 'model.' . $name . '.state.' . $property;
		$state = plg_sytem_umart_main('state')->get($key, null);

		if (null !== $state)
		{
			$value = $state;
		}

		return parent::setState($property, $value);
	}

	public function publish(&$pks, $value = 1)
	{
		$context = 'com_umart.' . strtolower($this->getName());
		$results = plg_sytem_umart_main('app')->triggerEvent('onUmartBeforeChangeState', [$context, &$pks, $value]);

		if (in_array(false, $results, true))
		{
			return false;
		}

		return parent::publish($pks, $value);
	}

	protected function canEditState($record)
	{
		return plg_sytem_umart_main(User::class)->core('edit.state');
	}

	protected function canDelete($record)
	{
		return plg_sytem_umart_main(User::class)->core('delete');
	}
}
