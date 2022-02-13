<?php
/**
 
 
 
 
 
 */

use Umart\Classes\Currency;
use Umart\Classes\Order;
use Umart\Classes\Utility;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;
HTMLHelper::_('umart.printOrder', '', 'a.es-print-order');
$utility       = plg_sytem_umart_main(Utility::class);
$orderClass    = plg_sytem_umart_main(Order::class);
$currencyClass = plg_sytem_umart_main(Currency::class);
$paymentStatus = $orderClass->getPaymentStatus();
$orderStatus   = $orderClass->getOrderStatus();
$iconMaps      = [
	'0'  => 'fa fa-plus',
	'1'  => 'fa fa-check',
	'2'  => 'fa fa-tasks',
	'3'  => 'fa fa-truck',
	'4'  => 'fa fa-check-circle',
	'5'  => 'fa fa-ban',
	'-2' => 'fa fa-trash',
];
?>
<table class="uk-table uk-table-small uk-table-divider uk-table-hover es-order-notification">
    <thead>
    <tr>
        <th colspan="5">
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
    <tr>
        <th class="uk-table-shrink uk-text-center uk-text-nowrap">
			<?php echo Text::_('COM_UMART_CODE'); ?>
        </th>
        <th class="uk-table-shrink uk-text-center uk-text-nowrap">
			<?php echo Text::_('COM_UMART_ORDER_STATUS'); ?>
        </th>
        <th class="uk-table-shrink uk-text-center uk-text-nowrap">
			<?php echo Text::_('COM_UMART_PRICE'); ?>
        </th>
        <th>
			<?php echo Text::_('COM_UMART_DATE'); ?>
        </th>
        <th class="uk-visible@m">
			<?php echo Text::_('COM_UMART_BILLING_SHIPPING_INFO') ?>
        </th>
    </tr>
    </thead>
    <tbody>
	<?php if (!empty($displayData['orders'])): ?>
		<?php foreach ($displayData['orders'] as $order):

			if (!($order instanceof Order))
			{
				$id    = $order->id;
				$order = plg_sytem_umart_main(Order::class);
				$order->load($id);
			}

			$address = $order->getAddress();

			?>
            <tr>
                <td class="uk-text-center uk-text-nowrap">
                    <a href="<?php echo Route::_('index.php?option=com_umart&task=order.edit&id=' . $order->id, false); ?>"
                       class="es-order-code">
						<?php echo $order->order_code; ?>
                    </a>
                </td>
                <td class="uk-text-center uk-text-nowrap">
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
                    <a href="#" class="es-order-icon es-print-order"
                       title="<?php echo htmlspecialchars(Text::_('COM_UMART_PRINT_ORDER'), ENT_COMPAT, 'UTF-8'); ?>"
                       data-order-id="<?php echo $order->id; ?>"
                       data-order-code="<?php echo $order->order_code; ?>"
                       data-order-email="<?php echo $order->user_email; ?>"
                       data-page-title="<?php echo htmlspecialchars(Text::sprintf('COM_UMART_ORDER_PRINT_TITLE_FORMAT', $order->order_code), ENT_COMPAT, 'UTF-8'); ?>"
                       uk-tooltip>
                        <i class="fa fa-print"></i>
                    </a>
                </td>
                <td class="uk-text-center uk-text-nowrap">
					<?php echo $order->currency->toFormat($order->total_price); ?>
                </td>
                <td title="<?php echo htmlspecialchars($utility->displayDate($order->created_date), ENT_COMPAT, 'UTF-8'); ?>"
                    uk-tooltip>
                    <i class="fa fa-calendar"></i>
					<?php echo $utility->displayDate($order->created_date, true, true); ?>
                </td>
                <td>
					<?php
					$title = '<strong>' . Text::_('COM_UMART_BILLING') . ': </strong>' . $utility->formatAddress($address['billing'])
						. '<br/><strong>' . Text::_('COM_UMART_SHIPPING') . ': </strong>' . $utility->formatAddress($address['shipping']);
					?>
                    <div class="uk-display-inline-block" uk-tooltip
                         title="<?php echo htmlspecialchars($title, ENT_COMPAT, 'UTF-8'); ?>">
                        <span uk-icon="icon: location"></span>
						<?php echo ($order->customerName ?: 'Unknown') . '. ' . $order->user_email; ?>
                    </div>
                </td>
            </tr>
		<?php endforeach; ?>
	<?php endif; ?>
    </tbody>
	<?php if (isset($displayData['pagination']) && $displayData['pagination']->getPagesCounter()): ?>
        <tfoot>
        <thead>
        <tr>
            <th colspan="5">
				<?php echo $displayData['renderer']->render('pagination.pagination', $displayData['pagination']->getData()); ?>
            </th>
        </tr>
        </tfoot>
	<?php endif; ?>
</table>
