<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2019 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * @var array            $displayData
 * @var ES\Classes\Order $orderClass
 */
$orderClass = $displayData['order'];
$code       = $orderClass->get('order_code');
$email      = $orderClass->get('user_email');
$customer   = $orderClass->get('customerName');
$amount     = $orderClass->currency->toFormat($orderClass->get('total_price'));
?>
<div class="es-checkout-success-wrap">
    <div class="uk-alert-success" uk-alert>
        <a class="uk-alert-close" uk-close></a>
        <p>
			<?php echo Text::sprintf('COM_EASYSHOP_CHECKOUT_SUCCESS_MESSAGE_THANKS_FORMAT', $customer, $code); ?>
        </p>
    </div>
    <div class="es-checkout-success-message">
        <div class="uk-grid-small uk-grid-divider uk-child-width-1-2@s" uk-grid>
            <div>
                <h5 class="uk-h5">
                    <i class="fa fa-hashtag"></i>
					<?php echo Text::_('COM_EASYSHOP_ORDER_DETAIL'); ?>
                </h5>
                <ul class="uk-list">
                    <li><?php echo Text::_('COM_EASYSHOP_TOTAL_AMOUNT') . ': <strong>' . $amount . '</strong>'; ?></li>
                    <li><?php echo Text::_('COM_EASYSHOP_ORDER_STATUS') . ': <strong>' . $orderClass->getStatusText() . '</strong>'; ?></li>
                    <li>
						<?php echo Text::_('COM_EASYSHOP_PAYMENT_STATUS') . ':'; ?>
                        <strong>
							<?php echo $orderClass->getPaymentText(); ?>
							<?php if ($orderClass->get('payment_status')): ?>
                                <i class="fa fa-check-circle uk-text-success"></i>
							<?php else: ?>
                                <i class="fa fa-times-circle uk-text-danger"></i>
							<?php endif; ?>
                        </strong>
                    </li>
                </ul>

				<?php if (easyshop('config', 'enable_track_order', 1)): ?>
                    <h5 class="uk-h5">
                        <i class="fa fa-hashtag"></i>
						<?php echo Text::_('COM_EASYSHOP_ORDER_TRACK_DATA_HINT'); ?>
                    </h5>
                    <ul class="uk-list">
                        <li><?php echo Text::_('COM_EASYSHOP_YOUR_EMAIL') . ': <strong>' . $email . '</strong>'; ?></li>
                        <li><?php echo Text::_('COM_EASYSHOP_ORDER_CODE') . ': <strong>' . $code . '</strong>'; ?></li>
                    </ul>
				<?php endif; ?>
            </div>

			<?php if (!empty($displayData['message'])): ?>
                <div>
					<?php echo Text::_($displayData['message']); ?>
                </div>
			<?php endif; ?>
        </div>
    </div>
</div>
