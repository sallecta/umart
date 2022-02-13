<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\Order;

/**
 * @var array $displayData
 * @var Order $order
 */

$order         = $displayData['order'];
$fieldsDetails = $order->get('fieldsPriceDetails', []);
$hasAction     = empty($displayData['noAction']);

?>
<div class="uk-grid uk-grid-small uk-form-grid" data-panel>
	<?php foreach ($displayData['fields'] as $field): ?>
        <div class="umartui_width-1-2 umartui_width-2-5@s">
            <label><?php echo $field->name; ?></label>
        </div>
        <div class="umartui_width-1-2 umartui_width-3-5@s">
            <div>
				<?php echo $field->display; ?>

				<?php if (isset($fieldsDetails[$field->customfield_id])):

					$label = '<small>' . $fieldsDetails[$field->customfield_id]->label . '</small>' . ' (' . ($fieldsDetails[$field->customfield_id]->price >= 0 ? '+' : '-') . $order->currency->toFormat($fieldsDetails[$field->customfield_id]->price) . ')';
					?>
					<?php if ($hasAction): ?>
                    <a class="uk-button uk-button-text es-checkout-field-price" uk-icon="icon: pencil"
                       data-order-id="<?php echo $order->id; ?>"
                       data-field-id="<?php echo $field->customfield_id; ?>"
                       data-price="<?php echo $fieldsDetails[$field->customfield_id]->price; ?>">
						<?php echo $label; ?>
                    </a>
                    <?php else: ?>
                        <?php echo $label; ?>
                    <?php endif; ?>
				<?php endif; ?>
            </div>
        </div>
	<?php endforeach; ?>
</div>
