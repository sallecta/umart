<?php
/**
 
 
 
 
 
 */

use Umart\Classes\Renderer;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;
HTMLHelper::_('umartui.tabState');
/** @var $renderer Renderer */
$renderer           = $this->getRenderer();
$enableRegistration = $this->config->get('enable_registration', 1);
$guestCheckout      = $this->config->get('guest_checkout', 1);
$tabLayout          = $this->config->get('checkout_tab', 'tab-default');

?>
<div id="es-checkout-form" class="es-checkout-wrap" data-login>
	<?php if ($this->config->get('show_steps_bar', 1)): ?>
        <div class="uk-panel uk-margin">
			<?php echo $renderer->render('checkout.bar', ['layout' => $this->getLayout()]); ?>
        </div>
	<?php endif; ?>

    <div class="uk-panel uk-margin">
		<?php echo $renderer->render('checkout.summary'); ?>
    </div>

    <div class="uk-panel uk-margin">
		<?php HTMLHelper::_('umartui.addTab', Text::_('COM_UMART_LOGIN')); ?>
        <div class="uk-child-width-1-2@s uk-grid-divider uk-grid-small" uk-grid>
            <div id="checkout-type-login">
				<?php echo $renderer->render('customer.login', [
					'form' => $this->customerForm,
				]); ?>
            </div>

			<?php if ($guestCheckout): ?>
                <div id="checkout-type-guest">
					<?php echo $renderer->render('checkout.guest', [
						'form' => $this->checkoutForm,
					]); ?>
                </div>
			<?php endif; ?>
        </div>
		<?php HTMLHelper::_('umartui.endTab'); ?>

		<?php if ($enableRegistration): ?>
			<?php HTMLHelper::_('umartui.addTab', Text::_('COM_UMART_REGISTRATION')); ?>
            <div id="checkout-type-registration">
				<?php echo $renderer->render('customer.registration', [
					'form'           => $this->customerForm,
					'isCheckoutPage' => true,
					'return'         => base64_encode(Route::_(UmartHelperRoute::getCartRoute('checkout'), false)),
				]); ?>
            </div>
			<?php HTMLHelper::_('umartui.endTab'); ?>
		<?php endif; ?>

		<?php echo HTMLHelper::_('umartui.renderTab', $tabLayout, ['tabId' => 'es-checkout-tab']); ?>
    </div>
</div>
