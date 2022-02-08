<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('ukui.openTab', 'addons');

foreach ($this->addOns as $element => $form)
{
	$groups = $form->getGroup('');

	if (empty($groups))
	{
		continue;
	}

	HTMLHelper::_('ukui.addTab', Text::_('PLG_EASYSHOP_' . strtoupper($element) . '_ADDON_LABEL'));

	echo '<fieldset class="uk-fieldset uk-form-horizontal uk-card uk-card-small uk-card-default uk-card-body es-border">';

	foreach ($groups as $field)
	{
		echo $field->renderField();
	}

	echo '</fieldset>';

	HTMLHelper::_('ukui.endTab');
}

echo HTMLHelper::_('ukui.renderTab', 'tab-left', ['responsive' => true, 'tabId' => 'addOns']);
