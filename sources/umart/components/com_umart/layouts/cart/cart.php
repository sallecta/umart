<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

/** @var array $displayData */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$data       = $displayData['extractData'];
$config     = plg_sytem_umart_main('config');
$showQtyBox = $config->get('show_quantity_box', 1);
$zeroAsFree = $config->get('zero_as_free', 0);

?>
<div class="uk-card uk-card-body uk-card-small uk-margin">
	<?php if (!empty($displayData['shopInfo']) && $displayData['shopInfo']['shopName']): ?>
        <article class="uk-article">
            <div class="uk-grid-small" uk-grid>
				<?php if (!empty($displayData['shopInfo']['shopLogo'])): ?>
                    <div class="umartui_width-small">
                        <img src="<?php echo UMART_MEDIA_URL . '/' . $displayData['shopInfo']['shopLogo']; ?>"
                             alt="<?php echo htmlspecialchars($displayData['shopInfo']['shopName'], ENT_COMPAT, 'UTF-8'); ?>"/>
                    </div>
				<?php endif; ?>
                <div class="umartui_width-auto">
                    <h4 class="uk-margin-remove uk-h4">
						<?php echo $displayData['shopInfo']['shopName']; ?>
                    </h4>
                    <div class="uk-article-lead">
						<?php if (!empty($displayData['shopInfo']['shopAddressFormat'])): ?>
                            <i class="fa fa-map-marker"></i>
							<?php echo $displayData['shopInfo']['shopAddressFormat']; ?> <br/>
						<?php endif; ?>

						<?php if ($displayData['shopInfo']['shopEmail']): ?>
                            <i class="fa fa-envelope"></i>
							<?php echo Text::_('COM_UMART_EMAIL') . ': ' . $displayData['shopInfo']['shopEmail'] . '<br/>'; ?>
						<?php endif; ?>

						<?php if ($displayData['shopInfo']['shopTelephone']): ?>
                            <i class="fa fa-phone"></i>
							<?php echo Text::_('COM_UMART_TELEPHONE') . ': ' . $displayData['shopInfo']['shopTelephone'] . '<br/>'; ?>
						<?php endif; ?>

						<?php if ($displayData['shopInfo']['shopMobile']): ?>
                            <i class="fa fa-mobile"></i>
							<?php echo Text::_('COM_UMART_MOBILE') . ': ' . $displayData['shopInfo']['shopMobile'] . '<br/>'; ?>
						<?php endif; ?>

						<?php if ($displayData['shopInfo']['shopFax']): ?>
                            <i class="fa fa-fax"></i>
							<?php echo Text::_('COM_UMART_FAX') . ': ' . $displayData['shopInfo']['shopFax'] . '<br/>'; ?>
						<?php endif; ?>
                    </div>
                </div>
            </div>
        </article>
        <hr class="uk-divider-icon"/>
	<?php endif; ?>
    <h4>
        <span uk-icon="icon: cart"></span>
        <div data-cart-count class="uk-badge uk-badge-notification">
			<?php echo $data['count']; ?>
        </div>
		<?php echo Text::_('COM_UMART_YOUR_CART'); ?>
    </h4>
    <div data-cart-body>
        <div data-cart-items class="product-cart-items">
            <table class="uk-table uk-table-small uk-table-middle uk-table-divider uk-table-responsive">
                <thead>
                <tr>
                    <th class="uk-table-expand" colspan="2">
						<?php echo Text::_('COM_UMART_PRODUCT'); ?>
                    </th>
                    <th class="uk-table-shrink uk-text-nowrap">
						<?php echo Text::_('COM_UMART_PRICE'); ?>
                    </th>
                    <th class="uk-table-shrink uk-text-nowrap">
						<?php echo Text::_('COM_UMART_SUB_TOTAL'); ?>
                    </th>
                </tr>
                </thead>
                <tbody>
				<?php foreach ($data['items'] as $item):
					$id = (int) $item['product']->id;
					$keyId = $item['key'] . $id;
					$image = null;

					if (!empty($item['image']))
					{
						$image = $item['image'];
					}
                    elseif (!empty($item['product']->images[0]))
					{
						$image = $item['product']->images[0];
					}

					$maxQuantity = (int) $item['product']->params->get('product_detail_max_quantity', 0);
					?>
                    <tr data-cart-row>
                        <td class="uk-table-shrink">
							<?php if ($image): ?>
								<?php echo $displayData['renderer']->render('media.image', [
									'image'      => $item['product']->images[0],
									'size'       => 'tiny',
									'attributes' => [
										'class' => 'uk-preserve-width uk-border-rounded',
										'alt'   => $item['product']->images[0]->title ?: $item['product']->name,
										'width' => '85',
									],
								]); ?>

							<?php endif; ?>
                        </td>
                        <td>
                            <div class="product-cart-info">
                                <a href="<?php echo $item['product']->link; ?>"
                                   class="uk-link-reset umartui_width-medium product-cart-name">
									<?php echo $item['product']->name; ?>
                                    <span class="uk-text-meta uk-visible@m"><?php echo 'x' . $item['quantity']; ?></span>
                                </a>
                                <div class="uk-inline">
                                    <ul class="uk-iconnav">
                                        <li>
                                            <a href="#"
                                               onclick="_umart.cart.update('remove', <?php echo $id; ?>, 0, '<?php echo $item['key']; ?>'); return false;"
                                               uk-icon="icon: close">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#"
                                               onclick="_umart.$(this).next().toggleClass('uk-hidden'); return false;"
                                               uk-icon="icon: pencil"></a>
                                            <input type="number"
                                                   min="<?php echo $item['product']->params->get('product_detail_min_quantity', 1) ?>"
												<?php echo $maxQuantity > 0 ? ' max="' . $maxQuantity . '"' : ''; ?>
												<?php echo $maxQuantity == 1 ? ' disabled' : ''; ?>
                                                   step="1"
                                                   class="product-quantity-box uk-hidden uk-input uk-form-small uk-form-width-small uk-position-center-right-out uk-margin-small-left"
                                                   value="<?php echo $item['quantity']; ?>"
                                                   onchange="_umart.cart.update('update', <?php echo $id; ?>, this.value, '<?php echo $item['key']; ?>');"/>
                                        </li>
                                    </ul>
                                </div>
								<?php echo $displayData['renderer']->render('cart.options', ['options' => $item['options']]); ?>
                            </div>
                        </td>
                        <td class="uk-text-nowrap">
                            <div class="product-cart-price">
                                <div class="product-cart-price-origin">
									<?php echo $displayData['currency']->toFormat($item['price'], true); ?>
                                    <span class="uk-text-meta uk-hidden@m"><?php echo 'x' . $item['quantity']; ?></span>
                                </div>

								<?php if ($item['discountAmount'] > 0.00): ?>
									<?php echo Text::_('COM_UMART_DISCOUNT'); ?>
									<?php echo $displayData['currency']->toFormat($item['discountAmount'], true); ?>
                                    <div class="product-cart-sale-price">
										<?php echo Text::_('COM_UMART_SALE_PRICE'); ?>
                                        <strong><?php echo $displayData['currency']->toFormat($item['salePrice'], true); ?></strong>
                                    </div>
								<?php endif; ?>
                            </div>
                        </td>
                        <td class="uk-text-nowrap">
                            <div class="product-cart-price-total umartui_width-1-5@s uk-text-right@m uk-text-bold">
								<?php echo $displayData['currency']->toFormat($item['subTotal'], true); ?>
                            </div>
                        </td>
                    </tr>
				<?php endforeach; ?>
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="4">
                        <div class="uk-grid-small uk-child-width-1-2@s uk-margin-top" uk-grid>
                            <div>
								<?php if ($config->get('show_coupons', '1')): ?>
									<?php echo $displayData['renderer']->render('coupon.coupon'); ?>
								<?php endif; ?>
                            </div>
                            <div>
                                <div class="product-cart-summary">
                                    <div class="uk-text-right">
                                        <div class="uk-grid uk-grid-collapse uk-child-width-1-2@s">
                                            <div>
												<?php echo Text::_('COM_UMART_SUBTOTAL'); ?>
                                            </div>
                                            <div>
												<?php echo $displayData['currency']->toFormat($data['subTotal'], true); ?>
                                            </div>
                                        </div>
										<?php if ($data['totalShip'] > 0.00): ?>
                                            <div class="uk-grid uk-grid-collapse uk-child-width-1-2@s">
                                                <div>
													<?php echo Text::_('COM_UMART_SHIPPING'); ?>
                                                </div>
                                                <div>
													<?php echo $displayData['currency']->toFormat($data['totalShip'], true); ?>
                                                </div>
                                            </div>
										<?php endif; ?>

										<?php if (!empty($data['paymentFee'] > 0.00)): ?>
                                            <div class="uk-grid uk-grid-collapse uk-child-width-1-2@s">
                                                <div>
													<?php echo Text::_('COM_UMART_PAYMENT_FEE'); ?>
                                                </div>
                                                <div>
													<?php echo $displayData['currency']->toFormat($data['paymentFee'], true); ?>
                                                </div>
                                            </div>
										<?php endif; ?>

										<?php if ($data['totalTaxes'] > 0.00): ?>
                                            <div class="uk-grid uk-grid-collapse uk-child-width-1-2@s">
                                                <div>
													<?php echo Text::_('COM_UMART_TAXES'); ?>
                                                </div>
                                                <div>
													<?php echo $displayData['currency']->toFormat($data['totalTaxes'], true); ?>
                                                </div>
                                            </div>
										<?php endif; ?>

										<?php if ($data['orderDiscount'] > 0.00): ?>
                                            <div class="uk-grid uk-grid-collapse uk-child-width-1-2@s">
                                                <div>
													<?php echo Text::_('COM_UMART_ORDER_DISCOUNT'); ?>
                                                </div>
                                                <div>
													<?php echo $displayData['currency']->toFormat($data['orderDiscount'], true); ?>
                                                </div>
                                            </div>
										<?php endif; ?>

                                        <div class="uk-grid uk-grid-collapse uk-child-width-1-1">
                                            <div class="uk-text-lead">
												<?php echo $displayData['currency']->toFormat($data['grandTotal'], true); ?>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <a href="<?php echo Route::_(UmartHelperRoute::getCartRoute('login'), false); ?>"
                                   class="uk-button uk-button-primary uk-float-right btn-checkout uk-margin-small-bottom uk-margin-small-top"
                                   data-cart-vendor-id="<?php echo $displayData['vendorId']; ?>">
									<?php echo Text::_('COM_UMART_CHECKOUT'); ?>
                                    <span uk-icon="icon: arrow-right"></span>
                                </a>
                                <div class="uk-clearfix"></div>
                            </div>
                        </div>
                    </td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
