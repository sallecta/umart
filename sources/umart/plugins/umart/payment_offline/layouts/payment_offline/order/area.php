<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
/** @var $data \Joomla\Registry\Registry */
$data = $displayData['payment']->data;
?>
<style>
    @media print {
        #es-umart_payment_offline-order-area {
            display: none !important;
        }
    }
</style>
<div id="es-umart_payment_offline-order-area" uk-margin>
    <button uk-toggle="target: #es-umart_payment_offline-card" type="button" class="uk-button uk-button-small uk-button-primary">
        <span uk-icon="icon: credit-card"></span>
		<?php echo JText::_('PLG_UMART_PAYMENT_OFFLINE_VIEW_CARD'); ?>
    </button>
    <div uk-drop="mode: click" id="es-umart_payment_offline-card"
         class="uk-card uk-card-small uk-card-default uk-card-body uk-width-large es-border">
        <div class="uk-clearfix">
            <button type="button" id="es-umart_payment_offline-delete" class="uk-button uk-button-small uk-background-muted">
                <span uk-icon="icon: trash"></span>
				<?php echo JText::_('PLG_UMART_PAYMENT_OFFLINE_DELETE_CARD_DATA'); ?>
            </button>
        </div>
        <ul class="uk-list">
            <li>
				<span class="uk-text-uppercase uk-text-meta">
                    <?php echo JText::_('COM_UMART_CARD_HOLDER_NAME'); ?>
                </span>
				<?php echo $data->get('holderName'); ?>
            </li>
            <li>
                <span class="uk-text-uppercase uk-text-meta">
				    <?php echo JText::_('COM_UMART_CARD_NUMBER'); ?>
                </span>
				<?php echo $data->get('number'); ?>
            </li>
            <li>
                <span class="uk-text-uppercase uk-text-meta">
				    <?php echo JText::_('COM_UMART_CARD_CVV'); ?>
                </span>
				<?php echo $data->get('cvv'); ?>
            </li>
            <li>
                <span class="uk-text-uppercase uk-text-meta">
				    <?php echo JText::_('PLG_UMART_PAYMENT_OFFLINE_CARD_EXPIRATION'); ?>
                </span>
				<?php echo sprintf('%02d', $data->get('expiryMonth')) . '/' . $data->get('expiryYear'); ?>
            </li>
        </ul>
    </div>
</div>
<script>
    _es.$(document).ready(function ($) {
        $(document).on('click', '#es-umart_payment_offline-delete', function () {
            if (confirm('<?php echo JText::_('PLG_UMART_PAYMENT_OFFLINE_DELETE_CARD_DATA_CONFIRM', true); ?>')) {
                _es.ajax('<?php echo JUri::base(true); ?>/index.php?option=com_ajax&plugin=umart_payment_offline&group=umart_payment&format=json', {
                    request: 'deleteCardData',
                    umartArea: $('#es-component'),
                    orderId: '<?php echo $displayData['order']->id; ?>'
                }, function (response) {
                    if (response.success) {
                        $('#es-umart_payment_offline-order-area').remove();
                    } else {
                        alert(response.message);
                    }
                });
            }
        });
    });
</script>
