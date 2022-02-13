<?php

/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
extract($displayData);
$link    = 'index.php?option=com_umart&view=media&method=importMedia&media_type=' . $mediaType . '&tmpl=component';
$isImage = $mediaType == 'image';
$rootUrl = JUri::root(true);

if ($multiple)
{
	$link .= '&multiple=true';
}

if (empty($thumb))
{
	$link .= '&thumb=0';
}

$iframeAttributes = [
	'src'    => JRoute::_($link, false),
	'width'  => '100%',
	'height' => '450',
];

?>

<div class="umartui_scope">
	<?php if ($multiple): ?>
        <button type="button" class="uk-button uk-button-primary uk-button-small"
                uk-toggle="target: #<?php echo $id; ?>_modal">
            <span uk-icon="icon: image"></span>
			<?php echo JText::_('COM_UMART_SELECT'); ?>
        </button>
	<?php ob_start(); ?>
        <div data-file="{value}">
            <div class="uk-position-relative uk-background-default uk-text-center es-border uk-text-nowrap uk-text-truncate uk-margin-small-right"
                 style="padding: 30px; width: 80px">
                <a class="uk-icon-link uk-position-small uk-position-top-right es-media-remove"
                   uk-icon="icon: trash"></a>
                <div class="uk-card-media-top">{image}</div>
                <div class="uk-h6 uk-margin-remove es-user-name">{name}</div>
                <input name="<?php echo $name; ?>" type="hidden" value="{value}"/>
            </div>
        </div>
	<?php $template = ob_get_clean(); ?>
        <script type="text/es-js-template">
			<?php echo $template; ?>
        </script>
        <div id="<?php echo $id; ?>-list"
             class="es-media-field-list uk-placeholder uk-margin uk-padding-small uk-flex uk-flex-wrap"
             uk-sortable uk-margin>
			<?php

			if (!empty($value))
			{
				foreach ($value as $file)
				{
					$name  = basename($file);
					$value = UmartHelper::filterMediaImage($file);

					if ($isImage)
					{
						$image = '<img src="' . UMART_MEDIA_URL . '/' . $value . '"/>';
					}
					else
					{
						$image = '<img src="' . UMART_MEDIA_URL . '/images/file.svg" width="80" height="80"/>';
					}

					$value = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
					echo str_replace(['{value}', '{name}', '{image}'], [$value, $name, $image], $template);
				}
			}

			?>
        </div>
	<?php else:
	if ($isImage && !empty($value))
	{
		$preview = '<img src="' . UMART_MEDIA_URL . '/' . UmartHelper::filterMediaImage($value) . '" width="250"/>';
		$title   = ' title="' . htmlspecialchars($preview) . '" uk-tooltip';
	}
	else
	{
		$title = '';
	}

	if ($readonly): ?>
	<?php echo empty($preview) ? '<img src="' . UMART_MEDIA_URL . '/images/no-image.png" title="No image" alt="No image"/>' : $preview; ?>
    <input
            type="hidden"
            name="<?php echo $name; ?>"
            id="<?php echo $id; ?>"
            class="uk-input"
            readonly="readonly"
            value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>"
            uk-tooltip<?php echo $title; ?>
		<?php echo $onChange; ?>/>
	<?php else: ?>
        <div class="uk-inline es-icon-input">
            <input
                    type="text"
                    name="<?php echo $name; ?>"
                    id="<?php echo $id; ?>"
                    class="uk-input"
                    readonly="readonly"
                    value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>"
                    uk-tooltip<?php echo $title; ?>
				<?php echo $onChange; ?>/>
            <a id="<?php echo $id; ?>_select"
               class="uk-form-icon uk-form-icon-flip<?php echo !empty($value) ? ' uk-hidden' : ''; ?>" href="#"
               uk-toggle="target:#<?php echo $id; ?>_modal"
               uk-icon="icon: <?php echo $mediaType; ?>"></a>
            <a id="<?php echo $id; ?>_clear"
               class="uk-form-icon uk-form-icon-flip<?php echo empty($value) ? ' uk-hidden' : ''; ?>"
               href="javascript: void(0);"
               onclick="_umart.$(this).siblings('input').val('').trigger('change');"
               uk-icon="icon: close"></a>
        </div>
	<?php endif; ?>
	<?php endif; ?>
    <div id="<?php echo $id; ?>_modal" class="uk-modal-container" uk-modal>
        <div class="uk-modal-dialog uk-modal-body">
            <a class="uk-modal-close-default" uk-close></a>
            <iframe data-attributes="<?php echo htmlspecialchars(json_encode($iframeAttributes), ENT_COMPAT, 'UTF-8'); ?>"></iframe>
        </div>
    </div>
</div>
