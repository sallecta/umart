<?php
/**
 
 
 
 
 
 */

defined('_JEXEC') or die;
use Joomla\Utilities\ArrayHelper;

class JFormFieldUmartCategory extends JFormFieldCategory
{
	public $type = 'UmartCategory';

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
			$db        = plg_sytem_umart_main('db');
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
				$text      = $extension == 'com_umart.product' ? JText::_('COM_UMART_CATEGORY_SELECT') : JText::_('COM_UMART_BRAND_SELECT');
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

		if (in_array($extension, ['com_umart.product', 'com_umart.brand']))
		{
			return parent::getInput();
		}

		$data                                    = [];
		$this->element['extension']              = 'com_umart.product';
		$data[JText::_('COM_UMART_CATEGORY')] = parent::getOptions();
		$this->element['extension']              = 'com_umart.brand';
		$data[JText::_('COM_UMART_BRAND')]    = parent::getOptions();

		return JHtml::_('select.groupedlist', $data, $this->name, [
			'group.items' => null,
			'id'          => $this->id,
			'list.select' => $this->value,
		]);
	}
}
