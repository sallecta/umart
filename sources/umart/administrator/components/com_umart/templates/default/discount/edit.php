<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

echo $this->getFormLayout('head');
?>
<div id="es-discount-edit" class="uk-form-horizontal es-edit-panel">
	<?php HTMLHelper::_('umartui.addTab', Text::_('COM_UMART_GENERAL', true), 'info-circle'); ?>
    <div class="uk-child-width-1-2@s uk-grid-match uk-grid-small" uk-grid uk-margin>
        <div>
            <div class="uk-panel uk-card uk-card-default uk-card-small uk-card-body es-border">
                <h3 class="uk-heading-bullet">
					<?php echo Text::_('COM_UMART_OPTIONS'); ?>
                </h3>
				<?php foreach ($this->form->getFieldset('general') as $field): ?>
					<?php echo $field->renderField(); ?>
				<?php endforeach; ?>
            </div>
        </div>
        <div data-zone-group>
            <div class="uk-panel uk-card uk-card-default uk-card-small uk-card-body es-border">
                <h3 class="uk-heading-bullet">
					<?php echo Text::_('COM_UMART_RESTRICTIONS'); ?>
                </h3>
				<?php foreach ($this->form->getFieldset('restrictions') as $field): ?>
					<?php echo $field->renderField(); ?>
				<?php endforeach; ?>
            </div>
        </div>
    </div>
	<?php HTMLHelper::_('umartui.endTab'); ?>
	<?php echo $this->getFormLayout('params'); ?>
	<?php echo HTMLHelper::_('umartui.renderTab'); ?>
</div>
<?php echo $this->getFormLayout('foot'); ?>
