<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
$dataValues = $displayData['value'];
?>

<div id="es-option-modal" uk-modal>
    <div class="uk-modal-dialog">
        <div class="uk-modal-header uk-background-muted uk-clearfix uk-padding-small">
            <div class="uk-float-left uk-button uk-button-text">
				<?php echo JText::sprintf('COM_UMART_OPTION_NAME_FORMAT', $displayData['name']); ?>
            </div>
            <div class="uk-button-group uk-float-right">
                <button type="button" class="uk-button uk-button-small uk-button-danger uk-modal-close">
                    <span uk-icon="icon: close"></span>
					<?php echo JText::_('COM_UMART_CLOSE'); ?>
                </button>
                <button type="button" id="es-button-save"
                        class="uk-button uk-button-primary uk-button-small"
                        data-option-id="<?php echo $displayData['id']; ?>">
                    <span uk-icon="icon: check"></span>
					<?php echo JText::_('COM_UMART_SAVE'); ?>
                </button>
            </div>
        </div>
        <div class="uk-modal-body uk-padding-small" uk-overflow-auto>
			<?php if ($displayData['type'] != 'checkbox'): ?>
				<?php foreach ($displayData['options'] as $option): ?>
                    <div data-option-value="<?php echo $option->value; ?>">
						<?php

						$displayData['name']  = JText::_($option->text);
						$displayData['value'] = isset($dataValues[$option->value]) ? $dataValues[$option->value] : [];

						echo plg_sytem_umart_main('renderer')->render('modal.option.body', $displayData);
						?>
                    </div>
				<?php endforeach; ?>
			<?php else: ?>
				<?php echo plg_sytem_umart_main('renderer')->render('modal.option.body', $displayData); ?>
			<?php endif; ?>
        </div>
    </div>
</div>
<div id="es-option-modal-stack" uk-modal="stack: true">
    <div class="uk-modal-dialog">
        <div class="uk-modal-header uk-background-muted uk-padding-small">
            <div class="uk-button-group">
                <a href="#" class="uk-modal-close uk-button uk-button-default uk-button-small">
                    <span uk-icon="icon: reply"></span>
					<?php echo JText::_('COM_UMART_BACK'); ?>
                </a>
                <a href="#es-option-modal-stack" id="es-image-insert"
                   class="uk-button uk-button-primary uk-button-small">
                    <span uk-icon="icon: check"></span>
					<?php echo JText::_('COM_UMART_PICK'); ?>
                </a>
            </div>
            <div id="es-option-image-header" class="uk-button uk-button-text"></div>
        </div>
        <div class="uk-modal-body uk-padding-small" uk-overflow-auto>
            <div id="es-option-images"></div>
        </div>
    </div>
</div>
