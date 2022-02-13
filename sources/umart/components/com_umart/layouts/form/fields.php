<?php
/**
 
 
 
 
 
 */

use Umart\Classes\Html;
use Umart\Classes\Utility;
use Joomla\CMS\Form\FormHelper;

defined('_JEXEC') or die;

/**
 * @var array   $displayData
 * @var Utility $utility
 */

$utility = plg_sytem_umart_main(Utility::class);

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
			plg_sytem_umart_main(Html::class)->flatPicker();
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
