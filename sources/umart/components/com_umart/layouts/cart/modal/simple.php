<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\Cart;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var array $displayData */
$extractData  = plg_sytem_umart_main(Cart::class)->extractData();
$productLink  = '<a href="' . $displayData['item']['product']->link . '">' . $displayData['item']['product']->name . '</a>';
$checkoutLink = '<a href="' . Route::_(UmartHelperRoute::getCartRoute(), false) . '">' . Text::_('COM_UMART_VIEW_CART') . '</a>';
?>
<div data-cart-modal uk-modal="{center:true}" class="uk-modal" tabindex="-1">
    <div class="uk-modal-dialog uk-modal-body es-cart-modal-simple">
        <a class="uk-modal-close-default" data-uk-close></a>
        <h4>
            <span uk-icon="icon: cart"></span>
            <span data-cart-count class="uk-badge uk-badge-notification">
				<?php echo $extractData['count']; ?>
			</span>
			<?php echo Text::_('COM_UMART_YOUR_CART'); ?>
        </h4>
        <div data-cart-body>
            <p>
				<?php echo Text::sprintf('COM_UMART_CART_MODAL_SIMPLE_FORMAT', $productLink, $checkoutLink); ?>
            </p>
        </div>
    </div>
</div>
