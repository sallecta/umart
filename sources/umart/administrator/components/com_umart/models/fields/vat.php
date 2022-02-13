<?php
/**
 
 
 
 
 
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('text');

class JFormFieldVat extends JFormFieldText
{
	protected $type = 'vat';

	protected function getInput()
	{
		if (class_exists('SoapClient'))
		{
			plg_sytem_umart_main('doc')->addScriptDeclaration('_umart.$(document).ready(function($){
				$(document).on("click", "#' . $this->id . '_check", function(e){
					e.preventDefault();					
					var el = $(this);					
					var vat = $.trim($("#' . $this->id . '").val());
					var successMsg = "' . JText::_('COM_UMART_CHECK_VAT_SUCCESS', true) . '";
					var successErr = "' . JText::_('COM_UMART_CHECK_VAT_FAIL', true) . '";
					var required = "' . $this->getAttribute('required') . '";
					if(vat == "" && (required == "" || required == "0" || required == "false")){
						return;
					}
					el.find("[uk-spinner]").removeClass("uk-hidden");
					_umart.ajax("' . JUri::root(true) . '/index.php?option=com_umart&task=ajax.validateVat", {
						vatNumber: vat,
						required: required,
						umartArea: $("umartArea") // None Area
					}, function(response){		
						el.find("[uk-spinner]").addClass("uk-hidden");			
						if(response.data){
							_umart.umartui.notification("<span uk-icon=\'icon: check\'></span> " + successMsg, {status: "success"});
						}else{
							_umart.umartui.notification("<span uk-icon=\'icon: warning\'></span> " + successErr, {status: "warning"});
						}
					});
				});
			});');
		}
		else
		{
			plg_sytem_umart_main('app')->enqueueMessage(JText::_('COM_UMART_WARNING_VAT_REQUIRE_SOAP_CLIENT'), 'warning');
		}

		return '<div class="uk-inline" style="width: 100%;"><a href="#" id="' . $this->id . '_check" class="uk-form-icon uk-form-icon-flip"'
			. ' uk-icon="icon: play"><span uk-spinner class="uk-hidden"></span></a>' . parent::getInput() . '</div>';

	}
}
