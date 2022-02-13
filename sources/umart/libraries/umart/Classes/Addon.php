<?php
/**
 
 
 
 
 
 */

namespace Umart\Classes;
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Form\Form;

class Addon
{
	protected static $buffers = [];

	public function getData($context, $itemId, $asArray = false)
	{
		static $data = [];
		$itemId = (int) $itemId;

		if (!isset($data[$context]))
		{
			$data[$context] = [];
		}

		if (!isset($data[$context][$itemId]))
		{
			$db    = plg_sytem_umart_main('db');
			$query = $db->getQuery(true)
				->select('a.data')
				->from($db->quoteName('#__umart_params', 'a'))
				->where('a.context = ' . $db->quote($context))
				->where('a.item_id = ' . $itemId);
			$db->setQuery($query);
			$dataParams = $db->loadResult();
			$registry   = new Registry;

			if (!empty($dataParams))
			{
				$registry->loadString((string) $dataParams);
			}

			$data[$context][$itemId] = $registry;
		}

		return $asArray ? $data[$context][$itemId]->toArray() : $data[$context][$itemId];
	}

	public function getAddons($addOnName = 'product', $itemId = 0)
	{
		static $addons = null;

		if (null === $addons)
		{
			$addons  = [];
			$results = plg_sytem_umart_main('app')->triggerEvent('onUmartAddonRegister', [$addOnName]);

			if (!empty($results))
			{
				$language = CMSFactory::getLanguage();

				foreach (array_unique($results) as $element)
				{
					if (empty($element)
						|| !is_string($element)
					)
					{
						continue;
					}

					$manifest = JPATH_PLUGINS . '/umart/' . $element . '/' . $element . '.xml';

					if (is_file($manifest))
					{
						$language->load('plg_umart_' . $element, JPATH_PLUGINS . '/umart/' . $element . '/language');
						$form = new Form('plg_umart_' . $element . '.addon.' . $addOnName, ['control' => 'jform[addon][' . $element . ']']);

						if ($form->load(file_get_contents($manifest), true, 'addon/' . $addOnName))
						{
							$data = new \stdClass;

							if ($itemId)
							{
								$data = (object) $this->getData($element . '.' . $addOnName, $itemId, true);
							}

							$data->addOnName = $addOnName;
							plg_sytem_umart_main('app')->triggerEvent('onUmartAddonPrepareForm', [$form, $data]);
							$form->bind($data);
							$addons[$element] = $form;
						}
					}
				}
			}
		}

		return $addons;
	}

	public function setBuffer($element, $content, $override = false)
	{
		if (!isset(self::$buffers[$element]) || $override)
		{
			self::$buffers[$element] = $content;
		}

		return self::$buffers;
	}

	public function getBuffer($element, $default = null)
	{
		return isset(self::$buffers[$element])
			? self::$buffers[$element]
			: $default;
	}
}
