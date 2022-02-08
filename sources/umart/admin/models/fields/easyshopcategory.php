<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;
use Joomla\Utilities\ArrayHelper;

class JFormFieldEasyshopCategory extends JFormFieldCategory
{
	public $type = 'EasyshopCategory';

	protected function getOptions()
	{
		$parentId  = (int) $this->getAttribute('parent_id', 0);
		$showEmpty = $this->getAttribute('show_empty');

		if ($parentId > 0)
		{
			$extension = (string) $this->element['extension'];
			$published = (string) $this->element['published'];
			$language  = (string) $this->element['language'];
			$user      = JFactory::getUser();
			$groups    = implode(',', $user->getAuthorisedViewLevels());
			$db        = easyshop('db');
			$query     = $db->getQuery(true)
				->select('a.id, a.title, a.level, a.language')
				->from('#__categories AS a')
				->where('extension = ' . $db->quote($extension))
				->where('a.access IN (' . $groups . ')')
				->order('a.lft');

			if ($published)
			{
				if (is_numeric($published))
				{
					$query->where('a.published = ' . (int) $published);
				}
				elseif (is_array($published))
				{
					$published = ArrayHelper::toInteger($published);
					$query->where('a.published IN (' . implode(',', $published) . ')');
				}
			}

			if ($language)
			{
				if (is_string($language))
				{
					$query->where('a.language = ' . $db->quote($language));
				}
				elseif (is_array($language))
				{
					foreach ($language as &$lang)
					{
						$lang = $db->quote($lang);
					}

					$query->where('a.language IN (' . implode(',', $language) . ')');
				}
			}

			$subQuery = $db->getQuery(true)
				->select('sub.id')
				->from('#__categories as sub')
				->join('INNER', '#__categories as this ON sub.lft > this.lft AND sub.rgt < this.rgt')
				->where('this.id = ' . $parentId);
			$query->where('a.id IN (' . (string) $subQuery . ')');

			$db->setQuery($query);
			$items   = $db->loadObjectList();
			$options = [];

			if ($showEmpty == '1' || $showEmpty == 'true')
			{
				$text      = $extension == 'com_easyshop.product' ? JText::_('COM_EASYSHOP_CATEGORY_SELECT') : JText::_('COM_EASYSHOP_BRAND_SELECT');
				$options[] = JHtml::_('select.option', '', $text);
			}

			foreach ($items as &$item)
			{
				$repeat      = ($item->level - 1 >= 0) ? $item->level - 1 : 0;
				$item->title = str_repeat('- ', $repeat) . $item->title;

				if ($item->language !== '*')
				{
					$item->title .= ' (' . $item->language . ')';
				}

				$options[] = JHtml::_('select.option', $item->id, $item->title);
			}

			return $options;
		}

		return parent::getOptions();
	}

	protected function getInput()
	{
		$extension = (string) $this->element['extension'];

		if (in_array($extension, ['com_easyshop.product', 'com_easyshop.brand']))
		{
			return parent::getInput();
		}

		$data                                    = [];
		$this->element['extension']              = 'com_easyshop.product';
		$data[JText::_('COM_EASYSHOP_CATEGORY')] = parent::getOptions();
		$this->element['extension']              = 'com_easyshop.brand';
		$data[JText::_('COM_EASYSHOP_BRAND')]    = parent::getOptions();

		return JHtml::_('select.groupedlist', $data, $this->name, [
			'group.items' => null,
			'id'          => $this->id,
			'list.select' => $this->value,
		]);
	}
}
