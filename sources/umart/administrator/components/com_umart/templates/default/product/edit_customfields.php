<?php
/**
 
 
 
 
 
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

$customFields = $this->form->getFieldsets('customfields');
$options      = $this->form->getFieldsets('options');
$optionFields = $this->item->option_fields;
$productId    = (int) $this->item->id;
?>

<?php if (count($customFields) || count($options)): ?>
	<?php HTMLHelper::_('umartui.addTab', Text::_('COM_UMART_FIELD_OPTION', true), 'indent'); ?>
    <div id="es-field-option-box" class="uk-grid-small uk-panel" uk-grid>
		<?php if (count($customFields)): ?>
            <div class="umartui_width-1-2@m">
                <div id="custom-fields-box"
                     class="uk-form-horizontal uk-card uk-card-small uk-card-default uk-card-body es-border">
                    <h3 class="uk-text-uppercase uk-heading-bullet">
						<?php echo Text::_('COM_UMART_CUSTOMFIELDS'); ?>
                    </h3>
                    <div data-zone-group class="uk-panel">
						<?php HTMLHelper::_('umartui.openTab', 'customfield'); ?>

						<?php foreach ($customFields as $name => $fieldSet): ?>

							<?php HTMLHelper::_('umartui.addTab', $fieldSet->label); ?>

							<?php foreach ($this->form->getFieldset($name) as $field): ?>
								<?php echo $field->renderField(); ?>
							<?php endforeach; ?>

							<?php HTMLHelper::_('umartui.endTab'); ?>

						<?php endforeach; ?>

						<?php echo HTMLHelper::_('umartui.renderTab', 'tab', ['responsive' => true]); ?>
                    </div>
                </div>
            </div>
		<?php endif; ?>
		<?php if (count($options)): ?>
            <div class="umartui_width-1-2@m">
                <div id="options-box" class="uk-card uk-card-small uk-card-default uk-card-body es-border">
                    <h3 class="uk-text-uppercase uk-heading-bullet">
						<?php echo Text::_('COM_UMART_OPTIONS'); ?>
                    </h3>
					<?php HTMLHelper::_('umartui.openTab', 'options'); ?>
                    <div class="uk-panel">
						<?php foreach ($options as $name => $fieldSet): $i = 0; ?>
							<?php HTMLHelper::_('umartui.addTab', $fieldSet->label); ?>
                            <table class="uk-table uk-table-small uk-table-striped">
                                <tbody>
								<?php foreach ($this->form->getFieldset($name) as $field): ?>
                                    <tr>
										<?php
										$optionId  = $field->getAttribute('name');
										$hasOption = isset($optionFields[$optionId]);
										$value     = $hasOption ? $optionFields[$optionId]['value'] : '{}';
										$disabled  = $productId > 0 && !$hasOption;

										if (is_array($value))
										{
											$value = json_encode($value);
										}

										?>
                                        <td><?php echo sprintf('%02d', ++$i); ?></td>
                                        <td><?php echo $field->label; ?></td>
                                        <td>
                                            <div class="uk-button-group">
                                                <button type="button"
                                                        class="uk-button uk-button-default uk-button-small es-button-disabled"
                                                        data-option-id="<?php echo $optionId; ?>">
														<span
                                                                uk-icon="icon: <?php echo $disabled ? 'close' : 'check'; ?>"></span>
                                                </button>
                                                <button type="button"
                                                        class="uk-button uk-button-small umartui_active uk-button-primary es-button-edit"
                                                        data-option-id="<?php echo $field->getAttribute('name'); ?>"
													<?php echo $disabled ? ' disabled' : ''; ?>>
                                                    <span uk-icon="icon: cog"></span>
                                                </button>
                                            </div>
                                            <div data-opt-value class="uk-hidden" uk-hidden>
                                                <input type="hidden" name="<?php echo $field->name; ?>"
													<?php echo $disabled ? ' disabled' : ''; ?>
                                                       value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>"/>
                                            </div>
                                        </td>
                                    </tr>
								<?php endforeach; ?>
                                </tbody>
                            </table>
							<?php HTMLHelper::_('umartui.endTab'); ?>

						<?php endforeach; ?>

						<?php echo HTMLHelper::_('umartui.renderTab'); ?>
                    </div>
                </div>
            </div>
			<?php
			$ajaxUrl = Route::_('index.php?option=com_umart&task=product.loadOptions', false);
			plg_sytem_umart_main('doc')->addScriptDeclaration(<<<JAVASCRIPT
_umart.$(document).ready(function ($) {
    $('#options-box button.es-button-edit').on('click', function () {
        var
            el = $(this),
            optionModal = $('#es-option-modal'),
            optionModalStack = $('#es-option-modal-stack'),
            optionId = el.data('optionId'),
            url = '{$ajaxUrl}',
            optEl = $('input[name^="jform[options][' + optionId + ']"]'),
            optValue = optEl.val();
        if (optEl.attr('name').toString().match(/\[\]$/gi)) {
            var arrayValue = $.parseJSON(optValue);
            if (arrayValue && arrayValue.length) {
                optValue = arrayValue[0].toString();
            }
        }

        _umart.ajax(url, {
            optionId: optionId,
            value: optValue
        }, function (response) {
            if (optionModal.length) {
                optionModal.remove();
            }
            if (optionModalStack.length) {
                optionModalStack.remove();
            }

            $('#umart_component').append(response.data);
            $('a[href="#es-option-modal-stack"]').trigger('countImages');
            _umart.umartui.modal('#es-option-modal').show();
        });
    });

    $('#options-box .es-button-disabled').on('click', function () {
        var
            el = $(this),
            row = el.parents('tr:eq(0)'),
            input = row.find('[data-opt-value]>input'),
            icon, disabled;
        row.prop('disabled', !input.prop('disabled'));
        disabled = row.prop('disabled');
        input.prop('disabled', disabled);
        icon = '<span uk-icon="icon:' + (disabled ? 'close' : 'check') + '"></span>';
        el.find('[uk-icon]').remove();
        el.append(icon);
        el.next('.es-button-edit').prop('disabled', disabled);
    });

    $(document).on('click', '#es-option-modal .es-button-value-add', function () {
        var
            group = $(this).parents('[data-option-group]:eq(0)'),
            clone = group.clone();
        if (group.siblings('[data-option-group]').length < 1 && group.attr('disabled')) {
            group.find('select, input, .es-button-remove').andSelf().removeAttr('disabled');
            clone.remove();
        } else {
            clone.find('select,input').val('').trigger('change');
            group.after(clone);
            clone.find('option[value="+"]').prop('selected', true);
        }
    });

    $(document).on('click', '#es-option-modal .es-button-remove', function (e) {
        e.preventDefault();
        var group = $(this).parents('[data-option-group]:eq(0)');
        if (group.siblings('[data-option-group]').length > 0) {
            group.remove();
        } else {
            group.find('select, input, .es-button-remove').andSelf().attr('disabled', 'disabled');
        }
    });

    $(document).on('click', 'a[href="#es-option-modal-stack"]', function (e) {
        e.preventDefault();
        var
            target = $(this),
            images = $('#es-list-image [name="jform[images][file_path][]"]'),
            optionTarget = target.parent().next('[data-option-group]:eq(0)');
        _umart.setData('optionTarget', optionTarget);

        if (images.length) {
            var
                html = '',
                curImages = _umart.getData('optionTarget').data('images');

            images.each(function () {
                var
                    image = $(this).val(),
                    thumb = $(this).parents('li:eq(0)').find('img:eq(0)');

                if (thumb.length) {
                    html += '<a href="#" data-image="' + image + '">'
                        + thumb.get(0).outerHTML + '</a>';
                }

            });
            $('#es-option-image-header').text($.trim(target.find('>span:eq(0)').text()));
            $('#es-option-images').html(html);

            if (curImages && curImages.length) {
                for (var i = 0, n = curImages.length; i < n; i++) {
                    $('#es-option-images [data-image="' + curImages[i] + '"]').addClass('es-option-image-selected');
                }
            }

            $('#es-image-insert').on('click', function (ev) {
                ev.preventDefault();
                ev.stopPropagation();
                var dataImages = [];

                $('.es-option-image-selected').each(function () {
                    dataImages.push($(this).data('image'));
                });

                _umart.getData('optionTarget').data('images', dataImages);
                target.trigger('countImages');
                _umart.umartui.modal('#es-option-modal-stack').hide();
            });

            _umart.umartui.modal('#es-option-modal-stack').show();
        }
    });

    $(document).on('countImages', '[href="#es-option-modal-stack"]', function () {
        var
            el = $(this),
            images = el.parent().next('[data-option-group]').data('images');
        if (images && images.length) {
            el.find('> .uk-badge').text(images.length).removeClass('uk-hidden');
        } else {
            el.find('> .uk-badge').text('').addClass('uk-hidden');
        }
    });

    $(document).on('click', '#es-option-images a', function (e) {
        e.preventDefault();
        $(this).toggleClass('es-option-image-selected');
    });

    $(document).on('click', '#es-button-save', function () {
        var
            el = $(this),
            optionId = el.data('optionId'),
            wrap = el.parents('#es-option-modal'),
            value = {},
            getValue = function (elOption) {
                var val = [];
                elOption.find('[data-option-group]').each(function () {
                    var
                        opt = $(this),
                        action = opt.find('[name="action"]').val(),
                        minQty = parseInt(opt.find('[name="min_quantity"]').val()),
                        price = parseFloat(opt.find('[name="price"]').val()),
                        currency = opt.find('[name="option_currency"]').val(),
                        images = opt.data('images');
                    if (price > 0.00) {
                        val.push({
                            action: action,
                            min_quantity: minQty,
                            price: price,
                            currency: currency,
                            images: images
                        });
                    } else {
                        if (images) {
                            val.push({images: images});
                        }
                    }
                });
                return val;
            };
        if (wrap.find('[data-option-value]').length) {
            // It's a dropdown or radio or checkboxes
            wrap.find('[data-option-value]').each(function () {
                var
                    el = $(this),
                    key = el.data('optionValue'),
                    children = el.find('[data-option-group]');
                if (children.length === 1 && children.attr('disabled')) {
                    value[key] = 'disabled';
                } else {
                    value[key] = getValue($(this));
                }
            });
        } else {
            // It's a single checkbox
            var children = wrap.find('[data-option-group]');

            if (children.length === 1 && children.attr('disabled')) {
                value = ['disabled'];
            } else {
                value = getValue(wrap);
            }
        }

        $('input[name^="jform[options][' + optionId + ']"]').val(JSON.stringify(value));
        UIkit.modal(wrap).hide();
    });
});
JAVASCRIPT
			);
			?>
		<?php endif; ?>
    </div>
	<?php HTMLHelper::_('umartui.endTab'); ?>
<?php endif; ?>
