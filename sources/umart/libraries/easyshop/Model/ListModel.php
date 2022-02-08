<?php

/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ES\Model;

defined('_JEXEC') or die;

use ES\Form\Form;
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
		$config['dbo'] = easyshop('db');
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
		easyshop('app')->triggerEvent('onEasyshopPrepareListQuery', [$this->context, $query]);
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

		if (easyshop('site'))
		{
			// Fix front-end limitstart
			$app = easyshop('app');

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

	protected function preprocessESForm(Form $form, $data, $group = 'easyshop')
	{
		PluginHelper::importPlugin($group);
		easyshop('app')->triggerEvent('onEasyshopPrepareForm', [$form, $data]);
	}
}
