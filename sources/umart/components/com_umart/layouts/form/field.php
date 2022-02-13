<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * @var array $displayData
 */

$fieldType    = strtolower($displayData['type']);
$extraClasses = empty($displayData['displayClass']) ? '' : $displayData['displayClass'];
$validate     = '';

if ($validateRegexPattern = $displayData['field']->getAttribute('validate_regex_pattern'))
{
	$validate .= 'data-field-validate-regex="' . htmlspecialchars($validateRegexPattern) . '" ';
}

if ($validateRegexMsg = $displayData['field']->getAttribute('validate_regex_message'))
{
	$validate .= 'data-field-regex-message="' . htmlspecialchars($validateRegexMsg) . '"';
}

$description = $displayData['field']->description;

if (!empty($description) && 'true' !== $displayData['field']->getAttribute('hiddenDescription'))
{
	$displayData['input'] .= '<div class="uk-text-small uk-text-muted uk-margin-small-top uk-margin-small-bottom uk-text-italic" id="' . $displayData['field']->id . '-desc">' . Text::_($description) . '</div>';
}

?>
<div class="uk-margin es-form-row es-field-<?php echo $fieldType ?><?php echo $extraClasses ? ' ' . trim($extraClasses) : ''; ?>"
	<?php echo $validate; ?><?php echo $displayData['rel']; ?>>
    <label for="<?php echo $displayData['field']->id; ?>" class="<?php echo $displayData['field']->labelclass; ?>">
		<?php echo strip_tags($displayData['field']->label, '<i><span>'); ?>

		<?php if (!empty($displayData['extraHint'])): ?>
            <div class="checkout-field-price uk-display-inline-block">
				<?php echo $displayData['extraHint']; ?>
            </div>
		<?php endif; ?>
    </label>
    <div class="uk-form-controls es-form-controls">
		<?php echo $displayData['input']; ?>
    </div>
</div>
