<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\Addon;
use Joomla\CMS\Language\Text;

echo $this->getFormLayout('head');

if (!$this->item->id)
{
	easyshop('doc')->addScriptDeclaration('
		_es.$(document).ready(function($){
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

$addOns = easyshop(Addon::class)
	->getAddons('email', (int) $this->item->id);

?>
<div id="es-email-edit">
    <div class="uk-grid-small uk-grid-match" uk-grid>
        <div class="uk-width-1-2@m uk-width-2-5@l">
            <div class="uk-card uk-card-small uk-card-body uk-card-default es-border es-input-100">
                <h3 class="uk-heading-bullet">
					<?php echo Text::_('COM_EASYSHOP_GENERAL'); ?>
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
						<?php echo Text::_('PLG_EASYSHOP_' . strtoupper($element) . '_ADDON_LABEL'); ?>
                    </h4>
					<?php foreach ($groups as $field): ?>
						<?php echo $field->renderField(); ?>
					<?php endforeach; ?>
				<?php endif; ?>
				<?php endforeach; ?>
            </div>
        </div>
        <div class="uk-width-1-2@m uk-width-3-5@l">
            <div class="uk-card uk-card-small uk-card-body uk-card-default es-border es-input-100">
                <h3 class="uk-heading-bullet">
					<?php echo Text::_('COM_EASYSHOP_SYSTEM_EMAILS'); ?>
                </h3>
				<?php echo $this->form->getField('send_subject')->renderField(); ?>
				<?php echo $this->form->getField('send_body')->renderField(); ?>
            </div>
        </div>
    </div>
</div>
<?php echo $this->getFormLayout('foot'); ?>
