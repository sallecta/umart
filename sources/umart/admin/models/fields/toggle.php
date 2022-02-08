<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
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
			easyshop('doc')->addScriptDeclaration(<<<JS
_es.$(document).ready(function ($) {
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
		$class = easyshop('site') ? 'uk-position-relative' : 'uk-inline';

		return <<<HTML
<div class="{$class} es-toggle-field-container">
	<a class="uk-form-icon uk-form-icon-flip" href="#" uk-icon="icon: question"></a>
    {$input}
</div>
HTML;

	}
}
