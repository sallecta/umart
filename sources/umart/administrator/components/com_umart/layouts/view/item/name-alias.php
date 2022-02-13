<?php
/**
 
 * @version     1.0.5
 
 
 
 */
defined('_JEXEC') or die;

$name  = empty($displayData['fieldName']) ? 'name' : $displayData['fieldName'];
$alias = empty($displayData['fieldAlias']) ? 'alias' : $displayData['fieldAlias'];

?>
<div class="es-name-alias-group uk-padding-small uk-background-muted es-border">
    <div class="uk-grid-small" uk-grid>
        <div class="umartui_width-1-1 umartui_width-2-3@m umartui_width-1-2@s">
			<?php echo $displayData['form']->getInput($name); ?>
        </div>
        <div class="umartui_width-1-1 umartui_width-1-3@m umartui_width-1-2@s">
			<?php echo $displayData['form']->getInput($alias); ?>
        </div>
    </div>
</div>
