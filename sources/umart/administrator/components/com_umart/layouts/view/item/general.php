<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * @var object $displayData
 * @var Form   $form
 */

$form        = $displayData->get('form');
$general     = $form->renderFieldset('general');
$extraParams = $form->renderFieldset('extra_params');

if (!empty($general))
{
	HTMLHelper::_('umartui.addTab', Text::_('COM_UMART_GENERAL', true), 'info-circle');
	echo '<div class="uk-grid-small uk-grid-match uk-child-width-1-' . (empty($extraParams) ? 1 : 2) . '@s" data-zone-group uk-grid>'
		. '<div><div class="uk-card uk-card-small uk-card-default uk-card-body es-border">' . $general . '</div></div>';

	if (!empty($extraParams))
	{
		echo '<div><div class="uk-card uk-card-small uk-card-default uk-card-body es-border">' . $extraParams . '</div></div>';
	}

	echo '</div>';
	HTMLHelper::_('umartui.endTab');
}