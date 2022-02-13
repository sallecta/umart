<?php

/**
 
 
 
 
 
 */

namespace Umart\Model;

defined('_JEXEC') or die;

use Umart\Form\Form;
use Exception;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\Model\ListModel as CMSListModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Utilities\ArrayHelper;
use ReflectionClass;

class ListModel extends CMSListModel
{
	protected $searchField = 'title';
	protected $searchMetaData = false;
	protected $key = 'id';
	protected $ordering = 'a.id';
	protected $direction = 'desc';
	protected $translateTable = null;

	public function __construct(array $config = [])
	{
		$config['dbo'] = plg_sytem_umart_main('db');
		parent::__construct($config);
	}

	public function standardFilter($db, $query, $qn = 'a', $stateField = 'state')
	{
		if ($stateField)
		{
			$published = $this->getState('filter.published', '');

			if (is_numeric($published))
			{
				$query->where($qn . '.' . $stateField . ' = ' . (int) $published);
			}
			elseif ($published === '')
			{
				$query->where($qn . '.' . $stateField . ' <> -2');
			}
		}

		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where($qn . '.' . $this->key . ' = ' . (int) substr($search, 3));
			}
			else
			{
				$searchField = $this->searchField;
				$search      = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));

				if (is_array($searchField))
				{
					$orWhere = [];

					foreach ($searchField as $field)
					{
						$orWhere[] = $qn . '.' . $field . ' LIKE ' . $search;
					}

					$query->where('(' . implode(' OR ', $orWhere) . ')');
				}
				else
				{
					if (strpos($searchField, '.') === false)
					{
						$searchField = $qn . '.' . $searchField;
					}

					$where = $searchField . ' LIKE ' . $search;

					if ($this->searchMetaData)
					{
						$where .= ' OR (' . $qn . '.metatitle LIKE ' . $search . ' OR ' . $qn . '.metakey LIKE ' . $search . ' OR ' . $qn . '.metadesc LIKE ' . $search . ')';
					}

					$query->where('(' . $where . ')');
				}
			}
		}

		$ordering  = $this->getState('list.ordering', $this->ordering);
		$direction = $this->getState('list.direction', $this->direction);
		$query->order($db->escape($ordering) . ' ' . $db->escape($direction));
		plg_sytem_umart_main('app')->triggerEvent('onUmartPrepareListQuery', [$this->context, $query]);
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

	protected function populateState($ordering = null, $direction = null)
	{
		if (null === $ordering)
		{
			$ordering = $this->ordering;
		}

		if (null === $direction)
		{
			$direction = $this->direction;
		}

		if (plg_sytem_umart_main('site'))
		{
			// Fix front-end limitstart
			$app = plg_sytem_umart_main('app');

			if (null === $app->input->get('limitstart'))
			{
				$app->input->set('limitstart', 0);
			}
		}

		parent::populateState($ordering, $direction);
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

		try
		{
			$form = Form::getInstance($name, $source, $options, false, $xpath);

			if (!Multilanguage::isEnabled() && $form->getField('language', 'filter'))
			{
				$form->removeField('language', 'filter');
			}

			if (isset($options['load_data']) && $options['load_data'])
			{
				$data = $this->loadFormData();
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

	protected function preprocessUmartForm(Form $form, $data, $group = 'umart')
	{
		PluginHelper::importPlugin($group);
		plg_sytem_umart_main('app')->triggerEvent('onUmartPrepareForm', [$form, $data]);
	}
}
