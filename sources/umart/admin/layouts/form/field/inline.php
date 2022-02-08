<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;
extract($displayData);
settype($value, 'array');
?>
<?php if (!empty($options)): ?>
    <div id="<?php echo $id; ?>-wrap" class="es-field-inline uk-scope" uk-margin>
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
