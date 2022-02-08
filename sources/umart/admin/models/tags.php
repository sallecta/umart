<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;
use ES\Model\ListModel;

class EasyshopModelTags extends ListModel
{
	protected $searchField = 'name';

	public function __construct(array $config)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = [
				'id', 'a.id',
				'name', 'a.name',
				'alias', 'a.alias',
				'state', 'a.state',
				'language', 'a.language',
				'ordering', 'a.ordering',
			];
		}
		parent::__construct($config);
	}

	protected function populateState($ordering = null, $direction = null)
	{
		$context = easyshop('app')->input->getCmd('context', 'com_easyshop.product');
		$this->setState('filter.context', $context);

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		parent::populateState('a.name', 'asc');
	}

	public function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.context');
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = parent::getListQuery();

		$query->select(
			$this->getState('list.select', 'a.id, a.context, a.state, a.name, a.alias, a.checked_out, '
				. 'a.checked_out_time, a.created_date, a.created_by, a.language, a.ordering, u.name AS author, uu.name AS editor, '
				. 'l.title AS language_title, l.image AS language_image'
			)
		);

		$query->from($db->quoteName('#__easyshop_tags', 'a'))
			->leftJoin($db->quoteName('#__users', 'u') . ' ON u.id = a.created_by')
			->leftJoin($db->quoteName('#__users', 'uu') . ' ON uu.id = a.checked_out')
			->leftJoin($db->quoteName('#__languages', 'l') . ' ON l.lang_code = a.language');
		$reflector = $this->getState('filter.context');
		$query->where('a.context = ' . $db->quote($reflector));

		if ($language = $this->getState('filter.language'))
		{
			$query->where('a.language = ' . $db->quote($language));
		}

		$this->standardFilter($db, $query);

		return $query;
	}
}
