<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;
$renderer = easyshop('renderer');
?>
<div class="uk-card uk-card-default uk-card-small uk-card-body">
    <div class="uk-margin">
		<?php foreach ($data['items'] as $pk => $item):
			$hasImage = !empty($item['product']->images[0]->tiny);
			?>
            <div class="uk-grid-small" uk-grid>
				<?php if ($hasImage): ?>
                    <div class="uk-width-auto">
						<?php echo $renderer->render('media.image', [
							'image'      => $item['product']->images[0],
							'size'       => 'tiny',
							'attributes' => [
								'class' => 'uk-preserve-width',
								'alt'   => $item['product']->images[0]->title ?: $item['product']->name,
								'width' => '55',
							],
						]); ?>
                    </div>
				<?php endif; ?>
                <div class="uk-width-expand">
                    <a href="<?php echo $item['product']->link; ?>" class="uk-link-reset uk-text-muted">
						<?php echo $item['product']->name; ?>
						<?php if (!empty($item['options'])): ?>
							<?php $options = []; ?>
							<?php foreach ($item['options'] as $option): ?>
								<?php $options[] = $option['text']; ?>
							<?php endforeach; ?>

							<?php echo ' (' . implode(', ', $options) . ')'; ?>
						<?php endif; ?>
                        <strong>
							<?php echo sprintf('x %02d', $item['quantity']); ?>
                            <br/><?php echo $currency->toFormat($item['subTotal'], true); ?>
                        </strong>
                    </a>
					<?php $id = (int) $item['product']->id; ?>
					<?php $keyId = $item['key'] . $id; ?>
					<?php $maxQuantity = (int) $item['product']->params->get('product_detail_max_quantity', 0); ?>
                    <div class="uk-inline">
						<?php $id = (int) $item['product']->id; ?>
						<?php $keyId = $item['key'] . $id; ?>
						<?php $maxQuantity = (int) $item['product']->params->get('product_detail_max_quantity', 0); ?>
                        <ul class="uk-iconnav">
                            <li>
                                <a href="#"
                                   onclick="_es.cart.update('remove', <?php echo $id; ?>, 0, '<?php echo $item['key']; ?>'); return false;"
                                   uk-icon="icon: close">
                                </a>
                            </li>
                            <li>
                                <a href="#" onclick="_es.$(this).next().toggleClass('uk-hidden'); return false;"
                                   uk-icon="icon: pencil"></a>
                                <input type="number"
                                       min="1"
									<?php echo $maxQuantity > 0 ? ' max="' . $maxQuantity . '"' : ''; ?>
									<?php echo $maxQuantity == 1 ? ' disabled' : ''; ?>
                                       step="1"
                                       class="product-quantity-box uk-input uk-form-small uk-form-width-small uk-position-center-right-out uk-margin-small-left uk-hidden"
                                       value="<?php echo $item['quantity']; ?>"
                                       onchange="_es.cart.update('update', <?php echo $id; ?>, this.value, '<?php echo $item['key']; ?>');"/>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
		<?php endforeach; ?>
    </div>
	<?php if ($params->get('sub_total', 1)): ?>
        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-expand"
                 uk-leader="fill: ."><?php echo Text::_('COM_EASYSHOP_SUBTOTAL'); ?></div>
            <div><?php echo $currency->toFormat($data['subTotal'], true); ?></div>
        </div>
	<?php endif; ?>

	<?php if ($params->get('taxes', 1) && $data['totalTaxes'] > 0.00): ?>
        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-expand" uk-leader="fill: ."><?php echo Text::_('COM_EASYSHOP_TAXES'); ?></div>
            <div><?php echo $currency->toFormat($data['totalTaxes'], true); ?></div>
        </div>
	<?php endif; ?>

	<?php if ($params->get('shipping', 1) && $data['totalShip'] > 0.00): ?>
        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-expand" uk-leader="fill: ."><?php echo Text::_('COM_EASYSHOP_SHIPPING'); ?></div>
            <div><?php echo $currency->toFormat($data['totalShip'], true); ?></div>
        </div>
	<?php endif; ?>

	<?php if ($params->get('payment_fee', 1) && $data['paymentFee'] > 0.00): ?>
        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-expand" uk-leader="fill: ."><?php echo Text::_('COM_EASYSHOP_PAYMENT_FEE'); ?></div>
            <div><?php echo $currency->toFormat($data['paymentFee'], true); ?></div>
        </div>
	<?php endif; ?>

	<?php if ($params->get('discount', 1) && $data['orderDiscount'] > 0.00): ?>
        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-expand"
                 uk-leader="fill: ."><?php echo Text::_('COM_EASYSHOP_ORDER_DISCOUNT'); ?></div>
            <div><?php echo $currency->toFormat($data['orderDiscount'], true); ?></div>
        </div>
	<?php endif; ?>

	<?php if (!empty($data['checkoutFieldsDetails'])): ?>
		<?php foreach ($data['checkoutFieldsDetails'] as $fieldDetail): ?>

            <div class="uk-grid-small" uk-grid>
                <div class="uk-width-expand" uk-leader="fill: .">
					<?php echo $fieldDetail['label']; ?>
                </div>
                <div>
					<?php echo $currency->toFormat($fieldDetail['price'], true); ?>
                </div>
            </div>

		<?php endforeach; ?>
	<?php endif; ?>

	<?php if ($params->get('grand_total', 1)): ?>
        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-expand" uk-leader="fill: ."><?php echo Text::_('COM_EASYSHOP_GRAND_TOTAL'); ?></div>
            <div class="uk-text-bold"><?php echo $currency->toFormat($data['grandTotal'], true); ?></div>
        </div>
	<?php endif; ?>

	<?php if ($params->get('checkout_button', 1)): ?>
        <a href="<?php echo Route::_(EasyshopHelperRoute::getCartRoute('checkout'), false); ?>"
           class="uk-button uk-button-primary uk-width-1-1 uk-margin-top uk-margin-bottom">
            <span uk-icon="icon: credit-card"></span>
			<?php echo Text::_('COM_EASYSHOP_CHECKOUT'); ?>
        </a>
	<?php endif; ?>

</div>
