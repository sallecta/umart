<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;
echo $this->getFormLayout('head');
easyshop('addLangText', [
	'COM_EASYSHOP_OPTION_VALUE',
	'COM_EASYSHOP_OPTION_TEXT'
]);
easyshop('doc')->addScriptDeclaration('
    _es.$(document).ready(function ($) {
        _es.uikit.sortable("#es-option-box .keep-js-table>tbody");
        if ($("#es-option-box .keep-js-table>tbody>tr").length < 1) {
            $("#es-option-box .keep-js-table>tbody").append(_es.events.getOptionTemplate());
        }
        
        var loadAssignByGroup = function(){ 
            $("#item-form").find("input[name=\'customFieldGroupId\']").val($("#jform_type").val());
            Joomla.submitform("customfield.loadAssignByGroup", document.getElementById("item-form"));
        };

        $("#jform_type").on("change", loadAssignByGroup);
    });
');
?>
<?php echo $this->getRenderer()->render('view.item.name-alias', ['form' => $this->form]); ?>
<div id="es-edit-field-page" class="uk-grid-small uk-grid-match uk-child-width-1-2@s" uk-grid>
    <div>
        <div class="uk-card uk-card-body uk-card-small uk-card-default es-border">
            <div class="uk-form-horizontal" data-zone-group>
				<?php echo $this->form->renderFieldset('general'); ?>
            </div>
        </div>
    </div>
    <div id="es-option-box" class="uk-form-horizontal">
        <div class="uk-card uk-card-body uk-card-small uk-card-default es-border">
			<?php echo $this->form->renderFieldset('options'); ?>
        </div>
    </div>
</div>
<input type="hidden" name="reflector" value="<?php echo $this->escape($this->state->get('filter.reflector')); ?>"/>
<input type="hidden" name="returnPage" value="<?php echo base64_encode(JUri::getInstance()->toString()); ?>"/>
<input type="hidden" name="customFieldGroupId" value=""/>
<?php echo $this->getFormLayout('foot'); ?>
