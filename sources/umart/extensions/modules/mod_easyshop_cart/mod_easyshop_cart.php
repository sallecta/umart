<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use ES\Classes\Cart;
use ES\Classes\Currency;
use Joomla\CMS\Helper\ModuleHelper;

defined('_JEXEC') or die;
JLoader::register('EasyshopHelperRoute', ES_COMPONENT_SITE . '/helpers/route.php');
$cart           = easyshop(Cart::class);
$currency       = easyshop(Currency::class)->getActive();
$data           = $cart->extractData();
$layout         = $params->get('layout', 'default');
$moduleClassSfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

ob_start();
require ModuleHelper::getLayoutPath('mod_easyshop_cart', $layout . '_info');
$cartInfo = ob_get_clean();

require ModuleHelper::getLayoutPath('mod_easyshop_cart', $layout);
