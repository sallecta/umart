<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
echo $this->getFormLayout('head');
plg_sytem_umart_main('addLangText', [
	'COM_UMART_OPTION_VALUE',
	'COM_UMART_OPTION_TEXT'
]);
plg_sytem_umart_main('doc')->addScriptDeclaration('
    _umart.$(document).ready(function ($) {
        _umart.umartui.sortable("#es-option-box .keep-js-table>tbody");
        if ($("#es-option-box .keep-js-table>tbody>tr").length < 1) {
            $("#es-option-box .keep-js-table>tbody").append(_umart.events.getOptionTemplate());
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
