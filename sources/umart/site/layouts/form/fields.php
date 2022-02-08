<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use ES\Classes\Html;
use ES\Classes\Utility;
use Joomla\CMS\Form\FormHelper;

defined('_JEXEC') or die;

/**
 * @var array   $displayData
 * @var Utility $utility
 */

$utility = easyshop(Utility::class);

foreach ($displayData['fields'] as $field)
{
	$field->__set('labelclass', trim('uk-form-label es-form-label ' . $field->__get('labelclass')));
	$name      = $field->getAttribute('name');
	$type      = $field->getAttribute('type');
	$extraHint = $field->getAttribute('extraHint');
	$showOn    = $field->getAttribute('showon');
	$rel       = '';

	if (!empty($showOn))
	{
		$rel = ' data-showon=\'' .
			json_encode(FormHelper::parseShowOnConditions($showOn, $field->formControl, $field->group)) . '\'';

	}

	if (!$field->hint)
	{
		$field->hint = $field->getAttribute('label');
	}

	$hiddenLabel               = $field->getAttribute('hiddenLabel');
	$displayClass              = trim($field->getAttribute('displayClass', ''));
	$fieldDisplayData          = [
		'field'        => $field,
		'type'         => strtolower($field->type),
		'extraHint'    => $extraHint,
		'rel'          => $rel,
		'displayClass' => $displayClass,
		'hiddenLabel'  => !empty($hiddenLabel) && ($hiddenLabel === 'true' || $hiddenLabel === '1'),
	];
	$fieldDisplayData['label'] = $fieldDisplayData['hiddenLabel'] ? '' : $field->label;

	if (empty($displayData['renderValue']))
	{
		$fieldDisplayData['input'] = $field->input;
	}
	else
	{
		if (strcasecmp($fieldDisplayData['type'], 'FlatPicker') === 0)
		{
			easyshop(Html::class)->flatPicker();
			$fieldDisplayData['input'] = $field->value ? $utility->displayPicker($field->value, $field->getDisplayOptions()) : '';
		}
		elseif (strcasecmp($fieldDisplayData['type'], 'checkbox') === 0)
		{
			$fieldDisplayData['input'] = '<span uk-icon="icon: ' . ($field->value ? 'check' : 'close') . '"></span>';
		}
		else
		{
			$fieldDisplayData['input'] = $field->value;
		}
	}

	echo $displayData['renderer']->render('form.field', $fieldDisplayData);
}
