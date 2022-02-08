<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\Product;
use Joomla\CMS\Language\Text;

/**
 * @var array   $displayData
 * @var Product $productClass
 */

extract($displayData);
$products     = $order->products;
$productClass = easyshop(Product::class);
?>
<div class="items">
    <table
            style="background-color: transparent; border-collapse: collapse; border-spacing: 0; max-width: 100%; width: 100%;">
        <thead>
        <tr>
            <th width="5%"
                style="border-bottom: 1px solid #eee; padding: 5px; padding-bottom: 8px; text-align: center;">
                #
            </th>
            <th width="55%"
                style="border-bottom: 1px solid #eee; padding: 5px; padding-bottom: 8px; text-align: left;">
				<?php echo Text::_('COM_EASYSHOP_PRODUCT'); ?>
            </th>
            <th width="15%"
                style="border-bottom: 1px solid #eee; padding: 5px; padding-bottom: 8px; text-align: right;">
				<?php echo Text::_('COM_EASYSHOP_PRICE'); ?>
            </th>
            <th width="10%"
                style="border-bottom: 1px solid #eee; padding: 5px; padding-bottom: 8px; text-align: right;">
				<?php echo Text::_('COM_EASYSHOP_QUANTITY'); ?>
            </th>
            <th width="15%"
                style="border-bottom: 1px solid #eee; padding: 5px; padding-bottom: 8px; text-align: right;">
				<?php echo Text::_('COM_EASYSHOP_SUBTOTAL'); ?>
            </th>
        </tr>
        </thead>
        <tbody>
		<?php if (!empty($products)): ?>
			<?php foreach ($products as $product):

				$images = $productClass->getImages($product->product_id);

				if ($images && isset($images[0]) && is_file(ES_MEDIA . '/' . $images[0]->file_path))
				{
					$image = 'data:' . $images[0]->mime_type . ';base64, ' . base64_encode(file_get_contents(ES_MEDIA . '/' . $images[0]->file_path));
				}
				else
				{
					$image = null;
				}

				?>
                <tr>
                    <td style="border: 1px solid #eee; padding: 5px; text-align: center; vertical-align: top;">
						<?php if ($image): ?>
                            <img src="<?php echo $image; ?>" alt="" width="32"/>
                        <?php else: ?>
                            <?php echo $product->sku; ?>
						<?php endif; ?>
                    </td>
                    <td style="border: 1px solid #eee; padding: 5px; text-align: left; vertical-align: top;">
						<?php echo htmlspecialchars($product->product_name); ?>

                        <?php if ($image && $product->sku): ?>
                            <span style="font-size: 11px">
                                <?php echo ' | #' . $product->sku; ?>
                            </span>
                        <?php endif; ?>

						<?php if (!empty($product->options)): ?>
                            <ul>
								<?php foreach ($product->options as $option):

									if (trim($option->option_value) === '')
									{
										continue;
									}

									?>
                                    <li>
										<?php echo $option->option_name . ': ' . $option->option_value; ?>
                                    </li>
								<?php endforeach; ?>
                            </ul>
						<?php endif; ?>
                    </td>
                    <td style="border: 1px solid #eee; padding: 5px; text-align: right; vertical-align: top;">
						<?php echo $order->currency->toFormat($product->product_price); ?>
                    </td>
                    <td style="border: 1px solid #eee; padding: 5px; text-align: right; vertical-align: top;">
						<?php echo $product->quantity; ?>
                    </td>
                    <td style="border: 1px solid #eee; padding: 5px; text-align: right; vertical-align: top;"><?php echo $order->currency->toFormat($product->total_price); ?></td>
                </tr>
			<?php endforeach; ?>
		<?php endif; ?>
        </tbody>
        <tfoot>
        <tr style="background-color: #f8f8f8!important; border: 1px solid #e5e5e5; padding-top: 8px;">
            <td colspan="4"
                style="padding: 5px 10px; text-align: right; font-weight: bold; border-right: 1px solid #e5e5e5;">
				<?php echo Text::_('COM_EASYSHOP_SUBTOTAL'); ?>
            </td>
            <td style="text-align: right">
				<?php echo $order->currency->toFormat($order->subTotal); ?>
            </td>
        </tr>

		<?php if ($order->total_shipping > 0.00): ?>
            <tr style="background-color: #f8f8f8!important; border: 1px solid #e5e5e5;">
                <td colspan="4"
                    style="padding: 5px 10px; text-align: right; font-weight: bold; border-right: 1px solid #e5e5e5;">
					<?php echo Text::_('COM_EASYSHOP_TOTAL_SHIPPING'); ?>
                </td>
                <td style="text-align: right">
					<?php echo $order->currency->toFormat($order->total_shipping); ?>
                </td>
            </tr>
		<?php endif; ?>

		<?php if ($order->total_fee > 0.00): ?>
            <tr style="background-color: #f8f8f8!important; border: 1px solid #e5e5e5;">
                <td colspan="4"
                    style="padding: 5px 10px; text-align: right; font-weight: bold; border-right: 1px solid #e5e5e5;">
					<?php echo Text::_('COM_EASYSHOP_PAYMENT_FEE'); ?>
                </td>
                <td style="text-align: right">
					<?php echo $order->currency->toFormat($order->total_fee); ?>
                </td>
            </tr>
		<?php endif; ?>

		<?php if ($fieldsPrice = $order->get('fieldsPriceDetails', [])): ?>
			<?php foreach ($fieldsPrice as $fieldPrice): ?>
                <tr style="background-color: #f8f8f8!important; border: 1px solid #e5e5e5;">
                    <td colspan="4"
                        style="padding: 5px 10px; text-align: right; font-weight: bold; border-right: 1px solid #e5e5e5;">
						<?php echo $fieldPrice->label; ?>
                    </td>
                    <td style="text-align: right">
						<?php echo $order->currency->toFormat($fieldPrice->price); ?>
                    </td>
                </tr>
			<?php endforeach; ?>
		<?php endif; ?>

        <?php if ($order->total_taxes > 0.00): ?>
            <tr style="background-color: #f8f8f8!important; border: 1px solid #e5e5e5;">
                <td colspan="4"
                    style="padding: 5px 10px; text-align: right; font-weight: bold; border-right: 1px solid #e5e5e5;">
			        <?php echo Text::_('COM_EASYSHOP_TAXES'); ?>
                </td>
                <td style="text-align: right">
			        <?php echo $order->currency->toFormat($order->total_taxes); ?>
                </td>
            </tr>
        <?php endif; ?>

        <?php if ($order->total_discount > 0.00): ?>
            <tr style="background-color: #f8f8f8!important; border: 1px solid #e5e5e5;">
                <td colspan="4"
                    style="padding: 5px 10px; text-align: right; font-weight: bold; border-right: 1px solid #e5e5e5;">
			        <?php echo Text::_('COM_EASYSHOP_DISCOUNT'); ?>
                </td>
                <td style="text-align: right">
			        <?php echo $order->currency->toFormat($order->total_discount); ?>
                </td>
            </tr>
        <?php endif; ?>

        <tr style="background-color: #f8f8f8!important; border: 1px solid #e5e5e5;">
            <td colspan="4"
                style="padding: 5px 10px; text-align: right; font-weight: bold; border-right: 1px solid #e5e5e5;">
				<?php echo Text::_('COM_EASYSHOP_GRAND_TOTAL'); ?>
            </td>
            <td style="text-align: right">
				<?php echo $order->currency->toFormat($order->total_price); ?>
            </td>
        </tr>
        </tfoot>
    </table>
	<?php if (!empty($note)): ?>
        <p>
            <strong><?php echo Text::_('COM_EASYSHOP_ORDER_NOTE'); ?>:</strong>
			<?php echo $note; ?>
        </p>
	<?php endif; ?>
</div>
