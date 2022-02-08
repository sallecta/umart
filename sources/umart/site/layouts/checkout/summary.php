<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use ES\Classes\Currency;
use ES\Classes\Cart;

/**
 * @var array    $displayData
 * @var Currency $currency
 * @var Cart     $cart
 */

$cart = easyshop(Cart::class);
$data = $cart->extractVendorData(true);

// @since 1.1.3
$coupons = [];

if (!empty($data['discounts']) && easyshop('config', 'show_coupons', 1))
{
	foreach ($data['discounts'] as $discount)
	{
		if ((int) $discount->type === 1)
		{
			$coupons[] = $discount;
		}
	}
}

?>
<div id="es-summary-wrap">
    <div class="es-summary-wrap uk-height-1-1">
        <div class="uk-card uk-card-small uk-card-body uk-background-default">
            <div class="es-cart-summary">
                <h3 class="es-panel-title">
                    <div class="uk-badge uk-badge-notification" data-items-count="<?php echo (int) $data['count']; ?>">
						<?php echo $data['count']; ?>
                    </div>
					<?php echo Text::_('COM_EASYSHOP_YOUR_CART'); ?>
                </h3>
                <div class="uk-margin">
					<?php foreach ($data['items'] as $item): ?>
                        <div class="es-cart-item uk-grid-small uk-flex-top" uk-grid>
							<?php if (!empty($item['product']->images[0]->tiny)): ?>
                                <div class="uk-width-auto">
									<?php echo $displayData['renderer']->render('media.image', [
										'image'      => $item['product']->images[0],
										'size'       => 'tiny',
										'attributes' => [
											'class' => 'uk-preserve-width uk-border-rounded',
											'alt'   => $item['product']->images[0]->title ?: $item['product']->name,
											'width' => '85',
										],
									]); ?>
                                </div>
							<?php endif; ?>
                            <div class="uk-width-expand">
                                <a href="<?php echo $item['product']->link; ?>" class="uk-link-reset">
									<?php echo $item['product']->name; ?>
                                    <strong>
										<?php echo $item['quantity'] . ' x ' . $displayData['currency']->toFormat($item['salePrice'], true); ?>
                                    </strong>
                                </a>
                                <div class="uk-inline">
									<?php $id = (int) $item['product']->id; ?>
									<?php $keyId = $item['key'] . $id; ?>
									<?php $maxQuantity = (int) $item['product']->params->get('product_detail_max_quantity', 0); ?>
                                    <ul class="uk-iconnav">
                                        <li>
                                            <a href="#"
                                               onclick="_es.cart.update('remove', <?php echo $id; ?>, 0, '<?php echo $item['key']; ?>'); return false;"
                                               uk-icon="icon: close">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#"
                                               onclick="_es.$(this).next().toggleClass('uk-hidden'); return false;"
                                               uk-icon="icon: pencil"></a>
                                            <input type="number"
                                                   min="<?php echo $item['product']->params->get('product_detail_min_quantity', 1); ?>"
												<?php echo $maxQuantity > 0 ? ' max="' . $maxQuantity . '"' : ''; ?>
												<?php echo $maxQuantity == 1 ? ' disabled' : ''; ?>
                                                   step="1"
                                                   class="product-quantity-box uk-input uk-form-small uk-form-width-small uk-position-center-right-out uk-hidden uk-margin-small-left"
                                                   value="<?php echo $item['quantity']; ?>"
                                                   onchange="_es.cart.update('update', <?php echo $id; ?>, this.value, '<?php echo $item['key']; ?>');"/>
                                        </li>
                                    </ul>
                                </div>
								<?php echo $displayData['renderer']->render('cart.options', ['options' => $item['options']]); ?>
                            </div>
                        </div>
					<?php endforeach; ?>
					<?php if ($coupons): ?>
                        <div id="es-discount-coupon" class="uk-text-small">
                            <div class="uk-heading-line uk-text-center uk-margin-small-top uk-margin-small-bottom">
                                <span><?php echo Text::_('COM_EASYSHOP_COUPON_PLURAL_MAYBE'); ?></span>
                            </div>
							<?php foreach ($coupons as $coupon): ?>
                                <div class="uk-grid-small uk-padding-remove uk-margin-remove" uk-grid>
                                    <div class="uk-width-expand uk-text-truncate"
                                         title="<?php echo htmlspecialchars($coupon->name); ?>" uk-tooltip>
										<?php echo $coupon->name; ?>
                                    </div>
                                    <div class="uk-text-right">
                                        <code><?php echo $coupon->coupon_code; ?></code>
                                        <a href="javascript: _es.cart.removeCoupon(<?php echo (int) $coupon->id; ?>);"
                                           class="uk-link-reset">
                                            <span class="uk-text-danger" uk-icon="icon: close"></span>
                                        </a>
                                    </div>
                                </div>
							<?php endforeach; ?>
                        </div>
					<?php endif; ?>
                </div>
                <div id="es-summary-amount-details">
                    <div class="uk-grid-small uk-padding-remove uk-margin-remove" uk-grid>
                        <div class="uk-width-expand" uk-leader="fill: .">
							<?php echo Text::_('COM_EASYSHOP_SUBTOTAL'); ?>
                        </div>
                        <div>
							<?php echo $displayData['currency']->toFormat($data['subTotal'], true); ?>
                        </div>
                    </div>

					<?php if ($data['totalShip'] > 0.00): ?>
                        <div id="es-shipping" class="uk-grid-small uk-padding-remove uk-margin-remove" uk-grid>
                            <div class="uk-width-expand" uk-leader="fill: .">
								<?php echo Text::_('COM_EASYSHOP_SHIPPING'); ?>
                            </div>
                            <div>
								<?php echo $displayData['currency']->toFormat($data['totalShip'], true); ?>
                            </div>
                        </div>
					<?php endif; ?>

					<?php if ($data['paymentFee'] > 0.00): ?>
                        <div id="es-payment-fee" class="uk-grid-small uk-padding-remove uk-margin-remove" uk-grid>
                            <div class="uk-width-expand" uk-leader="fill: .">
								<?php echo Text::_('COM_EASYSHOP_PAYMENT_FEE'); ?>
                            </div>
                            <div>
								<?php echo $displayData['currency']->toFormat($data['paymentFee'], true); ?>
                            </div>
                        </div>
					<?php endif; ?>

					<?php if (!empty($data['checkoutFieldsDetails'])): ?>
						<?php foreach ($data['checkoutFieldsDetails'] as $fieldDetail): ?>

                            <div class="es-checkout-field-detail uk-grid-small uk-padding-remove uk-margin-remove"
                                 uk-grid>
                                <div class="uk-width-expand" uk-leader="fill: .">
									<?php echo $fieldDetail['label']; ?>
                                </div>
                                <div>
									<?php echo $displayData['currency']->toFormat($fieldDetail['price'], true); ?>
                                </div>
                            </div>

						<?php endforeach; ?>
					<?php endif; ?>

	                <?php if ($data['totalTaxes'] > 0.00): ?>
                        <div class="uk-grid-small uk-padding-remove uk-margin-remove" uk-grid>
                            <div class="uk-width-expand" uk-leader="fill: .">
				                <?php echo Text::_('COM_EASYSHOP_TAXES'); ?>
                            </div>
                            <div>
				                <?php echo $displayData['currency']->toFormat($data['totalTaxes'], true); ?>
                            </div>
                        </div>
	                <?php endif; ?>

	                <?php if ($data['orderDiscount'] > 0.00): ?>
                        <div class="uk-grid-small uk-padding-remove uk-margin-remove" uk-grid>
                            <div class="uk-width-expand" uk-leader="fill: .">
				                <?php echo Text::_('COM_EASYSHOP_ORDER_DISCOUNT'); ?>
                            </div>
                            <div>
				                <?php echo $displayData['currency']->toFormat($data['orderDiscount'], true); ?>
                            </div>
                        </div>
	                <?php endif; ?>

                    <div id="es-grand-total" class="uk-text-lead uk-text-bold uk-text-right">
						<?php echo $displayData['currency']->toFormat($data['grandTotal'], true); ?>
                    </div>
                </div>
            </div>

			<?php if (isset($displayData['checkoutFieldsDisplay'])): ?>
                <div class="es-checkout-fields uk-margin">
					<?php echo $displayData['checkoutFieldsDisplay']; ?>
                </div>
			<?php endif; ?>

			<?php if (!empty($displayData['note'])): ?>
                <div class="es-order-note">
					<?php echo $displayData['note']; ?>
                </div>
			<?php endif; ?>
        </div>
    </div>
</div>
