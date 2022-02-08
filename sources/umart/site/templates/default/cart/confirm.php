<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\Cart;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * @var $cart     Cart
 * @var $form     Form
 */

$cart         = $this->cart;
$form         = $this->checkoutForm;
$currency     = $this->currency;
$data         = $cart->extractData();
$checkoutData = $cart->getCheckoutData();
$renderer     = $this->getRenderer();
?>
<div id="es-checkout-form" class="es-checkout-wrap es-checkout-confirm uk-margin">
    <form action="" method="post" name="esMainForm" novalidate>
        <div class="es-order-details">
            <div class="uk-grid uk-grid-small" uk-grid>
                <div class="uk-width-1-2@s uk-width-3-5@m">
					<?php if ($this->config->get('show_steps_bar', 1)): ?>
                        <div class="uk-margin">
							<?php echo $renderer->render('checkout.bar', ['layout' => $this->getLayout()]); ?>
                        </div>
					<?php endif; ?>
                    <div class="es-bill-to">
                        <strong><?php echo Text::_('COM_EASYSHOP_BILL_TO'); ?>: </strong>
						<?php echo $this->address['billing']; ?>
                    </div>
					<?php if (!$this->config->get('disable_shipping_address', 0)
						|| $this->config->get('keep_confirm_shipping', 0)
					): ?>
                        <div class="es-ship-to">
                            <strong><?php echo Text::_('COM_EASYSHOP_SHIP_TO'); ?>: </strong>
							<?php echo $this->address['shipping']; ?>
                        </div>
					<?php endif; ?>
                    <div class="es-methods">
                        <!-- Shipping methods -->
						<?php if ($this->shippingMethods): ?>
                            <div class="es-shipping-methods">
                                <h3 class="es-panel-title">
                                    <i class="fa fa-truck"></i>
									<?php echo Text::_('COM_EASYSHOP_SHIPPING_METHOD'); ?>
                                </h3>
                                <ul class="uk-list uk-list-line uk-margin-remove">
									<?php foreach ($this->shippingMethods as $shipping):

										if (!empty($shipping->description))
										{
											$shipping->description = $renderer->render('format.text', ['text' => $shipping->description]);
										}

										?>
                                        <li<?php echo $this->shippingActiveId == $shipping->id ? ' class="active"' : ''; ?>
                                                data-value="<?php echo $shipping->total; ?>">
                                            <label>
                                                <input type="radio" name="jform[shipping_id]"
                                                       data-rule-required="true"
													<?php echo $this->shippingActiveId == $shipping->id ? ' checked="checked"' : ''; ?>
                                                       class="uk-radio"
                                                       value="<?php echo $shipping->id; ?>"/>
												<?php if (!empty($shipping->image)): ?>
                                                    <img src="<?php echo ES_MEDIA_URL . '/' . $shipping->image; ?>"
                                                         class="es-shipping-logo"
                                                         alt="<?php echo $this->escape($shipping->name); ?>"/>
												<?php endif; ?>
												<?php if ($shipping->show_name): ?>
													<?php echo $this->escape($shipping->name); ?>
												<?php endif; ?>
                                                <span class="uk-badge">
                                                    <?php echo $currency->toFormat($shipping->total, true); ?>
                                                </span>
												<?php if (!empty($shipping->description) && $shipping->description_type == 1): ?>
                                                    <span uk-icon="icon: question"
                                                          title="<?php echo htmlspecialchars($shipping->description, ENT_COMPAT, 'UTF-8'); ?>"
                                                          uk-tooltip></span>
												<?php endif; ?>
                                            </label>
											<?php if ($shipping->description_type == 2): ?>
                                                <div class="uk-text-meta es-shipping-desc">
													<?php echo $shipping->description; ?>
                                                </div>
											<?php endif; ?>
											<?php if (!empty($shipping->extraDisplay)): ?>
												<?php echo $shipping->extraDisplay; ?>
											<?php endif; ?>
                                        </li>
									<?php endforeach; ?>
                                </ul>
                            </div>
						<?php endif; ?>
                        <!-- Payment methods -->
						<?php if ($this->paymentMethods): ?>
                            <div class="es-payment-methods">
                                <h3 class="es-panel-title">
                                    <i class="fa fa-credit-card"></i>
									<?php echo Text::_('COM_EASYSHOP_PAYMENT_METHOD'); ?>
                                </h3>
                                <ul class="uk-list uk-list-line uk-margin-remove">
									<?php foreach ($this->paymentMethods as $payment):
										if (!empty($payment->description))
										{
											$payment->description = $renderer->render('format.text', ['text' => $payment->description]);
										}
										?>
                                        <li<?php echo $this->paymentActiveId == $payment->id ? ' class="active"' : ''; ?>
                                                data-value="<?php echo $payment->fee; ?>">
                                            <label>
                                                <input type="radio" name="jform[payment_id]"
                                                       data-rule-required="true"
                                                       class="uk-radio"
													<?php echo !empty($payment->cardForm) ? 'data-card-target="#es-card-' . $payment->id . '"' : '' ?>
													<?php echo $this->paymentActiveId == $payment->id ? ' checked="checked"' : ''; ?>
                                                       value="<?php echo $payment->id; ?>"/>
												<?php if (!empty($payment->image)): ?>
                                                    <img src="<?php echo $payment->image; ?>"
                                                         class="es-payment-logo"
                                                         alt="<?php echo $this->escape($payment->name); ?>"/>
												<?php endif; ?>
												<?php if ($payment->show_name): ?>
													<?php echo $this->escape($payment->name); ?>
												<?php endif; ?>
												<?php if ($payment->fee): ?>
                                                    <span class="uk-badge">
                                                        <?php echo Text::_('COM_EASYSHOP_FEE'); ?>
                                                        <?php echo $currency->toFormat($payment->fee, true); ?>
                                                </span>
												<?php endif; ?>
												<?php if (!empty($payment->description) && $payment->description_type == 1): ?>
                                                    <span uk-icon="icon: question"
                                                          title="<?php echo htmlspecialchars($payment->description, ENT_COMPAT, 'UTF-8'); ?>"
                                                          uk-tooltip></span>
												<?php endif; ?>
                                            </label>
                                            <div id="es-card-<?php echo $payment->id; ?>"
                                                 data-payment-id="<?php echo $payment->id; ?>"
                                                 class="es-payment-card-form<?php echo $this->paymentActiveId != $payment->id ? ' uk-hidden' : ''; ?>">
												<?php if (!empty($payment->description) && $payment->description_type == 2): ?>
                                                    <div class="uk-text-meta">
														<?php echo $payment->description; ?>
                                                    </div>
												<?php endif; ?>
												<?php if (!empty($payment->cardForm)): ?>
													<?php echo $payment->cardForm; ?>
												<?php endif; ?>
												<?php if (!empty($payment->extraDisplay)): ?>
													<?php echo $payment->extraDisplay; ?>
												<?php endif; ?>
                                            </div>
                                        </li>
									<?php endforeach; ?>
                                </ul>
                            </div>
						<?php endif; ?>
                    </div>

					<?php if ($termsField = $form->getField('terms_and_conditions', 'confirm')): ?>
                        <input type="checkbox" name="<?php echo $termsField->name; ?>"
                               id="<?php echo $termsField->id; ?>" class="uk-checkbox" value="1"/>
						<?php echo $termsField->__get('label'); ?>
						<?php $form->removeField('terms_and_conditions', 'confirm'); ?>
					<?php endif; ?>

					<?php echo $renderer->render('form.fields', [
						'fields' => $form->getGroup('confirm'),
					]); ?>
                </div>
                <div class="uk-width-1-2@s uk-width-2-5@m">
					<?php

					if (empty($checkoutData['note']))
					{
						$note = null;
					}
					else
					{
						$note = '<div title="' . htmlspecialchars($checkoutData['note'], ENT_COMPAT, 'UTF-8') . '" uk-tooltip class="uk-text-nowrap uk-text-truncate uk-margin-small"><span uk-icon="icon: info"></span> ' . $checkoutData['note'] . '</div>';
					}

					$summaryDisplayData = ['note' => $note];

					if ($checkoutFields = $form->getGroup('checkoutFields'))
					{
						$summaryDisplayData['checkoutFieldsDisplay'] = $renderer->render('form.fields', [
							'fields'      => $checkoutFields,
							'renderValue' => true,
						]);
					}

					echo $renderer->render('checkout.summary', $summaryDisplayData);

					?>

                    <div class="uk-button-group uk-width-1-1 uk-margin-small">
                        <button type="button" class="uk-button uk-button-small uk-button-default uk-width-1-2"
                                onclick="_es.checkout.editAddress();">
                            <span uk-icon="icon: file-edit"></span>
							<?php echo Text::_('COM_EASYSHOP_EDIT'); ?>
                        </button>
                        <button type="button" class="uk-button uk-button-small uk-button-primary uk-width-1-2"
                                id="es-submit-button">
							<?php echo Text::_('COM_EASYSHOP_FINISHED') ?>
                            <span uk-icon="icon: check"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="option" value="com_easyshop"/>
        <input type="hidden" name="task" value="checkout.finish"/>
		<?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
