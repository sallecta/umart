<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
/**
 * @var $displayData array
 * @var $field       \JFormField
 * @var $prefix      string
 */
extract($displayData);
$type = strtolower($field->__get('type'));

if ($type != 'radio')
{
	$field->__set('class', 'not-chosen uk-' . ($type == 'list' ? 'select' : $type));
}

?>
<div class="uk-card uk-card-small uk-card-default uk-card-body uk-margin es-border">
    <div class="uk-margin-small-bottom">
		<?php echo $field->__get('label'); ?>
    </div>
	<?php if ($type == 'list' || $type == 'radio'): ?>
        <div class="uk-flex uk-child-width-1-2@s">
			<?php echo $field->__get('input'); ?>
            <input type="number" name="product_option_price[<?php echo $field->getAttribute('name'); ?>]"
                   class="uk-input es-option-price" data-rule-number
                   value="<?php echo preg_replace('/[^0-9\.]/', '', $prefix); ?>"/>
        </div>
	<?php else: ?>
        <div class="uk-flex uk-flex-middle uk-child-width-1-2@s">
	        <?php echo $field->__get('input'); ?>
            <input type="number" name="product_option_price[<?php echo $field->getAttribute('name'); ?>]"
                   class="uk-input es-option-price" data-rule-number
                   value="<?php echo preg_replace('/[^0-9\.]/', '', $prefix); ?>"/>
        </div>
	<?php endif; ?>
</div>
