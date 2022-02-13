<?php
/**
 
 * @version     1.0.5
 * @Author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2019 github.com/sallecta/umart All Rights Reserved.
 
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$view = $displayData;
Factory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "' . $view->getName() . '.cancel" || document.formvalidator.isValid(document.getElementById
		("item-form")))
		{
			Joomla.submitform(task, document.getElementById("item-form"));
		}
	};
');
?>
<form action="<?php echo Uri::getInstance()->toString(['path', 'query']); ?>"
      method="post" name="adminForm" id="item-form" data-form-validate class="form-validate">
    <fieldset class="uk-fieldset" uk-margin>
