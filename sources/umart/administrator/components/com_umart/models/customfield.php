<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\Zone;
use Umart\Model\AdminModel;
use Joomla\Registry\Registry;

class UmartModelCustomfield extends AdminModel
{
	protected $reflector;

	public function __construct(array $config)
	{
		$this->reflector = plg_sytem_umart_main('app')->input->getCmd('reflector', 'com_umart');
		parent::__construct($config);
	}

	public function save($data)
	{
		$data['reflector'] = $this->reflector;

		return parent::save($data);
	}

	public function getForm($data = [], $loadData = true)
	{
		$form = $this->loadForm('com_umart.customfield', 'customfield', [
			'control'   => 'jform',
			'load_data' => $loadData
		]);

		if (empty($form))
		{
			return false;
		}

		$input = plg_sytem_umart_main('app')->input;
		$task  = $input->get('task');

		if ($task == 'save' || $task == 'apply')
		{
			$formData = $input->post->get('jform', [], 'array');
		}
		else
		{
			$formData = $this->loadFormData();
		}

		$registry = new Registry($formData);
		$type     = trim($registry->get('type', ''));

		if (strtolower($type) == 'UI_DateTimePicker')
		{
			$type = 'flatpicker';
			$form->setValue('type', null, $type);
		}

		$paramFields = [
			'hiddenLabel',
			'render_form_class',
			'render_display_class',
			'user_access_groups',
			'validate_regex_pattern',
			'validate_regex_message',
		];

		if (strpos($this->reflector, 'com_umart.product') !== 0)
		{
			$form->removeField('group_id');
		}
		else
		{
			$form->setFieldAttribute('group_id', 'extension', $this->reflector);
		}

		if ($this->reflector != 'com_umart.user')
		{
			$form->removeField('checkout_field');
		}

		if ($this->reflector == 'com_umart.product.option')
		{
			foreach ($form->getXml()->xpath('descendant::field[@name="type"]/option') as $option)
			{
				if (!in_array((string) $option['value'], ['', 'list', 'radio', 'checkbox', 'colors', 'inline']))
				{
					unset($option[0]);
				}
			}

			$form->removeField('multiple', 'params');
			$form->removeField('hiddenLabel', 'params');
			$form->removeField('displayLayout', 'params');
		}
		elseif ($this->reflector == 'com_umart.checkout' && in_array($type, ['flatpicker', 'list', 'radio', 'checkbox', 'checkboxes', 'zone_country', 'zone_state', 'subzone', 'colors', 'inline']))
		{
			$paramFields[] = 'pricingLabel';
			$paramFields[] = 'taxes';
			$paramFields[] = 'pricingPattern';
		}

		if ($registry->get('id'))
		{
			$form->setFieldAttribute('field_name', 'readonly', 'readonly');
			$form->setFieldAttribute('type', 'readonly', 'readonly');
		}

		if ($registry->get('protected'))
		{
			$form->setFieldAttribute('state', 'value', '1');
			$form->setFieldAttribute('state', 'type', 'hidden');
		}

		switch ($type)
		{
			case 'zone_country':
				$form->removeField('zone_country_id');
				$form->removeField('zone_state_id');

			case 'zone_state':
				$form->removeField('zone_state_id');

			case 'subzone':
				$form->setFieldAttribute('default_value', 'type', 'zone');
				$form->setFieldAttribute('default_value', 'filter', 'uint');
				$form->setFieldAttribute('default_value', 'zone_type', str_replace('zone_', '', $type));
				$form->setFieldAttribute('default_value', 'class', 'uk-select');
				$defaultValue = (int) $registry->get('default_value', 0);

				if ($type != 'zone_country'
					&& $defaultValue > 0
					&& ($zoneTable = plg_sytem_umart_main(Zone::class)->load($defaultValue))
				)
				{
					if ($zoneTable->type === 'state')
					{
						$form->setValue('zone_country_id', null, $zoneTable->parent_id);
					}
					elseif ($zoneTable->type === 'subzone')
					{
						$form->setValue('zone_state_id', null, $zoneTable->parent_id);
						$zoneTable->load($zoneTable->parent_id);
						$form->setValue('zone_country_id', null, $zoneTable->parent_id);
					}
				}

				break;

			case 'textarea':
			case 'editor':
				$paramFields[] = 'rows';
				$paramFields[] = 'cols';
				break;

			case 'callname':
				$paramFields[] = 'call_name_type';
				break;

			case 'address':
				$paramFields[] = 'address_line_2';
				break;

			case 'list':
			case 'radio':
			case 'checkboxes':
			case 'colors':
			case 'inline':
				$paramFields[] = 'options';

				if ($type == 'colors')
				{
					$paramFields[] = 'displayLayout';
					$form->loadFile(__DIR__ . '/forms/customfield_colors.xml');
				}

				if (in_array($type, ['list', 'colors', 'inline']))
				{
					$paramFields[] = 'multiple';
				}

				break;

			case 'flatpicker':
				$paramFields[] = 'mode';
				$paramFields[] = 'showTime';
				$paramFields[] = 'numberOfMonths';
				$paramFields[] = 'minDate';
				$paramFields[] = 'maxDate';
				$paramFields[] = 'minTime';
				$paramFields[] = 'maxTime';
				$paramFields[] = 'disableDate';

				if ($registry->get('id'))
				{
					$form->setFieldAttribute('mode', 'readonly', 'readonly', 'params');
				}

				break;
		}

		if (in_array($type, ['text', 'textarea', 'email', 'address', 'callname']))
		{
			$paramFields[] = 'placeholder';
		}

		if (!in_array($type, ['zone_country', 'zone_state', 'subzone']))
		{
			$form->removeField('zone_country_id');
			$form->removeField('zone_state_id');
		}

		foreach ($form->getGroup('params') as $field)
		{
			$name = $field->getAttribute('name');

			if (!in_array($name, $paramFields, true))
			{
				$form->removeField($name, 'params');
			}
		}

		return $form;
	}

	public function canEditState($record)
	{
		if ($canEdit = parent::canEditState($record))
		{
			$task = plg_sytem_umart_main('app')->input->get('task');

			if ($task !== 'saveOrderAjax' && $record->protected)
			{
				if ((int) $record->state !== 1)
				{
					$record->set('state', 1);
					$record->store();
				}

				return false;
			}
		}

		return $canEdit;
	}

	public function canDelete($record)
	{
		if ($canDelete = parent::canDelete($record))
		{
			if ($record->protected)
			{
				return false;
			}
		}

		return $canDelete;
	}

	protected function populateState()
	{
		$this->setState('filter.reflector', $this->reflector);

		parent::populateState();
	}

	protected function getReorderConditions($table)
	{
		$db = $this->getDbo();

		return $db->quoteName('reflector') . ' = ' . $db->quote($table->reflector);
	}
}
