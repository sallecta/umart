<?php

/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
extract($displayData);
$count            = count($orders);
$iframeAttributes = [
	'src'    => JRoute::_('index.php?option=com_umart&view=orders&layout=modal&tmpl=component', false),
	'width'  => '100%',
	'height' => '450',
	'class'  => 'uk-height-large',
];

?>

<div class="umartui_scope">
	<?php if ($multiple): ?>
        <button type="button" class="uk-button uk-button-primary uk-button-small"
                uk-toggle="target: #<?php echo $id; ?>_modal">
            <span uk-icon="icon: code"></span>
			<?php echo JText::_('COM_UMART_SELECT'); ?>
        </button>

		<?php ob_start(); ?>
        <li data-target-id="{value}" class="uk-position-relative">
            <a class="uk-icon-link uk-position-center-right es-order-remove"
               uk-icon="icon: trash"></a>
            <span uk-icon="icon: move"></span>
            <span class="es-order-code">{code}</span>
            <input name="<?php echo $name; ?>" type="hidden" value="{value}"/>
        </li>
		<?php $template = ob_get_clean(); ?>
        <div class="uk-placeholder uk-margin uk-padding-small umartui_width-medium">
            <script type="text/es-js-template">
				<?php echo $template; ?>
            </script>
            <ul id="<?php echo $id; ?>-list" class="uk-list uk-list-divider" uk-sortable>

				<?php

				if ($count)
				{
					foreach ($orders as $order)
					{
						echo str_replace(['{value}', '{code}'], [$order->id, $order->order_code], $template);
					}
				}

				?>

				<?php if (count($orders)): ?>
					<?php foreach ($orders as $order):
						$options[] = '<option value="' . $order->id . '" selected></option>';
						?>
                        <li data-target-id="<?php echo $order->id; ?>">
                            <div class="uk-clearfix">
                                <i class="fa fa-sort"></i>
								<?php echo $order->order_code; ?>
                                <button type="button" class="uk-float-right uk-button uk-button-link uk-button-small">
                                    <i class="fa fa-times uk-text-danger"></i>
                                </button>
                            </div>
                        </li>
					<?php endforeach; ?>
				<?php endif; ?>
            </ul>
        </div>
	<?php else:
		$activeId = isset($orders[0]) ? $orders[0]->id : '';
		$activeCode = isset($orders[0]) ? $orders[0]->order_code : '';
		?>
        <div class="uk-inline es-icon-input">
            <input type="text" id="<?php echo $id; ?>_name" class="uk-input" readonly
                   value="<?php echo $activeCode; ?>"
				<?php echo empty($hint) ? '' : 'placeholder="' . htmlspecialchars(JText::_($hint)) . '"'; ?>/>
            <input type="hidden" name="<?php echo $name; ?>" id="<?php echo $id; ?>"
                   value="<?php echo $activeId; ?>"
				<?php echo $onChange; ?>/>
            <a id="<?php echo $id; ?>_select"
               class="uk-form-icon uk-form-icon-flip<?php echo $activeId ? ' uk-hidden' : ''; ?>" href="#"
               uk-toggle="target:#<?php echo $id; ?>_modal"
               uk-icon="icon: plus"></a>
            <a id="<?php echo $id; ?>_clear"
               class="uk-form-icon uk-form-icon-flip<?php echo !$activeId ? ' uk-hidden' : ''; ?>" href="#"
               onclick="_umart.$(this).siblings('input').val('').trigger('change');"
               uk-icon="icon: close"></a>
        </div>
	<?php endif; ?>
    <div id="<?php echo $id; ?>_modal" class="uk-modal-container" uk-modal>
        <div class="uk-modal-dialog uk-modal-body">
            <a class="uk-modal-close-default" uk-close></a>
			<?php if ($multiple): ?>
                <div class="uk-modal-header">
                    <button type="button" class="uk-button uk-button-primary es-button-insert">
                        <span uk-icon="icon: check"></span>
						<?php echo JText::_('COM_UMART_INSERT'); ?>
                    </button>
                </div>
			<?php endif; ?>
            <iframe data-attributes="<?php echo htmlspecialchars(json_encode($iframeAttributes), ENT_COMPAT, 'UTF-8'); ?>"></iframe>
        </div>
    </div>
</div>
