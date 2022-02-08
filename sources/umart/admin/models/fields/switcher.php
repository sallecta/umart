<?php
/**
 * @package     com_easyshop
 * @version     1.0.5
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
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
			easyshop('doc')
				->addScriptDeclaration('
			    _es.$(document).ready(function($){	
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
			        
			        _es.$(document).on("runESSwitcher", runSwitcher);
			        _es.$(window).on("resize", runSwitcher);
			        runSwitcher();
			    });');
		}

		return easyshop('renderer')->render('form.field.switcher', $this->getLayoutData());
	}
}
