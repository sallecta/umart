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

class Tags
{
	public function getTags($context = 'com_easyshop.product')
	{
		static $tags = [];

		if (!isset($tags[$context]))
		{
			$db    = easyshop('db');
			$query = $db->getQuery(true)
				->select('a.id, a.name, a.alias, a.language')
				->from($db->quoteName('#__easyshop_tags', 'a'))
				->where('a.context = ' . $db->quote($context) . ' AND a.state = 1');
			$db->setQuery($query);

			if ($tags[$context] = $db->loadObjectList())
			{
				$query->clear()
					->select('COUNT(*)')
					->from($db->quoteName('#__easyshop_tag_items', 'a'));

				foreach ($tags[$context] as $tag)
				{
					$query->clear('where')
						->where('a.tag_id = ' . (int) $tag->id);
					$db->setQuery($query);
					$tag->tagCount = (int) ($db->loadResult() ?: 0);
					Translator::translateObject($tag, 'easyshop_tags', $tag->id);
				}
			}
		}

		return $tags[$context];
	}

	public function getProductTags($productId)
	{
		$productId = (int) $productId;
		static $tags = [];

		if (!isset($tags[$productId]))
		{
			$db    = easyshop('db');
			$query = $db->getQuery(true)
				->select('a.id, a.name, a.alias, a.language')
				->from($db->quoteName('#__easyshop_tags', 'a'))
				->join('INNER', $db->quoteName('#__easyshop_tag_items', 'a2') . ' ON a2.tag_id = a.id')
				->where('a.context = ' . $db->quote('com_easyshop.product') . ' AND a.state = 1 AND a2.item_id = ' . $productId);
			$db->setQuery($query);

			if ($tags[$productId] = $db->loadObjectList())
			{
				$query->clear()
					->select('COUNT(*)')
					->from($db->quoteName('#__easyshop_tag_items', 'a'));

				foreach ($tags[$productId] as $tag)
				{
					$query->clear('where')
						->where('a.tag_id = ' . (int) $tag->id);
					$db->setQuery($query);
					$tag->tagCount = (int) ($db->loadResult() ?: 0);
					Translator::translateObject($tag, 'easyshop_tags', $tag->id);
				}
			}
		}

		return $tags[$productId];
	}
}
