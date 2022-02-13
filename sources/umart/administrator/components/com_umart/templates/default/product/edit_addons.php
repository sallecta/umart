<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('umartui.openTab', 'addons');

foreach ($this->addOns as $element => $form)
{
	$groups = $form->getGroup('');

	if (empty($groups))
	{
		continue;
	}

	HTMLHelper::_('umartui.addTab', Text::_('PLG_UMART_' . strtoupper($element) . '_ADDON_LABEL'));

	echo '<fieldset class="uk-fieldset uk-form-horizontal uk-card uk-card-small uk-card-default uk-card-body es-border">';

	foreach ($groups as $field)
	{
		echo $field->renderField();
	}

	echo '</fieldset>';

	HTMLHelper::_('umartui.endTab');
}

echo HTMLHelper::_('umartui.renderTab', 'tab-left', ['responsive' => true, 'tabId' => 'addOns']);
