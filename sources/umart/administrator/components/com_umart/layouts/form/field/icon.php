<?php

/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::addIncludePath(UMART_COMPONENT_ADMINISTRATOR . '/helpers/html');

extract($displayData);

/** @var Joomla\CMS\Form\Field\HiddenField $field */
$value   = $field->value;
$modal   = $field->getAttribute('modal');
$isModal = $modal === 'true' || $modal === '1';

plg_sytem_umart_main('doc')->addScriptDeclaration(<<<JAVASCRIPT
    _umart.$(document).ready(function ($) {
        var iconInput = $('#{$field->id}');
        var iconContainer = $('#{$field->id}-icon-container');
        var iconInlineContainer = $('#{$field->id}-icon-inline-container');
       
        iconContainer.find('.es-field-icon').on('click', function () {
            var icon = $(this);
            icon.toggleClass('active').siblings('.es-field-icon').removeClass('active');
            
            if (icon.hasClass('active')) {
                iconInput.val(icon.data('value'));
                iconContainer.find('.find-icon').val(icon.data('value').replace('es-icon-', ''));
                
                if (iconInlineContainer.length) {
                    iconInlineContainer.find('.es-field-icon-current').removeClass('empty').html(icon.html());
                } 
            } else {
                iconInput.val('');
                iconContainer.find('.find-icon').val('');
                
                if (iconInlineContainer.length) {
                    iconInlineContainer.find('.es-field-icon-current').addClass('empty').empty();
                } 
            }          
        });
        
        iconContainer.find('.find-icon').on('keyup', function () {
            var q = $.trim($(this).val());
            
            if (q.length) {
                var icon;
                iconContainer.find('.es-field-icon').each(function () {
                    icon = $(this);
                    
                    if (icon.data('value').indexOf(q) === -1) {
                        icon.addClass('uk-hidden');                        
                    } else {
                        icon.removeClass('uk-hidden');
                    }
                });
            } else {
                iconContainer.find('.es-field-icon').removeClass('uk-hidden');
            }
        });        
        
        if (iconInlineContainer.length) {
            iconInlineContainer.find('.clear-icon').on('click', function (e) {
                e.preventDefault();
                iconInlineContainer.find('.es-field-icon-current').addClass('empty').empty();
                iconInput.val('');
            });            
        }
    });
JAVASCRIPT
);

?>

<div class="umart_scope umartui_scope">
	<?php ob_start(); ?>
    <div id="<?php echo $field->id; ?>-icon-container">
        <div class="uk-inline">
			<?php if ($isModal): ?>
                <a class="uk-form-icon uk-form-icon-flip select-icon" href="#"
                   uk-toggle="target: #<?php echo $field->id . '-modal'; ?>" uk-icon="icon: check"></a>
			<?php endif; ?>
            <input class="uk-input find-icon uk-margin-small uk-form-width-medium" type="text"
                   placeholder="<?php echo Text::_('COM_UMART_SEARCH'); ?>"
                   value="<?php echo str_replace('es-icon-', '', $value); ?>"/>

        </div>
        <div class="es-field-icon-container uk-flex uk-flex-wrap">
			<?php foreach ($icons as $icon): ?>
                <div class="es-field-icon<?php echo $value === $icon ? ' active' : ''; ?>"
                     data-value="<?php echo $icon; ?>">
					<?php echo HTMLHelper::_('umart.icon', $icon, 24, 24); ?>
                </div>
			<?php endforeach; ?>
        </div>
    </div>
	<?php $iconsBuffer = ob_get_clean(); ?>

	<?php if ($isModal): ?>
        <div class="uk-flex uk-flex-middle uk-flex-wrap" id="<?php echo $field->id; ?>-icon-inline-container">
            <div class="es-field-icon-current<?php echo empty($value) ? ' empty' : ''; ?>">
				<?php if (!empty($value)): ?>
					<?php echo HTMLHelper::_('umart.icon', $value, 24, 24); ?>
				<?php endif; ?>
            </div>
            <a href="#" class="uk-button uk-button-text uk-text-meta search-icon uk-margin-small-left"
               uk-toggle="target: #<?php echo $field->id . '-modal'; ?>">
				<?php echo Text::_('COM_UMART_CHOOSE_ICON'); ?>
            </a>
            <span class="uk-margin-small-left uk-text-meta">|</span>
            <a href="#" class="uk-button uk-button-text uk-text-meta clear-icon uk-margin-small-left">
				<?php echo Text::_('COM_UMART_REMOVE'); ?>
            </a>
        </div>
        <div id="<?php echo $field->id . '-modal'; ?>" class="uk-modal-container" uk-modal>
            <div class="uk-modal-dialog uk-modal-body uk-background-default">
                <button class="uk-modal-close-default" type="button" uk-close></button>
				<?php echo $iconsBuffer; ?>
            </div>
        </div>
	<?php else: ?>
		<?php echo $iconsBuffer; ?>
	<?php endif; ?>

	<?php echo $input; ?>
</div>