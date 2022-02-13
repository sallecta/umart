<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\Utility;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * @var array $displayData
 */
extract($displayData);
$document = plg_sytem_umart_main('doc');
$products = $order->get('products');
$utility  = plg_sytem_umart_main(Utility::class);
$billTo   = $utility->formatAddress($order->address['billing']);
$shipTo   = $utility->formatAddress($order->address['shipping']);
?>
<!DOCTYPE html>
<html lang="<?php echo $document->language; ?>" dir="<?php echo $document->direction; ?>">
<head>
    <meta charset="utf-8"/>
    <title><?php echo htmlspecialchars($displayData['pageTitle']); ?></title>
    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 0;
            padding: 10mm;
            background: #f8f8f8;
        }

        table {
            width: 100%;
            font-weight: normal;
            border-collapse: collapse;
            border: 0;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .image-section,
        .title,
        .text {
            border-collapse: collapse;
            border: 0;
            margin: 0;
            -webkit-text-size-adjust: none;
            color: #555559;
            font-family: Arial, sans-serif;
            font-size: 16px;
            line-height: 19px;
        }

        .image-section {
            background-color: #fff;
            border-bottom-width: 3px;
            border-bottom-style: solid;
            border-image: linear-gradient(to right, black, rgba(0, 0, 0, 0)) 1 100%;
        }

        .image-section td {
            padding: 10px;
            vertical-align: top;
        }

        .title {
            padding: 20px;
            vertical-align: top;
            background-color: white;
            border-top: none;
        }

        .text table {
            color: #555559;
            line-height: 19px;
            font-size: 15px;
        }

        .items-table {
            color: #555559;
            line-height: 19px;
            border: 1px solid #ddd;
            margin-top: 10px;
            width: 100%
        }

        .items-table th {
            padding: 8px 10px;
            border: 1px solid #ddd;
            width: 10px;
        }

        .items-table td {
            padding: 8px 10px;
            border: 1px solid #ddd;
        }

        .shop-name {
            font-weight: bold;
            font-size: 18px;
        }

        .order-table td:first-child {
            width: 150px;
        }

        .order-table td:nth-child(2) {
            width: 15px;
        }

        @page {
            size: A4;
            padding: 5px;
        }

        @media print {
            html, body {
                width: 210mm;
                height: 296mm;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body link="#00a5b5" vlink="#00a5b5" alink="#00a5b5">
<table>
    <tr>
        <td colspan="4" valign="top" class="image-section">
            <table>
                <tr>
					<?php if ($shopLogo): ?>
                        <td>
                            <img src="<?php echo Uri::root() . 'media/com_umart/' . $shopLogo; ?>" alt=""
                                 width="125"/>
                        </td>
					<?php endif; ?>
                    <td<?php echo $shopLogo ? '' : ' colspan="2"'; ?>>
						<?php if ($shopName): ?>
                            <div class="shop-name">
								<?php echo $shopName; ?>
                            </div>
						<?php endif; ?>

						<?php if ($shopWebsite): ?>
							<?php echo Text::_('COM_UMART_WEBSITE') . ': ' . $shopWebsite . '<br/>'; ?>
						<?php endif; ?>

						<?php if ($shopAddressFormat): ?>
							<?php echo Text::_('COM_UMART_ADDRESS') . ': ' . $shopAddressFormat . '<br/>'; ?>
						<?php endif; ?>

						<?php if ($shopEmail): ?>
							<?php echo Text::_('COM_UMART_EMAIL') . ': ' . $shopEmail . '<br/>'; ?>
						<?php endif; ?>

						<?php if ($shopTelephone): ?>
							<?php echo Text::_('COM_UMART_TELEPHONE') . ': ' . $shopTelephone . '<br/>'; ?>
						<?php endif; ?>

						<?php if ($shopMobile): ?>
							<?php echo Text::_('COM_UMART_MOBILE') . ': ' . $shopMobile . '<br/>'; ?>
						<?php endif; ?>

						<?php if ($shopFax): ?>
							<?php echo Text::_('COM_UMART_FAX') . ': ' . $shopFax . '<br/>'; ?>
						<?php endif; ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td valign="top" class="title">
            <table>
                <tr>
                    <td class="text">
                        <div style="line-height: 1.5">
							<?php echo Text::_('COM_UMART_DEAR'); ?>
                            <strong><?php echo $order->customerName; ?></strong>,
                            <div style="margin-top: 15px">
								<?php echo Text::sprintf('COM_UMART_PRINT_ORDER_DESC_FORMAT', $order->order_code); ?>
                            </div>
                        </div>
                        <p>
                        <address>
                            <strong><?php echo Text::_('COM_UMART_BILL_TO'); ?></strong>
							<?php echo $billTo; ?>
                        </address>
                        <address>
                            <strong><?php echo Text::_('COM_UMART_SHIP_TO'); ?></strong>
							<?php echo $shipTo; ?>
                        </address>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <hr size="1" color="#ddd" style="width: 560px;">
                    </td>
                </tr>
                <tr>
                    <td class="text">
                        <div style="line-height: 1.5">
                            <table class="order-table">
                                <tbody>
                                <tr>
                                    <td>
										<?php echo Text::_('COM_UMART_ORDER_CODE'); ?>
                                    </td>
                                    <td>:</td>
                                    <td>
										<?php echo $order->order_code; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
										<?php echo Text::_('COM_UMART_ORDER_DATE'); ?>
                                    </td>
                                    <td>:</td>
                                    <td>
										<?php echo $utility->displayDate($order->created_date, false); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo Text::_('COM_UMART_ORDER_STATUS'); ?></td>
                                    <td>:</td>
                                    <td><?php echo $order->getStatusText(); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo Text::_('COM_UMART_PAYMENT_STATUS'); ?></td>
                                    <td>:</td>
                                    <td><?php echo $order->getPaymentText(); ?></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <hr size="1" color="#ddd" style="width: 560px;">
                    </td>
                </tr>
				<?php if (!empty($products)): ?>
                    <tr>
                        <td class="text">
							<?php echo Text::_('COM_UMART_ORDER_DETAIL'); ?>:
                            <table class="items-table">
                                <tr>
                                    <th>#</th>
                                    <td>
										<?php echo Text::_('COM_UMART_PRODUCT_NAME'); ?>
                                    </td>
                                    <td>
										<?php echo Text::_('COM_UMART_PRICE'); ?>
                                    </td>
                                    <td>
										<?php echo Text::_('COM_UMART_QUANTITY'); ?>
                                    </td>
                                    <td>
										<?php echo Text::_('COM_UMART_SUBTOTAL'); ?>
                                    </td>
                                </tr>
								<?php foreach ($products as $i => $product): ?>
                                    <tr>
                                        <th><?php echo sprintf('%02d', ($i + 1)); ?></th>
                                        <td>
											<?php echo $product->product_name; ?>
                                        </td>
                                        <td>
											<?php echo $order->currency->toFormat($product->product_price); ?>
											<?php if (!empty($product->options)): ?>
												<?php foreach ($product->options as $option): ?>
													<?php
													$text = $option->option_name . ': ' . $option->option_text;

													if ($option->option_price != 0)
													{
														$text .= Text::sprintf('COM_UMART_PRICE_INCL_FORMAT', ' ' . ((float) $option->option_price > 0.00 ? '+' : '') . $order->currency->toFormat($option->option_price));
													}

													echo '<br/>' . $text;
													?>
												<?php endforeach; ?>
											<?php endif; ?>
                                        </td>
                                        <td>
											<?php echo 'x' . $product->quantity; ?>
                                        </td>
                                        <td>
											<?php echo $order->currency->toFormat($product->total_price); ?>
                                        </td>
                                    </tr>
								<?php endforeach; ?>
                                <tr>
                                    <td colspan="4" align="right">
										<?php echo Text::_('COM_UMART_SUBTOTAL'); ?>
                                    </td>
                                    <td>
										<?php echo $order->currency->toFormat($order->subTotal); ?>
                                    </td>
                                </tr>
								<?php if ($order->total_shipping > 0.00): ?>
                                    <tr>
                                        <td colspan="4" align="right">
											<?php echo Text::_('COM_UMART_SHIPPING'); ?>
                                        </td>
                                        <td>
											<?php echo $order->currency->toFormat($order->total_shipping); ?>
                                        </td>
                                    </tr>
								<?php endif; ?>

								<?php if ($order->total_fee > 0.00): ?>
                                    <tr>
                                        <td colspan="4" align="right">
											<?php echo Text::_('COM_UMART_PAYMENT_FEE'); ?>
                                        </td>
                                        <td>
											<?php echo $order->currency->toFormat($order->total_fee); ?>
                                        </td>
                                    </tr>
								<?php endif; ?>

								<?php if ($fieldsPrice = $order->get('fieldsPriceDetails', [])): ?>
									<?php foreach ($fieldsPrice as $fieldPrice): ?>
                                        <tr>
                                            <td colspan="4" align="right">
												<?php echo $fieldPrice->label; ?>
                                            </td>
                                            <td style="font-size: 18px">
												<?php echo $order->currency->toFormat($fieldPrice->price); ?>
                                            </td>
                                        </tr>
									<?php endforeach; ?>
								<?php endif; ?>

	                            <?php if ($order->total_taxes > 0.00): ?>
                                    <tr>
                                        <td colspan="4" align="right">
				                            <?php echo Text::_('COM_UMART_TAXES'); ?>
                                        </td>
                                        <td>
				                            <?php echo $order->currency->toFormat($order->total_taxes); ?>
                                        </td>
                                    </tr>
	                            <?php endif; ?>

	                            <?php if ($order->total_discount > 0.00): ?>
                                    <tr>
                                        <td colspan="4" align="right">
				                            <?php echo Text::_('COM_UMART_DISCOUNT'); ?>
                                        </td>
                                        <td>
				                            <?php echo $order->currency->toFormat($order->total_discount); ?>
                                        </td>
                                    </tr>
	                            <?php endif; ?>


                                <tr>
                                    <td colspan="4" align="right">
										<?php echo Text::_('COM_UMART_GRAND_TOTAL'); ?>
                                    </td>
                                    <td style="font-size: 18px">
										<?php echo $order->currency->toFormat($order->total_price); ?>
                                    </td>
                                </tr>
                            </table>
							<?php if ($shipping): ?>
                                <p style="margin: 5px 0;">
									<?php echo Text::_('COM_UMART_SHIPPING_METHOD') . ': <strong>' . $shipping->name . '</strong>'; ?>
                                </p>
							<?php endif; ?>
							<?php if ($payment): ?>
                                <p style="margin: 5px 0;">
									<?php echo Text::_('COM_UMART_PAYMENT_METHOD') . ': <strong>' . $payment->name . '</strong>'; ?>
                                </p>
							<?php endif; ?>
                        </td>
                    </tr>
				<?php endif; ?>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
