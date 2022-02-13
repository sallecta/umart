<?php
/**
 
 
 
 
 
 */

use Umart\Classes\Cart;
use Umart\Classes\Currency;
use Joomla\CMS\Helper\ModuleHelper;

defined('_JEXEC') or die;
JLoader::register('UmartHelperRoute', UMART_COMPONENT_SITE . '/helpers/route.php');
$cart           = umart(Cart::class);
$currency       = umart(Currency::class)->getActive();
$data           = $cart->extractData();
$layout         = $params->get('layout', 'default');
$moduleClassSfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

ob_start();
require ModuleHelper::getLayoutPath('mod_umart_cart', $layout . '_info');
$cartInfo = ob_get_clean();

require ModuleHelper::getLayoutPath('mod_umart_cart', $layout);
