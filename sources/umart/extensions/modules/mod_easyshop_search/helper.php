<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;
JLoader::register('EasyshopHelperRoute', ES_COMPONENT_SITE . '/helpers/route.php');

class ModEasyshopSearchHelper
{
	public static function loadFormLayout($params)
	{
		$form = new Form('com_easyshop.search');
		$form->loadFile(ES_COMPONENT_SITE . '/models/forms/search.xml');

		if ($params->get('search_by_category', '1'))
		{
			if ($base = (int) $params->get('base_category_id', 0))
			{
				$form->setFieldAttribute('category', 'parent_id', $base);
			}
		}
		else
		{
			$form->removeField('category');
		}

		if (!$params->get('search_by_brand', '1'))
		{
			$form->removeField('brand');
		}

		if (!$params->get('range_by_price', '1'))
		{
			$form->removeField('range');
		}

		$app  = easyshop('app');
		$data = [];

		foreach ($app->input->getArray() as $name => $value)
		{
			if ($form->getField($name))
			{
				if (in_array($name, ['category', 'brand', 'range'])
					&& strpos($value, '|') !== false
				)
				{
					$value = explode('|', $value);
					$value = $value[0];
				}

				$data[$name] = $value;
			}
		}

		$app->triggerEvent('onEasyshopPrepareSearchForm', [$form, $data]);
		$form->bind($data);
		$action         = Route::_(EasyshopHelperRoute::getSearchRoute(['task' => 'search']), false);
		$uri            = Uri::getInstance($action);
		$hidden         = $uri->getQuery(true);
		$moduleClassSfx = htmlspecialchars($params->get('moduleclass_sfx'));

		require ModuleHelper::getLayoutPath('mod_easyshop_search', $params->get('layout', 'default'));
	}
}
