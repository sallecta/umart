<?php
/**
 
 
 
 
 
 */

defined('_JEXEC') or die;
extract($displayData);
settype($value, 'array');
?>
<?php if (!empty($options)): ?>
    <div id="<?php echo $id; ?>-wrap" class="es-field-inline umartui_scope" uk-margin>
		<?php foreach ($options as $i => $option):
			$checked = in_array($option->value, $value) ? ' checked' : '';
			$active = $checked ? ' active' : '';
			?>
            <button type="button" id="<?php echo $id . '_' . $i; ?>"
                    class="es-inline-button uk-button uk-button-default uk-button-small<?php echo $active . ($multiple ? ' multiple' : ''); ?>">
                <input name="<?php echo $name; ?>" type="checkbox"
                       class="uk-hidden<?php echo $field->required ? ' required' : ''; ?>"
                       value="<?php echo htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8'); ?>"<?php echo $checked; ?>/>
				<?php echo htmlspecialchars($option->text, ENT_COMPAT, 'UTF-8'); ?>
            </button>
		<?php endforeach; ?>
    </div>
<?php endif; ?>
