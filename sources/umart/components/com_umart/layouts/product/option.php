<?php
/**
 
 
 
 
 
 */

use Joomla\CMS\Form\FormField;

defined('_JEXEC') or die;
/**
 * @var $displayData array
 * @var $field       FormField
 * @var $prefix      string
 */
extract($displayData);
$type     = strtolower($field->__get('type'));
$label    = $field->__get('label');
$input    = $field->__get('input');
$lblClass = $field->__get('labelclass');
$name     = $field->getAttribute('name');

?>
<div class="es-option es-option-<?php echo $type; ?>">
	<?php if ($type == 'checkbox'): ?>
        <label class="<?php echo trim('es-label ' . $lblClass); ?>">
			<?php echo $input; ?>
			<?php echo $label; ?>
            <div class="option-prefix " data-prefix-id="<?php echo $name; ?>">
				<?php echo $prefix; ?>
            </div>
        </label>
	<?php else: ?>
        <div class="<?php echo trim('es-label ' . $lblClass); ?>">
			<?php echo $label; ?>
            <div class="option-prefix " data-prefix-id="<?php echo $name; ?>">
				<?php echo $prefix; ?>
            </div>
        </div>
        <div class="es-control">
			<?php echo $input; ?>
        </div>
	<?php endif; ?>
</div>
