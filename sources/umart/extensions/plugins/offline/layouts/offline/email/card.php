<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;
?>

<div id="offline-collect-card-email">
    <h3><?php echo JText::_('PLG_EASYSHOPPAYMENT_OFFLINE_HI_THERE'); ?>,</h3>
    <p><?php echo JText::_('PLG_EASYSHOPPAYMENT_OFFLINE_EMAIL_SUMMARY'); ?></p>
    <h4><?php echo JText::_('COM_EASYSHOP_ORDER_CODE'); ?>. #{ORDER_CODE}</h4>
    <ul>
        <li><?php echo JText::_('COM_EASYSHOP_CARD_HOLDER_NAME'); ?>: {CARD_HOLDER_NAME}</li>
        <li><?php echo JText::_('COM_EASYSHOP_CARD_NUMBER'); ?>: {CARD_NUMBER}</li>
        <li><?php echo JText::_('COM_EASYSHOP_CARD_CVV'); ?>: {CARD_CVV}</li>
        <li><?php echo JText::_('PLG_EASYSHOPPAYMENT_OFFLINE_CARD_EXPIRY_MONTH'); ?>: {CARD_EXPIRY_MONTH}</li>
        <li><?php echo JText::_('PLG_EASYSHOPPAYMENT_OFFLINE_CARD_EXPIRY_YEAR'); ?>: {CARD_EXPIRY_YEAR}</li>
    </ul>
    <p><?php echo JText::_('COM_EASYSHOP_BEST_REGARDS'); ?></p>
</div>
