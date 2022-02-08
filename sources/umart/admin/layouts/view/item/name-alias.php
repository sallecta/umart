<?php
/**
 * @package     com_easyshop
 * @version     1.0.5
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

$name  = empty($displayData['fieldName']) ? 'name' : $displayData['fieldName'];
$alias = empty($displayData['fieldAlias']) ? 'alias' : $displayData['fieldAlias'];

?>
<div class="es-name-alias-group uk-padding-small uk-background-muted es-border">
    <div class="uk-grid-small" uk-grid>
        <div class="uk-width-1-1 uk-width-2-3@m uk-width-1-2@s">
			<?php echo $displayData['form']->getInput($name); ?>
        </div>
        <div class="uk-width-1-1 uk-width-1-3@m uk-width-1-2@s">
			<?php echo $displayData['form']->getInput($alias); ?>
        </div>
    </div>
</div>
