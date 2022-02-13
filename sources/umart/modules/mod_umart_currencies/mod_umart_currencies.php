<?php
/**
 
 
 
 
 
 */

use Umart\Classes\Currency;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

/** @var $currency Currency */

$moduleClassSfx = htmlspecialchars($params->get('moduleclass_sfx'));
$currency       = umart(Currency::class);

if ($currency->isMultiMode())
{
	require ModuleHelper::getLayoutPath('mod_umart_currencies', $params->get('layout', 'default'));
}
else
{
	echo '<p>' . Text::_('MOD_UMART_CURRENCIES_WARNING_NO_MULTI_MODE') . '</p>';
}
