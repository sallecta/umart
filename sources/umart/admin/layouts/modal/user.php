<?php

/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

extract($displayData);
$count            = count($users);
$iframeAttributes = [
	'src'    => JRoute::_('index.php?option=com_easyshop&view=users&layout=modal&tmpl=component' . ($vendor ? '&filter_vendor=1' : ''), false),
	'width'  => '100%',
	'height' => '450',
	'class'  => 'uk-height-large',
];

?>
<div class="uk-scope">
	<?php if ($multiple): ?>
		<?php ob_start(); ?>
        <div data-target-id="{value}">
            <div class="uk-padding-small uk-background-default uk-position-relative uk-text-center es-border uk-text-nowrap uk-text-truncate uk-margin-small-right"
                 style="width: 80px">
                <a class="uk-icon-link uk-position-small uk-position-top-right es-user-remove"
                   uk-icon="icon: trash"></a>
                <div class="uk-card-media-top">{image}</div>
                <div class="uk-h6 uk-margin-remove es-user-name">{name}</div>
                <input name="<?php echo $name; ?>" type="hidden" value="{value}"/>
            </div>
        </div>
	<?php $template = ob_get_clean(); ?>
        <button type="button" class="uk-button uk-button-primary uk-button-small"
                data-uk-toggle="target: #<?php echo $id; ?>_modal">
            <i class="uk-icon-user"></i>
			<?php echo JText::_('COM_EASYSHOP_SELECT'); ?>
        </button>

        <script type="text/es-js-template">
			<?php echo $template; ?>
        </script>

        <div id="<?php echo $id; ?>-list"
             class="es-user-field-list uk-placeholder uk-margin uk-padding-small uk-flex uk-flex-wrap"
             uk-sortable uk-margin>
			<?php

			if ($count)
			{
				foreach ($users as $user)
				{
					echo str_replace(['{value}', '{name}', '{image}'], [$user->id, $user->name, $user->avatar], $template);
				}
			}

			?>
        </div>
	<?php else:
	$activeId   = isset($users[0]) ? $users[0]->id : '';
	$activeName = isset($users[0]) ? $users[0]->name : '';
	?>
        <div class="uk-inline es-icon-input">
            <input type="hidden" name="<?php echo $name; ?>" id="<?php echo $id; ?>"
                   value="<?php echo $activeId; ?>"<?php echo $onChange; ?> />
            <input type="text" id="<?php echo $id; ?>_name" class="uk-input" readonly
                   value="<?php echo $activeName; ?>"
				<?php echo empty($hint) ? '' : 'placeholder="' . htmlspecialchars(JText::_($hint)) . '"'; ?>/>
            <a id="<?php echo $id; ?>_select"
               class="uk-form-icon uk-form-icon-flip<?php echo $activeId ? ' uk-hidden' : ''; ?>" href="#"
               uk-toggle="target:#<?php echo $id; ?>_modal"
               uk-icon="icon: user"></a>
            <a id="<?php echo $id; ?>_clear"
               class="uk-form-icon uk-form-icon-flip<?php echo !$activeId ? ' uk-hidden' : ''; ?>" href="#"
               onclick="_es.$(this).siblings('input').val('').trigger('change');"
               uk-icon="icon: close"></a>
        </div>
	<?php endif; ?>
    <div id="<?php echo $id; ?>_modal" class="uk-modal-container" uk-modal="stack: true">
        <div class="uk-modal-dialog uk-modal-body">
            <a class="uk-modal-close-default" uk-close></a>
			<?php if ($multiple): ?>
                <div class="uk-modal-header">
                    <button type="button" class="uk-button uk-button-primary es-button-insert">
                        <span uk-ico="icon: check"></span>
						<?php echo JText::_('COM_EASYSHOP_INSERT'); ?>
                    </button>
                </div>
			<?php endif; ?>
            <iframe data-attributes="<?php echo htmlspecialchars(json_encode($iframeAttributes), ENT_COMPAT, 'UTF-8'); ?>"></iframe>
        </div>
    </div>
</div>
