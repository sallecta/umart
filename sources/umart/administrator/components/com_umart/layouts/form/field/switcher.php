<?php
/**
 
 
 
 
 
 */

defined('_JEXEC') or die;
extract($displayData);
$format = '<input type="radio" id="%1$s" name="%2$s" value="%3$s" %4$s />';
$alt    = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);
?>
<div class="es-switcher">
    <div class="es-switcher-highlight<?php echo $value == '0' || $value == 'false' ? ' es-switcher-highlight-no' : ''; ?>"></div>
	<?php foreach ($options as $i => $option):
		$checked = ((string) $option->value === $value) ? 'checked="checked"' : '';
		$disabled = !empty($option->disable) || ($disabled && !$checked) ? 'disabled' : '';
		$onclick = !empty($option->onclick) ? 'onclick="' . $option->onclick . '"' : '';
		$onchange = !empty($option->onchange) ? 'onchange="' . $option->onchange . '"' : '';
		$oid = $id . $i;
		$ovalue = htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8');
		$attributes = array_filter(array($checked, 'class="' . $option->class . '"', $disabled, $onchange, $onclick));

		if ($required)
		{
			$attributes[] = 'required aria-required="true"';
		}
		?>
        <button class="<?php echo $checked ? 'active' : ''; ?>"
                type="button">
			<?php echo $option->text; ?>
			<?php echo sprintf($format, $oid, $name, $ovalue, implode(' ', $attributes)); ?>
        </button>
	<?php endforeach; ?>
</div>
