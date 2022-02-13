<?php
/**
 
 
 
 
 
 */

namespace Umart\Classes;

defined('_JEXEC') or die;

class Tags
{
	public function getTags($context = 'com_umart.product')
	{
		static $tags = [];

		if (!isset($tags[$context]))
		{
			$db    = plg_sytem_umart_main('db');
			$query = $db->getQuery(true)
				->select('a.id, a.name, a.alias, a.language')
				->from($db->quoteName('#__umart_tags', 'a'))
				->where('a.context = ' . $db->quote($context) . ' AND a.state = 1');
			$db->setQuery($query);

			if ($tags[$context] = $db->loadObjectList())
			{
				$query->clear()
					->select('COUNT(*)')
					->from($db->quoteName('#__umart_tag_items', 'a'));

				foreach ($tags[$context] as $tag)
				{
					$query->clear('where')
						->where('a.tag_id = ' . (int) $tag->id);
					$db->setQuery($query);
					$tag->tagCount = (int) ($db->loadResult() ?: 0);
					Translator::translateObject($tag, 'umart_tags', $tag->id);
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
			$db    = plg_sytem_umart_main('db');
			$query = $db->getQuery(true)
				->select('a.id, a.name, a.alias, a.language')
				->from($db->quoteName('#__umart_tags', 'a'))
				->join('INNER', $db->quoteName('#__umart_tag_items', 'a2') . ' ON a2.tag_id = a.id')
				->where('a.context = ' . $db->quote('com_umart.product') . ' AND a.state = 1 AND a2.item_id = ' . $productId);
			$db->setQuery($query);

			if ($tags[$productId] = $db->loadObjectList())
			{
				$query->clear()
					->select('COUNT(*)')
					->from($db->quoteName('#__umart_tag_items', 'a'));

				foreach ($tags[$productId] as $tag)
				{
					$query->clear('where')
						->where('a.tag_id = ' . (int) $tag->id);
					$db->setQuery($query);
					$tag->tagCount = (int) ($db->loadResult() ?: 0);
					Translator::translateObject($tag, 'umart_tags', $tag->id);
				}
			}
		}

		return $tags[$productId];
	}
}
