<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\Currency;
use ES\Classes\Order;
use ES\Classes\Utility;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * @var Currency $currency
 * @var Order    $order
 * @var Utility  $utility
 */

echo $this->getFormLayout('head');

$vars          = $this->get('layout.storage');
$user          = $vars['user'];
$userId        = (int) $user->get()->id;
$this->columns = 5;
$currency      = easyshop(Currency::class);
$order         = easyshop(Order::class);
$utility       = easyshop(Utility::class);
$paymentStatus = $order->getPaymentStatus();
$orderStatus   = $order->getOrderStatus();
$model         = easyshop('model', 'Order');
$orderStatus   = $order->getOrderStatus();
$iconMaps      = [
	'0'  => 'fa fa-plus',
	'1'  => 'fa fa-check',
	'2'  => 'fa fa-tasks',
	'3'  => 'fa fa-truck',
	'4'  => 'fa fa-check-circle',
	'5'  => 'fa fa-ban',
	'-2' => 'fa fa-trash',
];
easyshop('doc')->addStyleDeclaration('#es-body{float:none; width: 100%}');

?>
<thead>
<tr>
    <th colspan="7">
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
    <th width="1%" class="uk-text-center">
		<?php echo HTMLHelper::_('easyshop.gridCheckall'); ?>
    </th>
    <th class="uk-text-center uk-table-shrink">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_ORDER_CODE', 'a.order_code', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-table-shrink uk-text-nowrap uk-text-center">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_ORDER_STATUS', 'a.state', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-table-shrink uk-text-nowrap uk-text-center">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_TOTAL_PRICE', 'a.total_price', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_CREATED_DATE', 'a.created_date', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th>
		<?php echo Text::_('COM_EASYSHOP_CUSTOMER'); ?>
    </th>
    <th width="1%" class="uk-text-nowrap uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_ID', 'a.id', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
</tr>
</thead>
<tbody>
<?php foreach ($this->items as $i => $item):
	$address = $order->getAddress($item->id);
	?>
    <tr>
        <td class="uk-text-center">
			<?php echo HTMLHelper::_('easyshop.gridId', $i, $item->id); ?>
        </td>
        <td class="uk-text-center">
            <a class="es-order-code" data-order-id="<?php echo $item->id; ?>">
				<?php echo $this->escape($item->order_code); ?>
            </a>
        </td>
        <td class="uk-text-center">
            <div class="es-order-icon es-order-icon-<?php echo $item->state; ?>"
                 title="<?php echo htmlspecialchars($orderStatus[$item->state], ENT_COMPAT, 'UTF-8'); ?>"
                 uk-tooltip>
                <i class="<?php echo $iconMaps[$item->state]; ?>"></i>
            </div>
            <div class="es-order-icon es-payment-icon-<?php echo $item->payment_status; ?>"
                 title="<?php echo htmlspecialchars($paymentStatus[$item->payment_status], ENT_COMPAT, 'UTF-8'); ?>"
                 uk-tooltip>
				<?php if ($item->payment_status == 1): ?>
                    <i class="fa fa-check"></i>
				<?php elseif ($item->payment_status == 2): ?>
                    <i class="fa fa-undo"></i>
				<?php else: ?>
                    <i class="fa fa-times"></i>
				<?php endif; ?>
            </div>
        </td>
        <td class="uk-text-center uk-text-bold">
			<?php
			if ($item->currency_id)
			{
				echo $currency->load($item->currency_id)->toFormat($item->total_price);
			}
			else
			{
				echo $currency->getDefault()->toFormat($item->total_price);
			}
			?>
        </td>
        <td class="uk-visible@m">
            <i class="uk-icon-calendar"></i>
			<?php echo $utility->displayDate($item->created_date); ?>
        </td>
        <td>
			<?php
			$title = '<strong>' . Text::_('COM_EASYSHOP_BILLING') . ': </strong>' . $utility->formatAddress($address['billing'])
				. '<br/><strong>' . Text::_('COM_EASYSHOP_SHIPPING') . ': </strong>' . $utility->formatAddress($address['shipping']);
			?>

            <div class="uk-display-inline-block" uk-tooltip
                 title="<?php echo htmlspecialchars($title, ENT_COMPAT, 'UTF-8'); ?>">
                <span uk-icon="icon: location"></span>
				<?php echo $item->customerName . '. ' . $item->user_email; ?>
            </div>
        </td>
        <td class="uk-visible@m">
			<?php echo $item->id; ?>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
<?php echo $this->getFormLayout('foot'); ?>
