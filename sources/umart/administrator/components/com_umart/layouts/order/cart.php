<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\Order;
use Joomla\CMS\Language\Text;

/**
 * @var array $displayData
 * @var Order $order
 */
extract($displayData);
$products = $order->products;
$action   = empty($noAction);
?>
<div class="es-order-item-data" data-order-cart>
    <div class="es-panel uk-overflow-auto">
        <table class="uk-table uk-table-divider uk-table-hover uk-table-small es-border es-input-100" data-panel>
            <thead>
            <tr>
                <th class="uk-table-expand@m">
					<?php echo Text::_('COM_UMART_PRODUCT'); ?>
                </th>
                <th class="uk-table-small uk-text-nowrap">
					<?php echo Text::_('COM_UMART_PRICE'); ?>
                </th>

				<?php if ($action): ?>
                    <th class="uk-text-center uk-table-small uk-text-nowrap">
						<?php echo Text::_('COM_UMART_TAXES'); ?>
                    </th>
				<?php endif; ?>

                <th class="uk-text-center uk-table-small uk-text-nowrap">
					<?php echo Text::_('COM_UMART_QUANTITY'); ?>
                </th>
                <th class="uk-text-<?php echo $action ? 'center' : 'right'; ?> uk-table-small uk-text-nowrap">
					<?php echo Text::_('COM_UMART_SUBTOTAL'); ?>
                </th>
				<?php if ($action): ?>
                    <th class="umartui_width-small"></th>
				<?php endif; ?>
            </tr>
            </thead>
            <tbody>
			<?php if (!empty($products)): ?>
				<?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <div>
								<?php echo htmlspecialchars($product->product_name); ?>
                            </div>
                        </td>
                        <td class="uk-text-nowrap">
                            <strong><?php echo $currency->toFormat($product->product_price); ?></strong>
							<?php if (!empty($product->options)): ?>
                                <ul class="uk-list uk-margin-remove">
									<?php foreach ($product->options as $option): ?>
                                        <li>
                                            <label class="uk-display-inline uk-text-bold">
												<?php echo $option->option_name . ': '; ?>
                                            </label>
											<?php echo $option->option_text; ?>
											<?php if ($option->option_price != 0): ?>
												<?php echo Text::sprintf('COM_UMART_PRICE_INCL_FORMAT', ' ' . ((float) $option->option_price > 0.00 ? '+' : '') . $currency->toFormat($option->option_price)); ?>
											<?php endif; ?>
                                        </li>
									<?php endforeach; ?>
                                </ul>
							<?php endif; ?>
                        </td>

						<?php if ($action): ?>
                            <td class="uk-text-center uk-text-nowrap">
								<?php echo $currency->toFormat($product->product_taxes); ?>
                            </td>
						<?php endif; ?>

                        <td class="uk-text-center uk-text-nowrap">
							<?php echo 'x' . $product->quantity; ?>
                        </td>
                        <td class="uk-text-<?php echo $action ? 'center' : 'right'; ?> uk-text-nowrap">
							<?php echo $currency->toFormat($product->total_price); ?>
                        </td>
						<?php if ($action): ?>
                            <td data-order-product-id="<?php echo $product->order_product_id; ?>">
                                <ul class="uk-iconnav">
                                    <li>
                                        <a href="#"
                                           uk-icon="icon: pencil" data-product-edit
                                           data-product-id="<?php echo (int) $product->product_id; ?>"></a>
                                    </li>
                                    <li>
                                        <a href="#"
                                           uk-icon="icon: trash" data-product-remove
                                           data-product-id="<?php echo (int) $product->product_id; ?>">
                                        </a>
                                    </li>
                                </ul>
                            </td>
						<?php endif; ?>
                    </tr>
				<?php endforeach; ?>
			<?php endif; ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="<?php echo $action ? 6 : 4; ?>" class="uk-padding-remove uk-clearfix">
                    <div class="product-cart-summary umartui_width-large@m umartui_width-medium@s uk-align-right@s">
                        <table class="uk-table uk-table-small uk-margin-remove">
                            <tbody>
                            <tr>
                                <th class="umartui_width-expand">
									<?php echo Text::_('COM_UMART_SUBTOTAL'); ?>
                                </th>
                                <td class="umartui_width-small">
									<?php echo $currency->toFormat($order->subTotal); ?>
                                </td>
                            </tr>

							<?php if ($order->total_shipping > 0.00): ?>
                                <tr>
                                    <th class="umartui_width-expand">
										<?php echo Text::_('COM_UMART_TOTAL_SHIPPING'); ?>
                                    </th>
                                    <td class="umartui_width-small">
										<?php echo $currency->toFormat($order->total_shipping); ?>
                                    </td>
                                </tr>
							<?php endif; ?>

							<?php if ($order->total_fee > 0.00): ?>
                                <tr>
                                    <th class="umartui_width-expand">
										<?php echo Text::_('COM_UMART_PAYMENT_FEE'); ?>
                                    </th>
                                    <td class="umartui_width-small">
										<?php echo $currency->toFormat($order->total_fee); ?>
                                    </td>
                                </tr>
							<?php endif; ?>

							<?php if ($fieldsPrice = $order->get('fieldsPriceDetails', [])): ?>
								<?php foreach ($fieldsPrice as $fieldPrice): ?>
                                    <tr>
                                        <th class="umartui_width-expand">
											<?php echo $fieldPrice->label; ?>
                                        </th>
                                        <td class="umartui_width-small">
											<?php echo $currency->toFormat($fieldPrice->price); ?>
                                        </td>
                                    </tr>
								<?php endforeach; ?>
							<?php endif; ?>

                            <?php if ($order->total_taxes > 0.00): ?>
                                <tr>
                                    <th class="umartui_width-expand">
			                            <?php echo Text::_('COM_UMART_TAXES'); ?>
                                    </th>
                                    <td class="umartui_width-small">
			                            <?php echo $currency->toFormat($order->total_taxes); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php if ($order->total_discount > 0.00): ?>
                                <tr>
                                    <th class="umartui_width-expand">
			                            <?php echo Text::_('COM_UMART_DISCOUNT'); ?>
                                    </th>
                                    <td class="umartui_width-small">
			                            <?php echo $currency->toFormat($order->total_discount); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <tr>
                                <th class="umartui_width-expand"></th>
                                <td class="uk-text-lead umartui_width-small">
									<?php echo $currency->toFormat($order->total_price); ?>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
