<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Form\Form;
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
	HTMLHelper::_('ukui.addTab', Text::_('COM_EASYSHOP_GENERAL', true), 'info-circle');
	echo '<div class="uk-grid-small uk-grid-match uk-child-width-1-' . (empty($extraParams) ? 1 : 2) . '@s" data-zone-group uk-grid>'
		. '<div><div class="uk-card uk-card-small uk-card-default uk-card-body es-border">' . $general . '</div></div>';

	if (!empty($extraParams))
	{
		echo '<div><div class="uk-card uk-card-small uk-card-default uk-card-body es-border">' . $extraParams . '</div></div>';
	}

	echo '</div>';
	HTMLHelper::_('ukui.endTab');
}