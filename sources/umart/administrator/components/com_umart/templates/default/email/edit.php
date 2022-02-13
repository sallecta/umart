<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\Addon;
use Joomla\CMS\Language\Text;

echo $this->getFormLayout('head');

if (!$this->item->id)
{
	plg_sytem_umart_main('doc')->addScriptDeclaration('
		_umart.$(document).ready(function($){
			$("#jform_send_on").on("change", function(){
				var el = $(this),
					form = $("#item-form");
				if(el.val() != ""){
					form.find("[name=\'task\']").val("email.loadDefaultTemplate");
					form.submit();
				}
			});
		});
	');
}

$addOns = plg_sytem_umart_main(Addon::class)
	->getAddons('email', (int) $this->item->id);

?>
<div id="es-email-edit">
    <div class="uk-grid-small uk-grid-match" uk-grid>
        <div class="umartui_width-1-2@m umartui_width-2-5@l">
            <div class="uk-card uk-card-small uk-card-body uk-card-default es-border es-input-100">
                <h3 class="uk-heading-bullet">
					<?php echo Text::_('COM_UMART_GENERAL'); ?>
                </h3>
				<?php foreach ($this->form->getFieldset('general') as $field): ?>
					<?php echo $field->renderField(); ?>
				<?php endforeach; ?>
				<?php echo $this->form->getField('send_from_name')->renderField(); ?>
				<?php echo $this->form->getField('send_from_email')->renderField(); ?>
				<?php echo $this->form->getField('send_to_emails')->renderField(); ?>

				<?php foreach ($addOns as $element => $form):
					$groups = $form->getGroup('');
					?>
					<?php if (count($groups)): ?>
                    <h4 class="uk-h5 uk-heading-bullet uk-margin-remove">
						<?php echo Text::_('PLG_UMART_' . strtoupper($element) . '_ADDON_LABEL'); ?>
                    </h4>
					<?php foreach ($groups as $field): ?>
						<?php echo $field->renderField(); ?>
					<?php endforeach; ?>
				<?php endif; ?>
				<?php endforeach; ?>
            </div>
        </div>
        <div class="umartui_width-1-2@m umartui_width-3-5@l">
            <div class="uk-card uk-card-small uk-card-body uk-card-default es-border es-input-100">
                <h3 class="uk-heading-bullet">
					<?php echo Text::_('COM_UMART_SYSTEM_EMAILS'); ?>
                </h3>
				<?php echo $this->form->getField('send_subject')->renderField(); ?>
				<?php echo $this->form->getField('send_body')->renderField(); ?>
            </div>
        </div>
    </div>
</div>
<?php echo $this->getFormLayout('foot'); ?>
