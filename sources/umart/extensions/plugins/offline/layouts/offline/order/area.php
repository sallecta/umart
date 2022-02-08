<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;
/** @var $data \Joomla\Registry\Registry */
$data = $displayData['payment']->data;
?>
<style>
    @media print {
        #es-offline-order-area {
            display: none !important;
        }
    }
</style>
<div id="es-offline-order-area" uk-margin>
    <button uk-toggle="target: #es-offline-card" type="button" class="uk-button uk-button-small uk-button-primary">
        <span uk-icon="icon: credit-card"></span>
		<?php echo JText::_('PLG_EASYSHOPPAYMENT_OFFLINE_VIEW_CARD'); ?>
    </button>
    <div uk-drop="mode: click" id="es-offline-card"
         class="uk-card uk-card-small uk-card-default uk-card-body uk-width-large es-border">
        <div class="uk-clearfix">
            <button type="button" id="es-offline-delete" class="uk-button uk-button-small uk-background-muted">
                <span uk-icon="icon: trash"></span>
				<?php echo JText::_('PLG_EASYSHOPPAYMENT_OFFLINE_DELETE_CARD_DATA'); ?>
            </button>
        </div>
        <ul class="uk-list">
            <li>
				<span class="uk-text-uppercase uk-text-meta">
                    <?php echo JText::_('COM_EASYSHOP_CARD_HOLDER_NAME'); ?>
                </span>
				<?php echo $data->get('holderName'); ?>
            </li>
            <li>
                <span class="uk-text-uppercase uk-text-meta">
				    <?php echo JText::_('COM_EASYSHOP_CARD_NUMBER'); ?>
                </span>
				<?php echo $data->get('number'); ?>
            </li>
            <li>
                <span class="uk-text-uppercase uk-text-meta">
				    <?php echo JText::_('COM_EASYSHOP_CARD_CVV'); ?>
                </span>
				<?php echo $data->get('cvv'); ?>
            </li>
            <li>
                <span class="uk-text-uppercase uk-text-meta">
				    <?php echo JText::_('PLG_EASYSHOPPAYMENT_OFFLINE_CARD_EXPIRATION'); ?>
                </span>
				<?php echo sprintf('%02d', $data->get('expiryMonth')) . '/' . $data->get('expiryYear'); ?>
            </li>
        </ul>
    </div>
</div>
<script>
    _es.$(document).ready(function ($) {
        $(document).on('click', '#es-offline-delete', function () {
            if (confirm('<?php echo JText::_('PLG_EASYSHOPPAYMENT_OFFLINE_DELETE_CARD_DATA_CONFIRM', true); ?>')) {
                _es.ajax('<?php echo JUri::base(true); ?>/index.php?option=com_ajax&plugin=offline&group=easyshoppayment&format=json', {
                    request: 'deleteCardData',
                    easyshopArea: $('#es-component'),
                    orderId: '<?php echo $displayData['order']->id; ?>'
                }, function (response) {
                    if (response.success) {
                        $('#es-offline-order-area').remove();
                    } else {
                        alert(response.message);
                    }
                });
            }
        });
    });
</script>
