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
use ES\Classes\User;
use ES\Classes\Utility;
use ES\Classes\Html;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

echo $this->getFormLayout('head');

/**
 * @var $currency      Currency
 * @var $utility       Utility
 * @var $order         Order
 * @var $userClass     User
 */
$utility        = easyshop(Utility::class);
$order          = $this->order;
$currency       = $order->get('currency');
$renderer       = $this->getRenderer();
$paymentStatus  = $order->getPaymentStatus();
$orderStatus    = $order->getOrderStatus();
$checkoutFields = $order->checkoutFields;

easyshop('addLangText', [
	'COM_EASYSHOP_REMOVE_CONFIRM',
	'COM_EASYSHOP_PRICE_N_OPTIONS',
	'COM_EASYSHOP_QUANTITY',
	'COM_EASYSHOP_SUBTOTAL',
	'COM_EASYSHOP_PRICE',
]);

$userClass        = easyshop(User::class);
$customerName     = $userClass->load($this->item->user_id) ? $userClass->getName() : '';
$iframeAttributes = [
	'src'    => Route::_('index.php?option=com_easyshop&view=products&layout=modal&tmpl=component', false),
	'width'  => '100%',
	'height' => '450',
	'class'  => 'uk-height-large',
];

easyshop(Html::class)->flatPicker();
$basePath  = Uri::base(true) . '/index.php?option=com_easyshop';
$orderId   = (int) $this->item->id;
$frameAttr = htmlspecialchars(json_encode($iframeAttributes), ENT_COMPAT, 'UTF-8');
$this->form->getField('user_id')->renderField();
easyshop('doc')->addScriptDeclaration(<<<JAVASCRIPT
_es.$(document).ready(function ($) {
    var orderId = {$orderId},
        basePath = '{$basePath}';
    $('[data-order-edit]')
        .on('click', '[data-close-panel]', function () {
            var group = $(this).parents('.uk-button-group'),
                panel = group.siblings('[data-panel]');
            panel.siblings('[data-panel-clone]').remove();
            group.addClass('uk-hidden')
                .siblings('[data-edit-panel]').removeClass('uk-hidden');
            panel.show();
        })
        .on('click', '[data-edit-panel], [data-save-panel]', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var
                button = $(this),
                type = button.attr('data-edit-panel') ? 'edit' : 'save',
                panel = button.data(type + 'Panel').toString().toLowerCase(),
                data = [];

            if (type === 'save') {
                var inputs = button.parent('.uk-button-group')
                    .siblings('[data-panel-clone]').find('[name^="jform"]');
                if (!inputs.es_validate()) {
                    return false;
                }
                data = inputs.serializeArray();
            } else {
                button.next('.uk-button-group').removeClass('uk-hidden');
            }

            _es.ajax(basePath + '&task=order.editPanel', {
                panel: panel,
                orderId: orderId,
                type: type,
                data: data,
                easyshopArea: button.parents('.uk-panel:eq(0)')
            }, function (response) {
                var html = $(response.data.html);

                if (type === 'edit') {
                    button
                        .siblings('[data-panel]')
                        .hide()
                        .after(html.attr('data-panel-clone', ''));
                    button
                        .addClass('uk-hidden')
                        .siblings('[data-save-panel]')
                        .removeClass('uk-hidden');
                } else {
                    $('[data-order-edit]').html(html.find('[data-order-edit]').html());
                }               
                
                $('[data-order-edit] .flatpickr').each(function () {
                    flatpickr(this, $(this).data('flatpickr'));
                });                
                
                window.usersJs['jform_user_id']();
                _es.initChosen();
            });
        });

    $('[data-order-edit]').on('click', '[data-product-remove]', function (e) {
        e.preventDefault();
        var el = $(this), offsetTop = el.offset().top, url, data;
        _es.uikit.modal.confirm(_es.lang._('COM_EASYSHOP_REMOVE_CONFIRM')).then(function () {
            url = basePath + '&task=order.removeProduct';
            data = {
                orderProductId: el.parents('td:eq(0)').data('orderProductId')
            };
            _es.ajax(url, data, function (response) {
                var html = $(response.data.html)
                    .find('[data-order-edit]')
                    .html();
                $('[data-order-edit]').html(html);
                $('html, body').animate({
                    scrollTop: offsetTop
                }, 800);
            });
        }, function () {
        });
    });

    if (!$('#cart-product-modal').length) {
        $('<div/>', {
            'id': 'cart-product-modal',
            'class': 'uk-modal-container',
            'html': '<div class="uk-modal-dialog uk-modal-body">'
                + '<a class="uk-modal-close-default" uk-close></a>'
                + '<iframe data-attribute="{$frameAttr}"></iframe></div>'
        }).appendTo($('#es-component'));
    }

    var modal = $('#cart-product-modal');
    modal.on('beforeshow', function () {
        if (!modal.data('iframeHandled')) {
            modal.data('iframeHandled', true);
            modal.find('.uk-modal-body').addClass('es-ajax-loading');
            var iframe = modal.find('iframe');
            iframe.attr(iframe.data('attribute'));
        }
    });

    var loadProduct = function (type, productId, rowIndex) {
        var table = $('[data-order-cart]').find('table[data-panel]');
        _es.ajax(basePath + '&task=order.loadProduct', {
            orderId: orderId,
            productId: productId,
            type: type,
            easyshopArea: $('#es-component')
        }, function (response) {
            var row = $(response.data);
            row.find('input[type="radio"]').addClass('uk-radio');
            if (rowIndex === -1) {
                table.find('tbody:eq(0)').append(row);
            } else {
                var tr = table.find('tbody>tr').eq(rowIndex);
                row.find('#pClose').attr('disabled', 'disabled');
                row.data('display', tr.get(0).outerHTML);
                tr.after(row);
                tr.remove();
            }

            $('html, body').animate({
                scrollTop: row.offset().top
            }, 800);
        });
    };

    modal.find('iframe').on('load', function () {
        modal.find('.uk-modal-body').removeClass('es-ajax-loading');
        var iframe = $(this).contents();
        iframe.find('[data-product-id]').on('click', function (e) {
            e.preventDefault();
            var el = $(this),
                productId = el.data('productId');

            loadProduct('add', productId, -1);
            $('[data-order-edit] [data-product-add]').attr('disabled', 'disabled');

            _es.uikit.modal('#cart-product-modal').hide();
        });
    });

    $('[data-order-edit]')
        .on('click', '[data-product-add], [data-product-edit]', function (e) {
            e.preventDefault();
            var el = $(this);
            if (el.get(0).hasAttribute('data-product-add')) {
                _es.uikit.modal('#cart-product-modal').show();
            } else {
                var rowIndex = el.parents('tr:eq(0)').index();
                loadProduct('edit', el.parents('[data-order-product-id]').data('orderProductId'), rowIndex);
            }
        });

    $('[data-order-edit]').on('click', '#pSave, #pClose, #pCancel', function (e) {
        e.preventDefault();
        var el = $(this),
            row = el.parents('tr:eq(0)'),
            offsetTop = row.offset().top;
        if (el.attr('id') === 'pClose' || el.attr('id') === 'pCancel') {
            if (el.attr('id') === 'pCancel' && el.parents('tr:eq(0)').data('display')) {
                var previous = $(el.parents('tr:eq(0)').data('display'));
                previous.find('svg').remove();
                row.after(previous);
            }
            row.remove();
        } else {
            if (row.find('[data-rule-required], data-rule-number').es_validate()) {
                _es.ajax(basePath + '&task=order.addProduct', {
                    orderId: orderId,
                    name: row.find('#pName').val(),
                    price: row.find('#pPrice').val(),
                    taxes: row.find('#pTaxes').val(),
                    options: row.find('#pOptions').find('select, input').serialize(),
                    quantity: row.find('#pQuantity').val(),
                    productId: row.find('#pID').val(),
                    orderProductId: el.data('orderProductId'),
                    easyshopArea: row.parents('.uk-panel')
                }, function (response) {
                    var html = $(response.data.html)
                        .find('[data-order-edit]')
                        .html();
                    $('[data-order-edit]').html(html);
                });
            }
        }

        $('[data-order-edit] [data-product-add]').removeAttr('disabled');
        $('html, body').animate({
            scrollTop: offsetTop
        }, 800);
    });

    $('[data-order-edit]').on('change', '#jform_payment_status', function () {
        var el = $(this), p = $('#jform_total_paid');
        if (el.val() === '1' && p.val() === '0') {
            p.val($('#jform_total_price').val());
        }
    });
    
    $('[data-order-edit]').on('click', '.es-checkout-field-price', function (e) {
       e.preventDefault();
       var a = $(this);
       _es.uikit.modal.prompt(_es.lang._('COM_EASYSHOP_PRICE'), a.data('price')).then(function (price) {
            _es.ajax(
                basePath + '&task=order.updateFieldPrice', 
                {
                    orderId: a.data('orderId'),
                    fieldId: a.data('fieldId'),
                    price: price,
                },
                function (response) {
                    if (response.success) {                        
                        $('[data-order-edit]').html($(response.data.html).find('[data-order-edit]').html());
                    }                   
                }
            );             
       });
    });
});
JAVASCRIPT
);

?>
    <div id="es-order-edit" class="es-edit-panel es-detail-panel" data-order-edit>
        <div class="uk-grid-small" uk-height-match="target: .uk-panel" uk-grid>
            <div class="uk-width-1-2@m">
                <fieldset class="uk-form-horizontal es-input-100 uk-fieldset">
                    <div class="uk-panel">
                        <button type="button" class="uk-button uk-button-default uk-button-small"
                                data-edit-panel="general">
                            <span uk-icon="icon: file-edit"></span>
                        </button>
                        <div class="uk-button-group uk-float-right uk-hidden uk-margin-small-bottom">
                            <button type="button" class="uk-button uk-button-primary uk-button-small"
                                    data-save-panel="general">
                                <span uk-icon="icon: check"></span>
                            </button>
                            <button type="button" class="uk-button uk-button-default uk-button-small"
                                    data-close-panel>
                                <span uk-icon="icon: close"></span>
                            </button>
                        </div>
                        <div class="uk-h5 uk-text-uppercase uk-margin-remove-top uk-margin-small-bottom uk-clearfix">
                            <span uk-icon="icon: info"></span>
							<?php echo Text::_('COM_EASYSHOP_MAIN_INFORMATION'); ?>
                        </div>
						<?php echo $renderer->render('order.general', [
							'form'          => $this->form,
							'orderStatus'   => $orderStatus,
							'paymentStatus' => $paymentStatus,
							'currency'      => $currency,
							'utility'       => $utility,
							'customerName'  => $customerName,
						]); ?>
                    </div>
                </fieldset>
            </div>
            <div class="uk-width-1-2@m">
                <fieldset class="uk-form-horizontal es-input-100 uk-fieldset">
                    <div class="uk-panel">
                        <button type="button" class="uk-button uk-button-default uk-button-small"
                                data-edit-panel="payment">
                            <span uk-icon="icon: file-edit"></span>
                        </button>
                        <div class="uk-button-group uk-float-right uk-hidden uk-margin-small-bottom">
                            <button type="button" class="uk-button uk-button-primary uk-button-small"
                                    data-save-panel="payment">
                                <span uk-icon="icon: check"></span>
                            </button>
                            <button type="button" class="uk-button uk-button-default uk-button-small"
                                    data-close-panel>
                                <span uk-icon="icon: close"></span>
                            </button>
                        </div>
                        <div class="uk-h5 uk-text-uppercase uk-margin-remove-top uk-margin-small-bottom uk-clearfix">
                            <span uk-icon="credit-card"></span>
							<?php echo Text::_('COM_EASYSHOP_PAYMENT'); ?>
                        </div>
						<?php echo $renderer->render('order.payment', [
							'form'          => $this->form,
							'orderStatus'   => $orderStatus,
							'paymentStatus' => $paymentStatus,
							'currency'      => $currency,
							'utility'       => $utility,
						]); ?>
						<?php if (is_object($this->payment) && !empty($this->payment->orderArea)): ?>
							<?php echo $this->payment->orderArea; ?>
						<?php endif; ?>
                    </div>
                </fieldset>
            </div>
            <div class="uk-width-1-2@m">
                <fieldset class="uk-form-horizontal es-input-100 uk-fieldset" data-zone-group>
                    <div class="uk-panel">
                        <button type="button" class="uk-button uk-button-default uk-button-small"
                                data-edit-panel="billing">
                            <span uk-icon="icon: file-edit"></span>
                        </button>
                        <div class="uk-button-group uk-float-right uk-hidden uk-margin-small-bottom">
                            <button type="button" class="uk-button uk-button-primary uk-button-small"
                                    data-save-panel="billing">
                                <span uk-icon="icon: check"></span>
                            </button>
                            <button type="button" class="uk-button uk-button-default uk-button-small"
                                    data-close-panel>
                                <span uk-icon="icon: close"></span>
                            </button>
                        </div>
                        <div class="uk-h5 uk-text-uppercase uk-margin-remove-top uk-margin-small-bottom uk-clearfix">
							<?php echo HTMLHelper::_('easyshop.icon', 'es-icon-bill'); ?>
							<?php echo Text::_('COM_EASYSHOP_BILLING_ADDRESS'); ?>
                        </div>
						<?php echo $renderer->render(
							'order.fields',
							[
								'order'  => $order,
								'fields' => $order->address['billing'],
							]
						); ?>
                    </div>
                </fieldset>
            </div>
            <div class="uk-width-1-2@m">
                <fieldset class="uk-form-horizontal es-input-100 uk-fieldset" data-zone-group>
                    <div class="uk-panel">
                        <button type="button" class="uk-button uk-button-small uk-button-default"
                                data-edit-panel="shipping">
                            <span uk-icon="icon: file-edit"></span>
                        </button>
                        <div class="uk-button-group uk-float-right uk-hidden uk-margin-small-bottom">
                            <button type="button" class="uk-button uk-button-primary uk-button-small"
                                    data-save-panel="shipping">
                                <span uk-icon="icon: check"></span>
                            </button>
                            <button type="button" class="uk-button uk-button-default uk-button-small"
                                    data-close-panel>
                                <span uk-icon="icon: close"></span>
                            </button>
                        </div>
                        <div class="uk-h5 uk-text-uppercase uk-margin-remove-top uk-margin-small-bottom uk-clearfix">
							<?php echo HTMLHelper::_('easyshop.icon', 'es-icon-truck'); ?>
							<?php echo Text::_('COM_EASYSHOP_SHIPPING_ADDRESS'); ?>
                        </div>
						<?php echo $renderer->render(
							'order.fields',
							[
								'order'  => $order,
								'fields' => $order->address['shipping'],
							]
						); ?>
                    </div>
                </fieldset>
            </div>
            <div class="uk-width-1-1">
                <fieldset class="uk-form-horizontal es-input-100 uk-fieldset" data-zone-group>
                    <div class="uk-panel">
                        <button type="button" class="uk-button uk-button-small uk-button-default"
                                data-edit-panel="checkout">
                            <span uk-icon="icon: file-edit"></span>
                        </button>
                        <div class="uk-button-group uk-float-right uk-hidden uk-margin-small-bottom">
                            <button type="button" class="uk-button uk-button-primary uk-button-small"
                                    data-save-panel="checkout">
                                <span uk-icon="icon: check"></span>
                            </button>
                            <button type="button" class="uk-button uk-button-default uk-button-small"
                                    data-close-panel>
                                <span uk-icon="icon: close"></span>
                            </button>
                        </div>
                        <div class="uk-h5 uk-text-uppercase uk-margin-remove-top uk-margin-small-bottom uk-clearfix">
							<?php echo HTMLHelper::_('easyshop.icon', 'field.svg'); ?>
							<?php echo Text::_('COM_EASYSHOP_ADDITIONAL_INFORMATION'); ?>
                        </div>
						<?php
                        echo $renderer->render(
							'order.fields',
							[
								'order'  => $order,
								'fields' => $checkoutFields,
							]
						);
                        ?>
                    </div>
                </fieldset>
            </div>
        </div>
        <div class="uk-clearfix uk-margin-small">
            <button type="button" class="uk-float-right uk-button uk-button-small uk-button-primary" data-product-add>
                <span uk-icon="icon: plus"></span>
            </button>
        </div>
		<?php echo $renderer->render('order.cart', [
			'order'    => $order,
			'currency' => $currency,
			'utility'  => $utility,
		]); ?>
    </div>
<?php
echo $this->getFormLayout('foot');