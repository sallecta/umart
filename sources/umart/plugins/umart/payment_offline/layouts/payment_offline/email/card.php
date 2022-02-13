<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
?>

<div id="umart_payment_offline-collect-card-email">
    <h3><?php echo JText::_('PLG_UMART_PAYMENT_OFFLINE_HI_THERE'); ?>,</h3>
    <p><?php echo JText::_('PLG_UMART_PAYMENT_OFFLINE_EMAIL_SUMMARY'); ?></p>
    <h4><?php echo JText::_('COM_UMART_ORDER_CODE'); ?>. #{ORDER_CODE}</h4>
    <ul>
        <li><?php echo JText::_('COM_UMART_CARD_HOLDER_NAME'); ?>: {CARD_HOLDER_NAME}</li>
        <li><?php echo JText::_('COM_UMART_CARD_NUMBER'); ?>: {CARD_NUMBER}</li>
        <li><?php echo JText::_('COM_UMART_CARD_CVV'); ?>: {CARD_CVV}</li>
        <li><?php echo JText::_('PLG_UMART_PAYMENT_OFFLINE_CARD_EXPIRY_MONTH'); ?>: {CARD_EXPIRY_MONTH}</li>
        <li><?php echo JText::_('PLG_UMART_PAYMENT_OFFLINE_CARD_EXPIRY_YEAR'); ?>: {CARD_EXPIRY_YEAR}</li>
    </ul>
    <p><?php echo JText::_('COM_UMART_BEST_REGARDS'); ?></p>
</div>
