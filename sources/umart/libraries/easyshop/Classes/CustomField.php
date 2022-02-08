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
use Exception;
use JLoader;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Utility\Utility as CMSUtility;
use Joomla\Registry\Registry;
use RuntimeException;
use SimpleXMLElement;
use stdClass;

JLoader::register('EasyshopHelper', ES_COMPONENT_ADMINISTRATOR . '/helpers/easyshop.php');

class CustomField
{
	protected static $reflectors = [
		'com_easyshop.user',
		'com_easyshop.order',
		'com_easyshop.checkout',
		'com_easyshop.product.customfield',
		'com_easyshop.product.option',
		'com_easyshop.order.billing_address',
		'com_easyshop.order.shipping_address',
	];
	protected $reflector;
	protected $reflector_id = 0;

	public function __construct(array $config = [])
	{
		$this->setUp($config);
	}

	public function setUp(array $config = [])
	{
		if (array_key_exists('reflector', $config))
		{
			$this->reflector = $config['reflector'];
		}

		if (array_key_exists('reflector_id', $config))
		{
			$this->reflector_id = (int) $config['reflector_id'];
		}
	}

	public function setReflector(string $reflector)
	{
		$this->reflector = $reflector;
	}

	public function setReflectorId($reflectorId)
	{
		$this->reflector_id = (int) $reflectorId;
	}

	public function findFieldByName($fieldName)
	{
		foreach ($this->load() as $group => $fields)
		{
			foreach ($fields as $field)
			{
				if ($field->field_name == $fieldName)
				{
					return $field;
				}
			}
		}

		return false;
	}

	public function load($key = 'id')
	{
		$this->check();
		$ref = $this->reflector;
		static $fields = [];

		if (!isset($fields[$ref]))
		{
			$showOn = (int) easyshop('app')->getClientId();
			$db     = easyshop('db');
			$query  = $db->getQuery(true)
				->select('a.id, a.name, a.alias, a.type, a.default_value, a.required, a.params, ' .
					'a.ordering, a.reflector, a.rules, a.showon, a.attributes, a.group_id, a.checkout_field, ' .
					'a.field_name, g.title AS group_title')
				->from($db->quoteName('#__easyshop_customfields', 'a'))
				->select('g.title AS group_title')
				->leftJoin($db->quoteName('#__categories', 'g') . ' ON a.group_id = g.id')
				->where('a.state = 1 AND a.showon IN (-1,' . $showOn . ')')
				->where('a.reflector = ' . $db->quote($ref))
				->order('a.ordering ASC');

			$db->setQuery($query);
			$fields[$ref] = [];

			if ($rows = $db->loadObjectList($key))
			{
				/** @var Utility $utility */
				$utility = easyshop(Utility::class);

				foreach ($rows as $id => $row)
				{
					$params = new Registry;
					$params->loadString($row->params);
					$userAccessGroups = $params->get('user_access_groups', []);

					if (empty($userAccessGroups)
						|| $utility->userAccess($userAccessGroups)
					)
					{
						$this->parseLabelLanguage($row->name);
						$fields[$ref][$row->group_id][$id] = $row;
					}
				}
			}
		}

		return $fields[$ref];
	}

	public function check($full = false, $reflector = null)
	{
		if (!$this->isValidReflector($reflector)
			|| ($full && (int) $this->reflector_id < 1)
		)
		{
			throw new RuntimeException(Text::_('COM_EASYSHOP_WARNING_PROVIDE_VALID_REFLECTOR'), 404);
		}

		return true;
	}

	public function isValidReflector($reflector = null)
	{
		if (null === $reflector)
		{
			$reflector = $this->reflector;
		}

		foreach (self::$reflectors as $rft)
		{
			if ($reflector == $rft)
			{
				return true;
			}
		}

		return false;
	}

	protected function parseLabelLanguage(&$label)
	{
		$key = 'COM_EASYSHOP_CUSTOMFIELD_' . strtoupper($label);
		$key = preg_replace('/[^A-Z0-9]+/i', '_', $key);

		if (CMSFactory::getLanguage()->hasKey($key))
		{
			$label = Text::_($key);
		}

		return $label;
	}

	public function parseFormFieldData($fieldsData)
	{
		$this->check();

		foreach ($fieldsData as $fieldId => $value)
		{
			/** @var $field stdClass */
			if ($field = $this->findField($fieldId))
			{
				$fieldsData[$fieldId]        = clone $field;
				$fieldsData[$fieldId]->value = $value;
				$this->parseFieldValues($fieldsData[$fieldId]);
			}
			else
			{
				unset($fieldsData[$fieldId]);
			}
		}

		return $fieldsData;
	}

	public function findField($id)
	{
		$groups = $this->load();

		foreach ($groups as $groupId => $fields)
		{
			foreach ($fields as $field)
			{
				if ($id == $field->id)
				{
					return $field;
				}
			}
		}

		return false;
	}

	protected function parseFieldValues($field)
	{
		$type   = strtolower($field->type);
		$params = $field->params;

		if (!($params instanceof Registry))
		{
			$params = new Registry($params);
		}

		switch ($type)
		{
			case 'list':
			case 'checkbox':
			case 'checkboxes':
			case 'colors':
			case 'inline':

				$renderer      = easyshop('renderer');
				$displayLayout = $params->get('displayLayout', 'text');
				$colorLabels   = [];

				if ($type == 'colors')
				{
					foreach ($params->get('options', []) as $colorOption)
					{
						$colorLabels[$colorOption->value] = $colorOption->text;
					}
				}

				if ($params->get('multiple') || $type === 'checkboxes')
				{
					if (is_string($field->value))
					{
						$field->value = explode('][', trim($field->value, '[]'));
					}

					if ($type == 'colors')
					{
						if ($displayLayout == 'text')
						{
							$field->display = array_map(function ($v) use ($colorLabels) {
								return isset($colorLabels[$v]) ? $colorLabels[$v] : $v;
							}, $field->value);

							$field->display = implode(', ', $field->display);
						}
						else
						{
							$field->display = $renderer->render('form.field.color.display', ['colors' => $field->value]);
						}
					}
					else
					{
						$field->display = implode(', ', $field->value);
					}
				}
				else
				{
					if ($type == 'colors')
					{
						if ($displayLayout == 'text')
						{
							$field->display = isset($colorLabels[$field->value]) ? $colorLabels[$field->value] : $field->value;
						}
						else
						{
							$field->display = $renderer->render('form.field.color.display', ['colors' => (array) $field->value]);
						}

					}
					else
					{
						$field->display = $field->value;
					}
				}

				break;

			case 'zone_country':
			case 'zone_state':
			case 'subzone':

				if ($zoneTable = easyshop(Zone::class)->load($field->value))
				{
					$field->display = $zoneTable->name;
				}
				else
				{
					$field->display = $field->value;
				}

				break;

			case 'callname':

				$field->display = str_replace('][', ' ', trim($field->value, '[]'));
				break;

			case 'address':

				$field->display = str_replace('][', ', ', trim($field->value, '[]'));
				break;

			case 'ui_datetimepicker':
			case 'flatpicker':
				/** @var Utility $utility */
				$utility        = easyshop(Utility::class);
				$field->display = $utility->displayPicker(
					$field->value,
					[
						'mode'     => $params->get('mode', 'single'),
						'showTime' => $params->get('showTime'),
					]
				);
				break;

			default:

				$field->display = $field->value;
				break;
		}
	}

	public function register($nameString = null)
	{
		if (null !== $nameString)
		{
			$nameString = preg_replace('/[^z-zA-z0-9\.]/', '', $nameString);

			if (!in_array($nameString, self::$reflectors))
			{
				array_push(self::$reflectors, $nameString);
			}
		}

		return self::$reflectors;
	}

	public function getGroups()
	{
		$this->check();
		static $groups = [];
		$ref = $this->reflector;

		if (!isset($groups[$ref]))
		{
			$db    = easyshop('db');
			$query = $db->getQuery(true)
				->select('a.id, a.title, a.alias, a.params')
				->from($db->quoteName('#__categories', 'a'))
				->where('a.published = 1')
				->where('a.extension = ' . $db->quote($this->reflector))
				->order('a.lft ASC');
			$db->setQuery($query);

			if ($rows = $db->loadObjectList())
			{
				$groups[$ref] = $rows;
			}
			else
			{
				$groups[$ref] = [];
			}
		}

		return $groups[$ref];
	}

	public function save($data = null, $groupName = 'customfields', $validate = false, $groupId = 0)
	{
		if ($validate && $groupId)
		{
			if (false === ($fieldsData = $this->validate($data, $groupName, $groupId)))
			{
				return false;
			}
		}
		else
		{
			$fieldsData = $groupName ? $data[$groupName] : $data;
		}

		$valueData = [];

		foreach ($fieldsData as $customFieldId => $value)
		{
			$valueData[$customFieldId] = [
				'customfield_id' => $customFieldId,
				'value'          => $value,
			];
		}

		if ($valueData)
		{
			return $this->insertValues(array_values($valueData));
		}

		return true;
	}

	public function validate($data = null, $groupName = 'customfields', $groupId = 0)
	{
		$this->check(false);
		$form = $this->getForm($data, $groupName, $groupId);

		if (false === $form)
		{
			return false;
		}

		$validData = $form->filter($data, $groupName);
		$valid     = $form->validate($validData, $groupName);

		if ($valid instanceof Exception)
		{
			easyshop('state')->set('customfield.errors', [$valid->getMessage()]);

			return false;
		}

		if (false === $valid)
		{
			$errors = [];

			foreach ($form->getErrors() as $error)
			{
				if ($error instanceof Exception)
				{
					$errors[] = $error->getMessage();
				}
				else
				{
					$errors[] = (string) $error;
				}
			}

			easyshop('state')->set('customfield.errors', $errors);

			return false;
		}

		return $validData;
	}

	public function getForm($data = null, $groupName = 'customfields', $groupId = 0)
	{
		$this->check(false);
		$app = easyshop('app');

		if (!is_array($data))
		{
			$data = $app->input->get('jform', [], 'array');
		}

		if (!isset($data[$groupName]))
		{
			$data[$groupName] = [];
		}

		/**
		 * @var                   $form Form
		 * @var SimpleXMLElement  $formElement
		 * @var SimpleXMLElement  $validateField
		 */
		$formField   = $this->getFormFieldData($groupId, [], $groupName);
		$form        = new Form('com_easyshop.customfield', ['control' => 'jform']);
		$formElement = $formField['form'];

		if (!$form->load($formElement))
		{
			return false;
		}

		$validateFields = $formElement->xpath('//field[@validate_regex_pattern]');

		if (false !== $validateFields)
		{
			$form->bind($data);

			foreach ($validateFields as $validateField)
			{
				$regex   = (string) @$validateField->attributes()->validate_regex_pattern;
				$message = (string) @$validateField->attributes()->validate_regex_message;
				$name    = (string) @$validateField->attributes()->name;
				$value   = $form->getValue($name, $groupName);

				if (!@preg_match('/' . $regex . '/', $value))
				{
					if ($message)
					{
						easyshop('state')->set('customfield.errors', [$message]);
					}

					return false;
				}
			}
		}

		return $form;
	}

	public function getFormFieldData($groupId = 0, array $conditions = [], $group = 'customfields', $fieldName = null)
	{
		$data      = [];
		$items     = $this->getItemsByGroup($groupId);
		$xml       = new DOMDocument('1.0', 'UTF-8');
		$form      = $xml->appendChild(new DOMElement('form'));
		$fieldSets = $form->appendChild(new DOMElement('fields'));
		$fieldSets->setAttribute('name', $group);
		$fieldSet = $fieldSets->appendChild(new DOMElement('fieldset'));

		foreach ($items as $item)
		{
			$pass = true;

			foreach ($conditions as $k => $v)
			{
				if (!isset($item->{$k}) || $item->{$k} != $v)
				{
					$pass = false;

					break;
				}
			}

			if (!$pass)
			{
				continue;
			}

			if (empty($item->group_title))
			{
				$fieldsetName = str_replace('.', '_', $this->reflector);
			}
			else
			{
				$fieldsetName = preg_replace(['/\s+/', '/[^a-z0-9]/i'], ['_', ''], $item->group_title . '_' . $item->group_id);
			}

			if (empty($fieldsetName))
			{
				$fieldsetName = 'general';
			}

			$fieldSet->setAttribute('name', $fieldsetName);
			$fieldSet->setAttribute('label', $item->group_title);
			$name = $fieldName ? $fieldName . '_' . $item->id : $item->id;
			$this->prepareField($fieldSet, $item, $name);
			$data[$name] = $item->value;
		}

		return [
			'form' => new SimpleXMLElement($xml->saveXML()),
			'data' => $data
		];
	}

	public function getItemsByGroup($groupId = 0, $reflectorId = 0)
	{
		$values = $this->getValues($reflectorId);
		$groups = $this->load();
		$items  = [];

		if (isset($groups[$groupId]))
		{
			foreach ($groups[$groupId] as $id => $item)
			{
				$items[$id]          = clone $item;
				$items[$id]->value   = null;
				$items[$id]->display = null;

				if (isset($values[$id]))
				{
					$items[$id]->value   = $values[$id]->value;
					$items[$id]->display = $values[$id]->display;
				}
				elseif ($items[$id]->type != 'checkbox')
				{
					$items[$id]->value   = $items[$id]->default_value;
					$items[$id]->display = $items[$id]->value;
				}
			}
		}

		return $items;
	}

	public function getValues($reflectorId = 0, $forceNew = false)
	{
		$this->check();
		static $values = [];
		$ref   = $this->reflector;
		$refId = (int) $reflectorId;

		if ($refId < 1)
		{
			$refId = $this->reflector_id;
		}

		if ($refId < 1)
		{
			return [];
		}

		if (!isset($values[$ref][$refId]) || $forceNew)
		{
			$db    = easyshop('db');
			$query = $db->getQuery(true)
				->select('a.customfield_id, a.reflector_id, a.value, a2.type, a2.name, a2.field_name, a2.alias, a2.params')
				->from($db->quoteName('#__easyshop_customfield_values', 'a'))
				->innerJoin($db->quoteName('#__easyshop_customfields', 'a2') . ' ON a2.id = a.customfield_id')
				->where('a.reflector LIKE ' . $db->quote($ref . '%'))
				->where('a.reflector_id = ' . $refId)
				->order('a2.ordering ASC');
			$db->setQuery($query);

			if (!($fieldValues = $db->loadObjectList('customfield_id')))
			{
				$fieldValues = [];
			}

			foreach ($fieldValues as $field)
			{
				$this->parseLabelLanguage($field->name);
				$this->parseFieldValues($field);
			}

			$values[$ref][$refId] = $fieldValues;
		}

		return $values[$ref][$refId];
	}

	public function prepareField(DOMElement $parentField, $itemField, $name)
	{
		$params      = new Registry($itemField->params);
		$field       = $parentField->appendChild(new DOMElement('field'));
		$renderClass = trim($params->get('render_form_class'));
		$class       = 'field-' . $itemField->type . ' ' . $renderClass;

		if (strcasecmp($itemField->type, 'ui_datetimepicker') === 0)
		{
			$itemField->type = 'FlatPicker';
		}

		$field->setAttribute('name', $name);
		$field->setAttribute('alias', $itemField->alias);
		$field->setAttribute('type', $itemField->type);
		$field->setAttribute('label', $itemField->name);
		$field->setAttribute('required', $itemField->required == '1' ? 'true' : 'false');
		$field->setAttribute('default', trim($itemField->default_value));
		$field->setAttribute('displayClass', trim($params->get('render_display_class')));

		if ($validateRegexPattern = trim($params->get('validate_regex_pattern')))
		{
			$field->setAttribute('validate_regex_pattern', $validateRegexPattern);

			if ($validateRegexMsg = trim($params->get('validate_regex_message')))
			{
				$field->setAttribute('validate_regex_message', $validateRegexMsg);
			}
		}

		if ($hint = $params->get('placeholder'))
		{
			$field->setAttribute('hint', trim($hint));
		}

		$attributes = trim($itemField->attributes);

		if (!empty($attributes) && ($attributes = CMSUtility::parseAttributes($attributes)))
		{
			foreach ($attributes as $attrName => $attrValue)
			{
				$field->setAttribute($attrName, $attrValue);
			}
		}

		if ($params->get('hiddenLabel'))
		{
			$field->setAttribute('hiddenLabel', 'true');
		}

		switch (strtolower($itemField->type))
		{
			case 'textarea':
				$field->setAttribute('rows', $params->get('rows', 5));
				$field->setAttribute('cols', $params->get('cols', 15));
				$class .= ' uk-textarea';
				break;

			case 'editor':
				$field->setAttribute('filter', 'safehtml');
				$field->setAttribute('buttons', 'true');
				$class .= ' uk-textarea';
				break;

			case 'text':
			case 'email':

				$class .= ' uk-input';

				if ($itemField->type == 'email')
				{
					$field->setAttribute('validate', 'email');
				}

				break;

			case 'callname':
				$field->setAttribute('filter', 'EasyshopHelper::filterArrayToString');
				$field->setAttribute('call_name_type', $params->get('call_name_type', 1));
				break;

			case 'address':
				$field->setAttribute('filter', 'EasyshopHelper::filterArrayToString');
				$field->setAttribute('address_line_2', $params->get('address_line_2', 1));
				break;

			case 'checkbox':
				$class .= ' uk-checkbox';
				break;

			case 'list':
			case 'radio':
			case 'checkboxes':
			case 'colors':
			case 'inline':

				if ($itemField->type == 'checkboxes')
				{
					$field->setAttribute('multiple', 'true');
				}
				else
				{
					$field->setAttribute('multiple', $params->get('multiple') ? 'true' : 'false');

					if ($itemField->type == 'list')
					{
						$class .= ' uk-select';
					}
				}

				$options = (array) $params->get('options', []);

				foreach ($options as $option)
				{
					$child = $field->appendChild(new DOMElement('option'));
					$child->setAttribute('value', $option->value);
					$child->nodeValue = $option->text;
				}

				break;

			case 'zone_country':
			case 'zone_state':
			case 'subzone':
				$field->setAttribute('type', 'zone');
				$zoneType = str_replace('zone_', '', $itemField->type);
				$field->setAttribute('zone_type', $zoneType);
				$class .= ' uk-select';

				if ($itemField->type != 'zone_country')
				{
					$field->setAttribute('parent_id', '0');
				}

				break;

			case 'vat':
				$field->setAttribute('message', 'COM_EASYSHOP_CHECK_VAT_FAIL');
				$field->setAttribute('validate', 'vat');
				$class .= ' uk-input';
				break;

			case 'flatpicker':
				$mode = $params->get('mode', 'single');
				$field->setAttribute('mode', $mode);
				$field->setAttribute('showTime', $params->get('showTime', '1'));
				$field->setAttribute('numberOfMonths', $params->get('numberOfMonths', '1'));
				$field->setAttribute('minDate', $params->get('minDate', ''));
				$field->setAttribute('maxDate', $params->get('maxDate', ''));
				$field->setAttribute('minTime', $params->get('minTime', ''));
				$field->setAttribute('maxTime', $params->get('maxTime', ''));
				$field->setAttribute('disableDate', $params->get('disableDate', ''));
				$field->setAttribute('filter', 'EasyshopHelper::filterDate' . ucfirst($mode));
				$field->setAttribute('validate', 'flatpicker');
				break;
		}

		$class = array_unique(preg_split('/\s+/', $class));
		$field->setAttribute('class', trim(implode(' ', $class)));
	}

	public function insertValues(array $data = [])
	{
		$db      = easyshop('db');
		$values  = [];
		$columns = [
			'reflector',
			'reflector_id',
			'customfield_id',
			'value',
		];

		try
		{
			$this->check(true);

			foreach ($data as $dt)
			{
				if (!array_key_exists('customfield_id', $dt))
				{
					throw new RuntimeException('Invalid Data: customfield_id not found');
				}

				if (!array_key_exists('value', $dt))
				{
					throw new RuntimeException('Invalid Data: value not found');
				}

				$customFieldId = (int) $dt['customfield_id'];
				$value         = $dt['value'];

				if (is_array($value))
				{
					$value = '[' . implode('][', $value) . ']';
				}

				$values[] = $db->quote($this->reflector) . ',' . (int) $this->reflector_id . ',' . (int) $customFieldId . ',' . $db->quote($value);
			}

			if (count($values))
			{
				$this->removeValues();
				$query = $db->getQuery(true)
					->insert($db->quoteName('#__easyshop_customfield_values'))
					->columns($db->quoteName($columns))
					->values($values);
				$db->setQuery($query)
					->execute();
			}
		}
		catch (RuntimeException $e)
		{
			easyshop('app')->enqueueMessage($e->getMessage(), 'warning');

			return false;
		}

		return true;
	}

	public function removeValues($reflector = 0, $reflectorId = 0)
	{
		if (!$reflector || !$reflectorId)
		{
			$this->check(true);

			if (!$reflector)
			{
				$reflector = $this->reflector;
			}

			if (!$reflectorId)
			{
				$reflectorId = $this->reflector_id;
			}
		}

		$db    = easyshop('db');
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__easyshop_customfield_values'))
			->where($db->quoteName('reflector') . ' = ' . $db->quote($reflector))
			->where($db->quoteName('reflector_id') . ' = ' . (int) $reflectorId);
		$db->setQuery($query);

		return $db->execute();
	}
}
