<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\Cart;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/**
 * @var array $displayData
 * @var string $layout
 */
extract($displayData);
$checkoutData  = plg_sytem_umart_main(Cart::class)->getCheckoutData();
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
            <a href="<?php echo Route::_(UmartHelperRoute::getCartRoute('default'), false); ?>">
				<?php echo Text::_('COM_UMART_CART'); ?>
                <span uk-icon="chevron-right"></span>
            </a>
        </li>
        <li class="<?php echo ($layout == 'checkout' ? 'active' : '') . $disabledClass['checkout']; ?>">
            <a href="<?php echo Route::_(UmartHelperRoute::getCartRoute('checkout'), false); ?>">
				<?php echo Text::_('COM_UMART_CHECKOUT_STEP'); ?>
                <span uk-icon="chevron-right"></span>
            </a>
        </li>
        <li class="<?php echo ($layout == 'confirm' ? 'active' : '') . $disabledClass['confirm']; ?>">
            <a href="<?php echo Route::_(UmartHelperRoute::getCartRoute('confirm'), false); ?>">
				<?php echo Text::_('COM_UMART_CONFIRM_STEP'); ?>
            </a>
        </li>
    </ul>
</div>
