<?php
/**
 
 
 
 
 
 */

use Joomla\CMS\Form\FormHelper;

defined('_JEXEC') or die;
FormHelper::loadFieldClass('password');

class JFormFieldToggle extends JFormFieldPassword
{
	protected $type = 'Toggle';

	protected function getInput()
	{
		static $js = false;

		if (!$js)
		{
			$js = true;
			plg_sytem_umart_main('doc')->addScriptDeclaration(<<<JS
_umart.$(document).ready(function ($) {
    var toggle = function () {
        $('.es-toggle-field-container > a:not(.handled)').each(function () {
            $(this).addClass('handled').on('click', function (e) {
	            e.preventDefault();
	            var input = $(this).next();            
	            input.attr('type', input.attr('type') === 'password' ? 'text' : 'password');
        	});
        });
    };
    
    $(document).on('DOMSubtreeModified', toggle);
    toggle();
});
JS
			);
		}

		$input = parent::getInput();
		$class = plg_sytem_umart_main('site') ? 'uk-position-relative' : 'uk-inline';

		return <<<HTML
<div class="{$class} es-toggle-field-container">
	<a class="uk-form-icon uk-form-icon-flip" href="#" uk-icon="icon: question"></a>
    {$input}
</div>
HTML;

	}
}
