<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\Cart;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/**
 * @var array $displayData
 * @var string $layout
 */
extract($displayData);
$checkoutData  = easyshop(Cart::class)->getCheckoutData();
$userId        = CMSFactory::getUser()->id;
$disabledClass = [
	'login'    => $userId ? ' uk-disabled' : '',
	'checkout' => $userId < 1 && empty($checkoutData['guest']['email']) ? ' uk-disabled' : '',
	'confirm'  => empty($checkoutData['billing_address']) || empty($checkoutData['shipping_address']) ? ' uk-disabled' : '',
];

?>
<div id="product-checkout-navigation">
    <ul>
        <li class="<?php echo $layout == 'default' ? 'active' : ''; ?>">
            <a href="<?php echo Route::_(EasyshopHelperRoute::getCartRoute('default'), false); ?>">
				<?php echo Text::_('COM_EASYSHOP_CART'); ?>
                <span uk-icon="chevron-right"></span>
            </a>
        </li>
        <li class="<?php echo ($layout == 'checkout' ? 'active' : '') . $disabledClass['checkout']; ?>">
            <a href="<?php echo Route::_(EasyshopHelperRoute::getCartRoute('checkout'), false); ?>">
				<?php echo Text::_('COM_EASYSHOP_CHECKOUT_STEP'); ?>
                <span uk-icon="chevron-right"></span>
            </a>
        </li>
        <li class="<?php echo ($layout == 'confirm' ? 'active' : '') . $disabledClass['confirm']; ?>">
            <a href="<?php echo Route::_(EasyshopHelperRoute::getCartRoute('confirm'), false); ?>">
				<?php echo Text::_('COM_EASYSHOP_CONFIRM_STEP'); ?>
            </a>
        </li>
    </ul>
</div>
