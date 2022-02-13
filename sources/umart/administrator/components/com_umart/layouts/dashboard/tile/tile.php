<?php
/**
 
 
 
 
 
 */

defined('_JEXEC') or die;

extract($displayData);

$filterDayDate   = 'filter_from_date='
	. $ordersThisDay['fromDate']
	. '&filter_to_date=&filter_currency_id='
	. $currencyClass->get('id')
	. '&filter_payment_status=';
$filterMonthDate = 'filter_from_date='
	. $ordersThisMonth['fromDate']
	. '&filter_to_date=' . $ordersThisMonth['toDate']
	. '&filter_currency_id=' . $currencyClass->get('id')
	. '&filter_payment_status=';
$filterSaleMonth = $filterMonthDate . '1';
?>

<div class="uk-flex uk-flex-wrap uk-flex-center uk-flex-wrap-around uk-grid-small uk-grid-match es-tile uk-margin" uk-grid>
    <div class="umartui_width-1-4@m umartui_width-1-2@s">
        <div class="uk-tile uk-padding-small uk-tile-primary">
            <div class="es-count">
				<?php echo count($ordersThisMonth['items']); ?>
            </div>
            <div class="es-total">
				<?php echo $currencyClass->toFormat($ordersThisMonth['totalPrice']); ?>
            </div>
            <p><?php echo JText::_('COM_UMART_ORDERS_THIS_MONTH'); ?></p>
            <i class="fa fa-3x fa-area-chart"></i>
            <a href="<?php echo JRoute::_('index.php?option=com_umart&view=orders&' . $filterMonthDate, false); ?>">
				<?php echo JText::_('COM_UMART_MORE_INFO'); ?>
            </a>
        </div>
    </div>
    <div class="umartui_width-1-4@m umartui_width-1-2@s">
        <div class="uk-tile uk-padding-small es-sales-this-month">
            <div class="es-count">
				<?php echo count($ordersThisMonth['saleItems']); ?>
            </div>
            <div class="es-total">
				<?php echo $currencyClass->toFormat($ordersThisMonth['totalPaid']); ?>
            </div>
            <p><?php echo JText::_('COM_UMART_SALES_THIS_MONTH'); ?></p>
            <i class="fa fa-3x fa-credit-card"></i>
            <a href="<?php echo JRoute::_('index.php?option=com_umart&view=orders&' . $filterSaleMonth, false); ?>">
				<?php echo JText::_('COM_UMART_MORE_INFO'); ?>
            </a>
        </div>
    </div>
    <div class="umartui_width-1-4@m umartui_width-1-2@s">
        <div class="uk-tile uk-padding-small es-order-average-price">
            <div class="es-count">
				<?php echo $averageDay['count']; ?>
            </div>
            <div class="es-total">
				<?php echo $currencyClass->toFormat($averageDay['totalPaid']); ?>
            </div>
            <p><?php echo JText::_('COM_UMART_AVERAGE_DAY'); ?></p>
            <i class="fa fa-3x fa-money"></i>
            <a href="<?php echo JRoute::_('index.php?option=com_umart&view=orders&' . $filterSaleMonth, false); ?>">
				<?php echo JText::_('COM_UMART_MORE_INFO'); ?>
            </a>
        </div>
    </div>
    <div class="umartui_width-1-4@m umartui_width-1-2@s">
        <div class="uk-tile uk-padding-small es-order-current">
            <div class="es-count">
				<?php echo count($ordersThisDay['items']); ?>
            </div>
            <div class="es-total">
				<?php echo $currencyClass->toFormat($ordersThisDay['totalPrice']); ?>
            </div>
            <p><?php echo JText::_('COM_UMART_ORDERS_THIS_DAY'); ?></p>
            <i class="fa fa-3x fa-shopping-cart"></i>
            <a href="<?php echo JRoute::_('index.php?option=com_umart&view=orders&' . $filterDayDate, false); ?>">
				<?php echo JText::_('COM_UMART_MORE_INFO'); ?>
            </a>
        </div>
    </div>
</div>
