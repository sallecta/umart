<?php
/**
 *  @package     com_easyshop
 *  @version     1.0.5
 *  @Author      JoomTech Team
* @copyright   Copyright (C) 2015 - 2019 www.joomtech.net All Rights Reserved.
 *  @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;
$domain = JUri::root();
?>

<div id="order-change-status-email">
	<h3><?php echo JText::_('COM_EASYSHOP_YOUR_ORDER_NO'); ?>.
        {ORDER_CODE} <?php echo JText::_('COM_EASYSHOP_HAS_BEEN_CHANGED_TO'); ?> {ORDER_STATUS}.</h3>
	<p><?php echo JText::_('COM_EASYSHOP_HI'); ?> {CUSTOMER_NAME},</p>
	<p><?php echo JText::sprintf('COM_EASYSHOP_THANKS_FOR_YOUR_ORDER', $domain); ?>.</p>
	<h4><?php echo JText::_('COM_EASYSHOP_BILLING_ADDRESS'); ?></h4>
	{BILLING_ADDRESS}
	<div style="margin-top: 15px"></div>
	{CART_BODY}
    <p>
        <strong><?php echo JText::_('COM_EASYSHOP_PAYMENT_METHOD'); ?>:</strong> {PAYMENT_METHOD}
    </p>
	<p><?php echo JText::sprintf('COM_EASYSHOP_THANKS_FOR_YOUR_ORDER_AGAIN', $domain) ?>.</p>
	<p><?php echo JText::_('COM_EASYSHOP_BEST_REGARDS'); ?>,</p>
	<p>{USER_NAME}</p>
</div>
