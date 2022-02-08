<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\Order;
use ES\Classes\Utility;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('searchtools.form', '#es-customer-orders-form');
HTMLHelper::_('easyshop.printOrder', '', 'a.es-print-order');
/**
 * @var $utility       Utility
 * @var $orderClass    Order
 */

$utility     = easyshop(Utility::class);
$orderClass  = easyshop(Order::class);
$listOrder   = $this->escape($this->ordersState->get('list.ordering'));
$listDirn    = $this->escape($this->ordersState->get('list.direction'));
$action      = Uri::getInstance()->toString();
$return      = base64_encode($action);
$orderStatus = $orderClass->getOrderStatus();
$iconMaps    = [
	'0'  => 'fa fa-plus',
	'1'  => 'fa fa-check',
	'2'  => 'fa fa-tasks',
	'3'  => 'fa fa-truck',
	'4'  => 'fa fa-check-circle',
	'5'  => 'fa fa-ban',
	'-2' => 'fa fa-trash',
];
?>
<div id="es-customer-orders">
    <form action="<?php echo $action; ?>" method="post" id="es-customer-orders-form">

		<?php echo $this->getRenderer()->render('form.searchtools.filters', ['view' => $this]); ?>

        <table class="uk-table uk-table-striped uk-table-hover uk-table-small">
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
                <th class="uk-table-shrink uk-text-center">
					<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_CODE', 'a.order_code', $listDirn, $listOrder); ?>
                </th>
                <th class="uk-table-shrink uk-text-nowrap uk-text-center">
					<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_ORDER_STATUS', 'a.state', $listDirn, $listOrder); ?>
                </th>
                <th class="uk-table-shrink uk-text-nowrap uk-text-center">
					<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_TOTAL_PRICE', 'a.total_price', $listDirn, $listOrder); ?>
                </th>
                <th class="uk-visible@m">
					<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_CREATED_DATE', 'a.created_date', $listDirn, $listOrder); ?>
                </th>
                <th>
					<?php echo Text::_('COM_EASYSHOP_CUSTOMER'); ?>
                </th>
            </tr>
            </thead>
            <tbody>
			<?php foreach ($this->orders as $i => $order):
				/** @var $order Order */
				$address = $order->getAddress();

				?>
                <tr>
                    <td class="uk-text-center">
						<?php if ($order->checked_out) : ?>
                            <i class="fa fa-lock"></i>
							<?php echo $this->escape($order->order_code); ?>
						<?php else: ?>
                            <a href="<?php echo Route::_('index.php?option=com_easyshop&task=customer.page&page=order&orderId=' . $order->id . '&return=' . $return, false); ?>"
                               class="es-order-code">
								<?php echo $this->escape($order->order_code); ?>
                            </a>
						<?php endif; ?>
                    </td>
                    <td class="uk-text-center">
                        <div class="es-order-icon es-order-icon-<?php echo $order->state; ?>"
                             title="<?php echo htmlspecialchars($order->getStatusText(), ENT_COMPAT, 'UTF-8'); ?>"
                             uk-tooltip>
                            <i class="<?php echo $iconMaps[$order->state]; ?>"></i>
                        </div>
                        <div class="es-order-icon es-payment-icon-<?php echo $order->payment_status; ?>"
                             title="<?php echo htmlspecialchars($order->getPaymentText(), ENT_COMPAT, 'UTF-8'); ?>"
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
                           title="<?php echo htmlspecialchars(Text::_('COM_EASYSHOP_PRINT_ORDER'), ENT_COMPAT, 'UTF-8'); ?>"
                           data-order-id="<?php echo $order->id; ?>"
                           data-order-code="<?php echo $order->order_code; ?>"
                           data-order-email="<?php echo $order->user_email; ?>"
                           data-page-title="<?php echo htmlspecialchars(Text::sprintf('COM_EASYSHOP_ORDER_PRINT_TITLE_FORMAT', $order->order_code), ENT_COMPAT, 'UTF-8'); ?>"
                           uk-tooltip>
                            <i class="fa fa-print"></i>
                        </a>
                    </td>
                    <td class="uk-text-center uk-text-bold">
						<?php echo $order->currency->toFormat($order->total_price); ?>
                    </td>
                    <td class="uk-visible@m">
                        <i class="fa fa-calendar"></i>
						<?php echo $utility->displayDate($order->created_date); ?>
                    </td>
                    <td>
						<?php
						$title = '<strong>' . Text::_('COM_EASYSHOP_BILLING') . ': </strong>' . $utility->formatAddress($address['billing'])
							. '<br/><strong>' . Text::_('COM_EASYSHOP_SHIPPING') . ': </strong>' . $utility->formatAddress($address['shipping']);
						?>

                        <div class="uk-display-inline-block" uk-tooltip
                             title="<?php echo htmlspecialchars($title, ENT_COMPAT, 'UTF-8'); ?>">
                            <span uk-icon="icon: location"></span>
							<?php echo $order->customerName . '. ' . $order->user_email; ?>
                        </div>
                    </td>
                </tr>
			<?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="5" style="border:none">
					<?php if ($this->ordersPagination->getPagesCounter()): ?>
						<?php echo $this->getRenderer()->render('pagination.pagination', $this->ordersPagination->getData()); ?>
					<?php endif; ?>
                </td>
            </tr>
            </tfoot>
        </table>
        <input type="hidden" name="task" value=""/>
		<?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
