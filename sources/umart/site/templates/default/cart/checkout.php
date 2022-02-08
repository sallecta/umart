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
use ES\Classes\Currency;
use ES\Classes\Renderer;
use ES\Classes\Utility;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;

/**
 * @var Form     $form
 * @var Cart     $cart
 * @var Currency $currency
 * @var Renderer $renderer
 */

$form     = $this->checkoutForm;
$cart     = $this->cart;
$currency = $this->currency;
$data     = $cart->getCheckoutData();
$renderer = $this->getRenderer();
$utility  = easyshop(Utility::class);
$isReady  = true;

?>
<div id="es-checkout-form" class="uk-margin es-checkout-wrap">
    <div class="uk-grid uk-grid-small" uk-grid>
        <div class="uk-width-1-2@s uk-width-3-5@m uk-form-stack">
            <div class="uk-card uk-card-small uk-card-body uk-background-default">
				<?php if ($this->config->get('show_steps_bar', 1)): ?>
                    <div class="uk-margin">
						<?php echo $renderer->render('checkout.bar', ['layout' => $this->getLayout()]); ?>
                    </div>
				<?php endif; ?>

				<?php if (!$this->user->id): ?>
                    <div class="es-customer-info uk-margin">
						<?php if ($this->config->get('enable_registration', 1)): ?>
                            <div class="es-customer-info">
                                <h4 class="uk-h5 uk-heading-bullet">
									<?php echo Text::_('COM_EASYSHOP_ACCOUNT'); ?>
                                </h4>
                                <div class="uk-text-small">
									<?php echo Text::_('COM_EASYSHOP_CHECKOUT_ACCOUNT_DESC'); ?>
                                    <a class="uk-text-emphasis" uk-toggle="target: #login-modal">
                                        <u><?php echo Text::_('COM_EASYSHOP_OR_LOGIN'); ?></u>
                                        <span uk-icon="icon: sign-in"></span>
                                    </a>
                                </div>

                                <div id="login-modal" uk-modal>
                                    <div class="uk-modal-dialog uk-modal-body">
                                        <a class="uk-modal-close-default" uk-close></a>
										<?php echo $renderer->render('customer.login', ['form' => $this->customerForm,]); ?>
                                    </div>
                                </div>
                            </div>

							<?php echo $renderer->render('form.fields', ['fields' => $this->customerForm->getGroup('registration')]); ?>

							<?php if ($extraGroups = $this->customerForm->getGroup('extras')): ?>
								<?php echo $renderer->render('form.fields', ['fields' => $extraGroups]); ?>
							<?php endif; ?>

						<?php elseif ($this->config->get('guest_checkout', 1)) : ?>
							<?php echo $this->customerForm->renderField('email', 'registration') ?>
						<?php else:
							$isReady = false;
							echo $renderer->render('customer.login', ['form' => $this->customerForm]);
						endif; ?>
                    </div>
				<?php endif; ?>
				<?php if ($isReady) : ?>
                    <div class="es-billing-address">
                        <h3 class="es-panel-title uk-heading-bullet">
							<?php echo Text::_('COM_EASYSHOP_BILLING_ADDRESS'); ?>
                        </h3>
                        <div data-zone-group>
							<?php echo $renderer->render('form.fields', [
								'fields' => $form->getGroup('billing_address'),
							]); ?>
                        </div>
                    </div>

					<?php if ($this->config->get('disable_shipping_address', 0)): ?>
                        <input type="checkbox" name="jform[address_different]" value="0" checked="checked"
                               class="uk-hidden"/>
					<?php else: ?>
                        <label class="uk-margin">
                            <input type="checkbox" name="jform[address_different]"
								<?php echo !empty($data['address_different']) ? ' checked="checked"' : '' ?>
                                   class="uk-checkbox"
                                   value="1"/>
							<?php echo Text::_('COM_EASYSHOP_SHIPPING_DIFFERENT_FROM_BILLING'); ?>
                        </label>
                        <div class="es-shipping-address"<?php echo empty($data['address_different']) ? ' style="display:none"' : '' ?>>
                            <h3 class="es-panel-title uk-heading-bullet uk-margin-top">
								<?php echo Text::_('COM_EASYSHOP_SHIPPING_ADDRESS'); ?>
                            </h3>
                            <div data-zone-group>
								<?php echo $renderer->render('form.fields', [
									'fields' => $form->getGroup('shipping_address'),
								]); ?>
                            </div>
                        </div>
					<?php endif; ?>
				<?php endif; ?>
            </div>
            <div id="es-sticky-bottom"></div>
        </div>
        <div class="uk-width-1-2@s uk-width-2-5@m">
            <div uk-sticky="media: 960; bottom: #es-sticky-bottom">

				<?php

				$summaryDisplayData = [
					'note' => $renderer->render('form.fields', [
						'fields' => [
							$form->getField('note'),
						],
					]),
				];

				if ($checkoutFields = $form->getGroup('checkoutFields'))
				{
					$summaryDisplayData['checkoutFieldsDisplay'] = $renderer->render('form.fields', [
						'fields'      => $checkoutFields,
						'renderValue' => false,
					]);
				}


				echo $renderer->render('checkout.summary', $summaryDisplayData);

				?>

				<?php if ($isReady): ?>

					<?php if ($this->config->get('show_coupons', '1')): ?>
                        <div class="uk-margin">
                            <div class="uk-padding-small uk-background-muted">
								<?php echo $renderer->render('coupon.coupon', ['formSmall' => true]); ?>
                            </div>
                        </div>
					<?php endif; ?>

                    <div class="uk-margin">
						<?php echo $renderer->render('checkout.button', $data); ?>
                    </div>

				<?php endif; ?>

            </div>
        </div>
    </div>
</div>
