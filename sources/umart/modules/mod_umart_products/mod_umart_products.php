<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

if ($params->get('prepare'))
{
	JPluginHelper::importPlugin('umart');
}

JLoader::register('ModUmartProductsHelper', __DIR__ . '/helper.php');
$items = ModUmartProductsHelper::getItems($params);

if (!empty($items))
{
	/**
	 * @var $config \Joomla\Registry\Registry
	 * @since 1.0.0
	 */
	$moduleClassSfx = htmlspecialchars($params->get('moduleclass_sfx'));
	$config         = clone umart('config');

	foreach ($params->toArray() as $name => $value)
	{
		if (!$config->exists($name) || trim($value) !== '')
		{
			$config->set($name, $value);
		}
	}

	$categories = ModUmartProductsHelper::getCategories();
	$layout     = $params->get('layout', 'default');

	require JModuleHelper::getLayoutPath('mod_umart_products', $layout);
}
