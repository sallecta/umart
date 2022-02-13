<?php
/**
 
 
 
 
 
 */

use Umart\Classes\Currency;
use Umart\Classes\Order;
use Umart\Classes\User;
use Umart\Classes\Utility;

defined('_JEXEC') or die;
$utility       = plg_sytem_umart_main(Utility::class);
$orderClass    = plg_sytem_umart_main(Order::class);
$currencyClass = plg_sytem_umart_main(Currency::class);
$canManage     = plg_sytem_umart_main(User::class)->core('admin');
$orderStatus   = $orderClass->getOrderStatus();
$paymentStatus = $orderClass->getPaymentStatus();
$iconMaps      = [
	'0'  => 'fa fa-plus',
	'1'  => 'fa fa-check',
	'2'  => 'fa fa-tasks',
	'3'  => 'fa fa-truck',
	'4'  => 'fa fa-check-circle',
	'5'  => 'fa fa-ban',
	'6'  => 'fa fa-history',
	'-2' => 'fa fa-trash',
];
?>
<div class="uk-modal-body">
    <table class="uk-table uk-table-small uk-table-divider uk-table-hover es-orders-notification">
        <thead>
        <tr>
            <th colspan="4" class="uk-text-center">
				<?php foreach ($orderStatus as $value => $text): ?>
                    <div class="es-order-icon-wrap">
                        <div class="es-order-icon es-order-icon-<?php echo $value; ?>">
                            <i class="<?php echo $iconMaps[$value]; ?>"></i>
                        </div>
                        <div>
							<?php echo $text; ?>
                        </div>
                    </div>
				<?php endforeach; ?>
            </th>
        </tr>
        <tbody>
		<?php if (!empty($displayData['orders'])): ?>
			<?php foreach ($displayData['orders'] as $i => $order):
				$currencyClass->load($order->currency_id);
				?>
                <tr>
                    <td class="uk-table-shrink uk-text-nowrap">
						<?php echo sprintf('%02d', $i + 1); ?>
                    </td>
                    <td class="uk-table-shrink uk-text-nowrap">
						<?php if ($canManage): ?>
                            <a href="<?php echo JRoute::_('index.php?option=com_umart&task=order.edit&id=' . $order->id, false); ?>"
                               class="es-order-code">
								<?php echo $order->order_code; ?>
                            </a>
						<?php else: ?>
                            <div class="es-order-code">
								<?php echo $order->order_code; ?>
                            </div>
						<?php endif; ?>
                    </td>
                    <td>
                        <div class="es-order-icon es-order-icon-<?php echo $order->state; ?>"
                             title="<?php echo htmlspecialchars($orderStatus[$order->state], ENT_COMPAT, 'UTF-8'); ?>"
                             uk-tooltip>
                            <i class="<?php echo $iconMaps[$order->state]; ?>"></i>
                        </div>
                        <div class="es-order-icon es-payment-icon-<?php echo $order->payment_status; ?>"
                             title="<?php echo htmlspecialchars($paymentStatus[$order->payment_status], ENT_COMPAT, 'UTF-8'); ?>"
                             uk-tooltip>
							<?php if ($order->payment_status == 1): ?>
                                <i class="fa fa-check"></i>
							<?php elseif ($order->payment_status == 2): ?>
                                <i class="fa fa-undo"></i>
							<?php else: ?>
                                <i class="fa fa-times"></i>
							<?php endif; ?>
                        </div>
                        <i class="fa fa-calendar"></i>
						<?php echo $utility->displayDate($order->created_date, false, true); ?>
                    </td>
                    <td class="uk-table-shrink uk-text-nowrap">
						<?php echo $currencyClass->toFormat($order->total_price); ?>
                    </td>
                </tr>
			<?php endforeach; ?>
		<?php endif; ?>
        </tbody>
    </table>
</div>
