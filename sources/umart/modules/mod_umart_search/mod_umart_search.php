<?php
/**
 
 
 
 
 
 */

use Joomla\CMS\Component\ComponentHelper;

defined('_JEXEC') or die;

if (ComponentHelper::isEnabled('com_umart'))
{
	JLoader::register('ModUmartSearchHelper', __DIR__ . '/helper.php');
	ModUmartSearchHelper::loadFormLayout($params);
}
