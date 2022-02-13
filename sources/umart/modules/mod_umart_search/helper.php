<?php
/**
 
 
 
 
 
 */

use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;
JLoader::register('UmartHelperRoute', UMART_COMPONENT_SITE . '/helpers/route.php');

class ModUmartSearchHelper
{
	public static function loadFormLayout($params)
	{
		$form = new Form('com_umart.search');
		$form->loadFile(UMART_COMPONENT_SITE . '/models/forms/search.xml');

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

		$app  = umart('app');
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

		$app->triggerEvent('onUmartPrepareSearchForm', [$form, $data]);
		$form->bind($data);
		$action         = Route::_(UmartHelperRoute::getSearchRoute(['task' => 'search']), false);
		$uri            = Uri::getInstance($action);
		$hidden         = $uri->getQuery(true);
		$moduleClassSfx = htmlspecialchars($params->get('moduleclass_sfx'));

		require ModuleHelper::getLayoutPath('mod_umart_search', $params->get('layout', 'default'));
	}
}
