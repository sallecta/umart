<?php
/**
 
 
 
 
 
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

extract($displayData);
?>
<div class="umartui_scope umart_scope es-field-datetime-picker-container">
    <div id="<?php echo $id; ?>-field-inline" class="uk-inline es-field-datetime-picker es-field-control">
		<?php if ($inline): ?>
            <span class="uk-form-icon uk-form-icon-flip" uk-icon="icon: calendar"></span>
		<?php else: ?>
            <a href="#" class="uk-form-icon uk-form-icon-flip"
               uk-toggle="target: #<?php echo $id; ?>-container; animation: uk-animation-fade">
                <span uk-icon="icon: calendar" class="es-icon-calendar"></span>
            </a>
		<?php endif; ?>
        <input type="text" name="<?php echo $id; ?>" id="<?php echo $id; ?>-display"
               class="uk-input es-field-input-display"
               value="<?php echo $valueFormat; ?>"
               placeholder="<?php echo htmlspecialchars($hint, ENT_COMPAT, 'UTF-8'); ?>"
               readonly<?php echo $required ? ' required' : ''; ?>/>
        <input type="hidden" name="<?php echo $name; ?>" id="<?php echo $id; ?>"
               value="<?php echo $value; ?>"<?php echo $required ? ' required' : ''; ?>/>
        <div id="<?php echo $id; ?>-container"
             class="es-datetime-picker-container<?php echo $inline ? ' es-datetime-picker-inline' : ''; ?>"<?php echo $inline ? '' : ' hidden'; ?>>
            <div class="uk-card uk-card-body uk-card-small uk-card-default uk-position-relative">
                <ul class="uk-iconnav uk-position-small uk-position-top-right">
                    <li<?php echo $inline ? ' hidden' : ''; ?>>
                        <a href="#" uk-icon="icon: check" class="es-icon-check"
                           title="<?php echo Text::_('COM_UMART_SELECT_N_CLOSE'); ?>" uk-tooltip></a>
                    </li>
                    <li>
                        <a href="#" uk-icon="icon: refresh" class="es-icon-refresh"
                           title="<?php echo Text::_('COM_UMART_CLEAR'); ?>" uk-tooltip></a>
                    </li>
                    <li<?php echo $inline ? ' hidden' : ''; ?>>
                        <a href="#" uk-icon="icon: close" class="es-icon-close"
                           title="<?php echo Text::_('COM_UMART_CLOSE'); ?>" uk-tooltip></a>
                    </li>
                </ul>
                <div class="uk-text-center uk-margin es-date-display">
					<?php echo $valueFormat ?: Text::_('COM_UMART_NO_DATETIME_SELECTED'); ?>
                </div>
                <div id="<?php echo $id . '-inline' ?>" class="es-datetime-picker-inline uk-flex uk-flex-center"></div>
            </div>
        </div>
    </div>
</div>
