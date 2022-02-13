<?php
/**
 
 * @version     1.0.5
 
 
 
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('radio');

class JFormFieldSwitcher extends JFormFieldRadio
{
	protected $type = 'switcher';

	protected function getInput()
	{
		static $style = false;

		if (!$style)
		{
			$style = true;
			plg_sytem_umart_main('doc')
				->addScriptDeclaration('
			    _umart.$(document).ready(function($){	
			    	var setHighLight = function (highLight, el) {
			    	    highLight.show();
			    	    var elWidth = el.outerWidth();
			    	    var elPosLft = el.position().left;		    	    
			    	    
			    	    if (elWidth < 1 || elPosLft < 1) {
			    	        var parent = el.parent(".es-switcher");
			    	        clone = parent.clone().appendTo($("body"));
			    	        el = clone.find("button.active");			    	       
			    	        elWidth = el.outerWidth();
			    	        elPosLft = el.position().left;        
			    	        clone.remove();
			    	    }
			    	    
			    	    highLight.css({
				            left: elPosLft,
				            width: elWidth,
				            display: "block"
			            });
			    	};
			    	
			        $(document).on("click", ".es-switcher button", function () {
			            var 
			                el = $(this)
			                , radio = el.find("input")
				            , activeValue = radio.val()
				            , highLight = el.siblings(".es-switcher-highlight");				        			        
				        el.addClass("active");
				        radio.prop("checked", true).trigger("change");
				        setHighLight(highLight, el);
				        			            		            
			            if (activeValue == "0" || activeValue == "false") {
			                highLight.addClass("es-switcher-highlight-no");
			            } else {
			                highLight.removeClass("es-switcher-highlight-no");
			            }
			                    
			            el.siblings("button").removeClass("active");
			        });
			        
			        var runSwitcher = function() {
			            $(".es-switcher button.active").each(function () {
				            var el = $(this)
				                , highLight = el.siblings(".es-switcher-highlight");
				            setHighLight(highLight, el);
			            });
			        };
			        
			        _umart.$(document).on("runUmartSwitcher", runSwitcher);
			        _umart.$(window).on("resize", runSwitcher);
			        runSwitcher();
			    });');
		}

		return plg_sytem_umart_main('renderer')->render('form.field.switcher', $this->getLayoutData());
	}
}
