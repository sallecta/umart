<?php

/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
$domain = JUri::root();
?>

<div id="order-change-status-email">
	<h3><?php echo JText::_('COM_UMART_YOUR_ORDER_NO'); ?>.
		{ORDER_CODE} <?php echo JText::_('COM_UMART_PAYMENT_HAS_BEEN_CHANGED_TO'); ?> {PAYMENT_STATUS}.</h3>
	<p><?php echo JText::_('COM_UMART_HI'); ?> {CUSTOMER_NAME},</p>
	<p><?php echo JText::sprintf('COM_UMART_THANKS_FOR_YOUR_ORDER', $domain); ?>.</p>
	<h4><?php echo JText::_('COM_UMART_BILLING_ADDRESS'); ?></h4>
	{BILLING_ADDRESS}
	<div style="margin-top: 15px"></div>
	{CART_BODY}
    <p>
        <strong><?php echo JText::_('COM_UMART_PAYMENT_METHOD'); ?>:</strong> {PAYMENT_METHOD}
    </p>
	<p><?php echo JText::sprintf('COM_UMART_THANKS_FOR_YOUR_ORDER_AGAIN', $domain) ?>.</p>
	<p><?php echo JText::_('COM_UMART_BEST_REGARDS'); ?>,</p>
	<p>{USER_NAME}</p>
</div>
