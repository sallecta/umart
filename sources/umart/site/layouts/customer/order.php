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
use ES\Classes\Renderer;
use ES\Classes\User;
use ES\Classes\Utility;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * @var array $displayData
 * @var       $currency      Currency
 * @var       $utility       Utility
 * @var       $order         Order
 * @var       $userClass     User
 * @var       $renderer      Renderer
 * @var       $orderForm     Form
 * @var       $payment       stdClass
 */
extract($displayData);
$userClass = easyshop(User::class);
$utility   = easyshop(Utility::class);
$userClass->load($orderForm->getValue('user_id'));
$paymentStatus  = $order->getPaymentStatus();
$orderStatus    = $order->getOrderStatus();
$orderState     = (int) $orderForm->getValue('state');
$currency       = $order->get('currency');
$checkoutFields = $order->get('checkoutFields', []);
$dataOrder      = htmlspecialchars(json_encode(
	[
		'id'    => $order->id,
		'code'  => $order->order_code,
		'email' => $order->user_email,
	]
), ENT_COMPAT, 'UTF-8');
HTMLHelper::_('easyshop.printOrder', Text::sprintf('COM_EASYSHOP_ORDER_PRINT_TITLE_FORMAT', '#es-print-order'));

?>
<div id="es-customer-order" class="es-detail-panel">
    <div id="es-toolbar" class="uk-clearfix uk-padding-small uk-background-muted uk-margin">
        <form action="" method="post">
            <div class="uk-button-group">
                <button type="button" data-task="customer.goBackPage"
                        class="uk-button uk-button-default uk-button-small">
                    <span uk-icon="icon: reply"></span>
					<?php echo Text::_('COM_EASYSHOP_GO_BACK'); ?>
                </button>
                <button type="button"
                        id="es-print-order"
                        class="uk-button uk-button-primary uk-button-small uk-float-left"
                        data-order="<?php echo $dataOrder; ?>">
                    <i class="fa fa-print"></i>
					<?php echo Text::_('COM_EASYSHOP_PRINT'); ?>
                </button>
            </div>
			<?php if ($orderState === ES_ORDER_CREATED): ?>
                <button type="button" data-task="customer.cancelOrder"
                        class="uk-button uk-button-secondary uk-button-small uk-float-right">
                    <span uk-icon="icon: ban"></span>
					<?php echo Text::_('COM_EASYSHOP_CANCEL_THIS_ORDER'); ?>
                </button>
			<?php endif; ?>
            <input name="task" type="hidden" value=""/>
			<?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </div>

    <div id="es-order-print" class="uk-grid-small" uk-height-match="target: .uk-panel" uk-grid>
        <div class="uk-width-1-2@m">
            <fieldset class="uk-form-horizontal uk-fieldset">
                <div class="uk-panel">
                    <div class="uk-h5 uk-text-uppercase uk-margin-remove-top uk-margin-small-bottom">
                        <span uk-icon="icon: info"></span>
						<?php echo Text::_('COM_EASYSHOP_MAIN_INFORMATION'); ?>
                    </div>
					<?php echo $renderer->render('order.general', [
						'form'          => $orderForm,
						'orderStatus'   => $orderStatus,
						'paymentStatus' => $paymentStatus,
						'currency'      => $currency,
						'utility'       => $utility,
						'customerName'  => $userClass->getName(),
					]); ?>
                </div>
            </fieldset>
        </div>
        <div class="uk-width-1-2@m">
            <fieldset class="uk-form-horizontal uk-fieldset">
                <div class="uk-panel">
                    <div class="uk-h5 uk-text-uppercase uk-margin-remove-top uk-margin-small-bottom">
                        <span uk-icon="credit-card"></span>
						<?php echo Text::_('COM_EASYSHOP_PAYMENT'); ?>
                    </div>
					<?php echo $renderer->render('order.payment', [
						'form'          => $orderForm,
						'orderStatus'   => $orderStatus,
						'paymentStatus' => $paymentStatus,
						'currency'      => $currency,
						'utility'       => $utility,
					]); ?>
					<?php if (is_object($payment) && !empty($payment->orderArea)): ?>
						<?php echo $payment->orderArea; ?>
					<?php endif; ?>
                </div>
            </fieldset>
        </div>
        <div class="uk-width-1-2@m">
            <fieldset class="uk-form-horizontal uk-fieldset" data-zone-group>
                <div class="uk-panel">
                    <div class="uk-h5 uk-text-uppercase uk-margin-small-bottom">
						<?php echo HTMLHelper::_('easyshop.icon', 'es-icon-bill'); ?>
						<?php echo Text::_('COM_EASYSHOP_BILLING_ADDRESS'); ?>
                    </div>
					<?php echo $utility->formatAddress($order->address['billing']); ?>
                </div>
            </fieldset>
        </div>
        <div class="uk-width-1-2@m">
            <fieldset class="uk-form-horizontal uk-fieldset" data-zone-group>
                <div class="uk-panel">
                    <div class="uk-h5 uk-text-uppercase uk-margin-remove-top uk-margin-small-bottom">
						<?php echo HTMLHelper::_('easyshop.icon', 'es-icon-truck'); ?>
						<?php echo Text::_('COM_EASYSHOP_SHIPPING_ADDRESS'); ?>
                    </div>
					<?php echo $utility->formatAddress($order->address['shipping']); ?>
                </div>
            </fieldset>
        </div>
    </div>

	<?php if (!empty($checkoutFields)): ?>
        <div class="uk-margin">
            <div class="uk-h5 uk-text-uppercase uk-margin-remove-top uk-margin-small-bottom uk-clearfix">
				<?php echo HTMLHelper::_('easyshop.icon', 'field'); ?>
				<?php echo Text::_('COM_EASYSHOP_ADDITIONAL_INFORMATION'); ?>
            </div>

			<?php echo $renderer->render(
				'order.fields',
				[
					'order'    => $order,
					'fields'   => $checkoutFields,
					'noAction' => true,
				]
			); ?>
        </div>
	<?php endif; ?>

	<?php echo $renderer->render('order.cart', [
		'order'    => $order,
		'currency' => $currency,
		'utility'  => $utility,
		'noAction' => true,
	]); ?>

	<?php if (!empty($paymentOptionsList)):
		$paymentFormArea = '';
		$selectedPayment = isset($payment->element) ? $payment->element : null;
		?>

        <div class="uk-margin">
            <blockquote class="uk-text-meta">
				<?php echo Text::_('COM_EASYSHOP_PAYPAL_CUSTOMER_PAY_ORDER_MSG'); ?>
            </blockquote>
            <select class="uk-select uk-width-medium" id="es-payment-options">
                <option value=""><?php echo Text::_('COM_EASYSHOP_PAYMENT_SELECT'); ?></option>
				<?php foreach ($paymentOptionsList as $paymentElement => $paymentOption):
					$selected = $selectedPayment == $paymentElement ? ' selected' : '';
					$class = ' class="es-payment-form-area uk-margin' . ($selected ? '' : ' uk-hidden') . '"';
					$paymentFormArea .= '<div id="es-payment-form-' . $paymentElement . '"' . $class . '>' . $paymentOption[1] . '</div>';
					?>
                    <option value="<?php echo $paymentElement; ?>"<?php echo $selected; ?>>
						<?php echo htmlspecialchars($paymentOption[0]); ?>
                    </option>
				<?php endforeach; ?>
            </select>
			<?php echo $paymentFormArea; ?>
        </div>
	<?php endif; ?>
</div>

<script>
    _es.$(document).ready(function ($) {
        $('form button[data-task]').on('click', function () {
            var task = $(this).data('task');
            this.form.task.value = task;
            if (task === 'customer.cancelOrder' && !confirm('<?php echo Text::_('COM_EASYSHOP_ORDER_CANCELLED_CONFIRM', true); ?>')) {
                return false;
            }
            this.form.submit();
        });

        $('#es-payment-options').on('change', function () {
            var value = $(this).val();
            $('.es-payment-form-area').addClass('uk-hidden');

            if (value.length) {
                $('#es-payment-form-' + value).removeClass('uk-hidden');
            }
        });
    });
</script>
