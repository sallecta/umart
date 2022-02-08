<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use ES\Classes\User;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;
/**
 * @var array $displayData
 */
extract($displayData);
$maxQuantity  = (int) $product->params->get('product_detail_max_quantity', 0);
$showQuantity = $config->get('show_quantity_box', 1);
$buttonCart   = $config->get('cart_button_type', 'text_only');

// @since 1.1.0 Disable on zero
$disableOnZero = $config->get('disable_btn_on_zero', 0);
$currentPrice  = isset($product->cart['price']) ? $product->cart['price'] : $product->price;
$disabled      = $disableOnZero && $currentPrice < 0.01;

// @since 1.1.1 Direct checkout (Buy now)
$direct = $product->params->get('product_detail_direct', 0);
$text   = $direct ? Text::_('COM_EASYSHOP_BUY_NOW') : Text::_('COM_EASYSHOP_ADD_TO_CART');

// @since 1.2.3, check permission
$userClass = easyshop(User::class);
$groups    = $config->get('groups_can_add_to_cart', []);

// @since 1.3.0
$minQuantity = (int) $product->params->get('product_detail_min_quantity', 1);

?>

<?php if (empty($groups) || $userClass->accessGroups($groups, true)): ?>
    <div class="add-to-cart">
        <span data-product-warning class="uk-text-warning"></span>
        <div class="uk-flex">
            <div class="es-quantity<?php echo empty($showQuantity) ? ' uk-hidden' : ''; ?>">
                <input type="number"
                       min="<?php echo $minQuantity; ?>"
					<?php echo $maxQuantity > 0 ? ' max="' . $maxQuantity . '"' : ''; ?>
					<?php echo $maxQuantity == 1 ? ' readonly' : ''; ?>
                       value="<?php echo $product->cart['quantity'] >= $minQuantity ? $product->cart['quantity'] : $minQuantity; ?>"
                       data-product-quantity/>
                <div class="es-quantity-nav">
                    <div class="es-quantity-button es-quantity-up">+</div>
                    <div class="es-quantity-button es-quantity-down">-</div>
                </div>
            </div>
            <button type="button"
                    data-add-to-cart
                    data-disable-on-zero="<?php echo $disableOnZero; ?>"
                    class="btn-add-to-cart uk-button uk-button-primary <?php echo $buttonCart; ?><?php echo $disabled ? ' uk-disabled' : ''; ?>"
				<?php echo $disabled ? ' disabled="disabled"' : ''; ?>>
				<?php if ($buttonCart !== 'text_only'): ?>
                    <span uk-icon="icon: cart"></span>
				<?php endif; ?>
				<?php if ($buttonCart !== 'icon_only'): ?>
                    <div class="uk-display-inline-block">
						<?php echo $text; ?>
                    </div>
				<?php endif; ?>
            </button>
        </div>
    </div>
<?php endif; ?>
