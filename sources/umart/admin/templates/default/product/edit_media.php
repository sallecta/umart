<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\Media;
use ES\Classes\Html;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

easyshop(Html::class)->jui(['sortable']);
$this->fileEnabled = PluginHelper::isEnabled('easyshop', 'file');
Text::script('COM_EASYSHOP_WARNING_NO_IMAGE_SELECTED');
Text::script('COM_EASYSHOP_WARNING_NO_FILES_SELECTED');

?>
    <div id="es-product-media" class="uk-grid-small uk-child-width-1-2@m es-edit-panel" uk-grid>
        <div>
            <div class="uk-panel uk-card uk-card-small uk-card-default uk-card-body es-border">
                <h3 class="uk-heading-bullet uk-text-uppercase">
					<?php echo Text::_('COM_EASYSHOP_MEDIA'); ?>
                    <a href="#es-media-modal-image" uk-toggle
                       class="uk-button uk-button-primary uk-button-small">
                        <i class="fa fa-plus-circle"></i>
                    </a>
                </h3>
				<?php ob_start(); ?>
                <h3 class="uk-accordion-title">
                    ##preview##
                    ##file_name##
                    <a class="uk-text-danger es-media-remove">
                        <i class="fa fa-times-circle"></i>
                    </a>
                </h3>
                <div class="uk-accordion-content">
                    <div class="uk-card uk-card-default uk-card-body uk-background-muted es-border es-input-100">
                        <div class="uk-clearfix uk-margin"></div>
                        <input type="hidden" name="jform[images][file_path][]"
                               value="##file_path##"/>
                        <input type="text"
                               name="jform[images][title][]"
                               class="uk-input"
                               placeholder="<?php echo Text::_('COM_EASYSHOP_TITLE'); ?>"
                               value="##title##"/>
                        <textarea class="uk-textarea uk-margin-small"
                                  cols="20" rows="3"
                                  placeholder="<?php echo Text::_('COM_EASYSHOP_DESCRIPTION'); ?>"
                                  name="jform[images][description][]">##description##</textarea>

                    </div>
                </div>
				<?php $imageTemplate = ob_get_clean(); ?>
                <script type="product-image/template">
					<?php echo $imageTemplate; ?>
                </script>
                <ul id="es-list-image" uk-accordion="multiple: true">
					<?php if (!empty($this->item->images)):
						/** @var Media $mediaClass */
						$mediaClass = easyshop(Media::class);

						?>
						<?php foreach ($this->item->images as $media): ?>
                        <li>
							<?php

							$mime    = $mediaClass->getMimeByFile(ES_MEDIA . '/' . $media->file_path);
							$isVideo = strpos($mime, 'video') === 0;

							if ($isVideo)
							{
								$preview = '<span uk-icon="icon: play-circle; ratio: 1.5"></span>';
							}
							else
							{
								$preview = '<img src="' . $media->tiny . '" alt=""/>';
							}

							echo str_replace(
								[
									'##file_path##',
									'##file_name##',
									'##preview##',
									'##title##',
									'##description##',
								],
								[
									htmlspecialchars($media->file_path),
									htmlspecialchars(basename($media->file_path)),
									$preview,
									htmlspecialchars($media->title),
									htmlspecialchars($media->description),
								],
								$imageTemplate);
							?>
                        </li>
					<?php endforeach; ?>
					<?php endif; ?>
                </ul>
            </div>
        </div>
		<?php if ($this->fileEnabled): ?>
			<?php ob_start(); ?>
            <h3 class="uk-accordion-title">
                <span uk-icon="icon: file"></span>
                ##file_name##
                <a class="uk-text-danger es-media-remove">
                    <i class="fa fa-times-circle"></i>
                </a>
            </h3>
            <div class="uk-accordion-content">
                <div class="uk-card uk-card-default uk-card-body uk-background-muted es-border es-input-100">
                    <div class="uk-clearfix uk-margin"></div>
                    <input type="hidden" name="jform[files][file_path][##index##]"
                           value="##file_path##"/>
                    <input type="text" name="jform[files][title][##index##]"
                           class="uk-input"
                           value="##title##"
                           placeholder="<?php echo Text::_('COM_EASYSHOP_TITLE'); ?>"/>
                    <textarea class="uk-textarea uk-margin-small"
                              cols="20"
                              rows="3"
                              name="jform[files][description][##index##]"
                              placeholder="<?php echo Text::_('COM_EASYSHOP_DESCRIPTION'); ?>">##description##</textarea>
                    <div class="uk-margin-small-bottom">
                        <div class="uk-text-bold uk-margin-small-bottom">
                            <span uk-icon="icon: lock"></span>
							<?php echo Text::_('COM_EASYSHOP_ACCESS'); ?>
                        </div>
						<?php
						$field     = $this->form->getField('user_groups', 'files');
						$fieldName = 'jform[files][user_groups][##index##][]';
						$field->__set('value', null);
						echo str_replace($field->name, $fieldName, $field->renderField());

						$field     = $this->form->getField('order_status', 'files');
						$fieldName = 'jform[files][order_status][##index##][]';
						$field->__set('value', null);
						echo str_replace($field->name, $fieldName, $field->renderField());

						$field     = $this->form->getField('order_paid', 'files');
						$fieldName = 'jform[files][order_paid][##index##]';
						$field->__set('value', null);
						echo str_replace($field->name, $fieldName, $field->renderField());

						$field     = $this->form->getField('durations', 'files');
						$fieldName = 'jform[files][durations][##index##]';
						$field->__set('value', null);
						echo str_replace($field->name, $fieldName, $field->renderField());
						?>
                    </div>
                </div>
            </div>
		<?php $fileTemplate = ob_get_clean(); ?>
            <script type="product-file/template">
				<?php echo $fileTemplate; ?>
            </script>
            <div>
                <div class="uk-panel uk-card uk-card-small uk-card-default uk-card-body es-border">
                    <h3 class="uk-heading-bullet uk-text-uppercase">
						<?php echo Text::_('COM_EASYSHOP_FILES'); ?>
                        <a href="#es-media-modal-file" uk-toggle
                           class="uk-button uk-button-primary uk-button-small">
                            <i class="fa fa-plus-circle"></i>
                        </a>
                    </h3>
                    <ul id="es-list-file" uk-accordion="multiple: true">
						<?php if (!empty($this->item->files)):
							$fileAccess = [];
							?>
							<?php foreach ($this->item->files as $index => $file): ?>
                            <li>
								<?php
								$params             = new Registry($file->params);
								$fileAccess[$index] = $params->toArray();
								echo str_replace(
									[
										'##file_name##',
										'##file_path##',
										'##title##',
										'##description##',
										'##index##',
									],
									[
										htmlspecialchars(basename($file->file_path)),
										htmlspecialchars($file->file_path),
										htmlspecialchars($file->title),
										htmlspecialchars($file->description),
										$index,
									],
									$fileTemplate);
								?>
                            </li>
						<?php endforeach; ?>
						<?php endif; ?>
                    </ul>
                </div>
            </div>
		<?php endif; ?>
    </div>

<?php

Text::script('COM_EASYSHOP_REMOVE_CONFIRM');
$hasFile    = isset($fileTemplate) ? 'true' : 'false';
$fileAccess = !empty($fileAccess) ? json_encode($fileAccess) : '[]';
easyshop('doc')->addScriptDeclaration(<<<JAVASCRIPT

jQuery(document).ready(function ($) {
    $('#es-media-modal-image, #es-media-modal-file').on('beforeshow', function () {
        var modal = $(this);
        var iframe = modal.find('iframe');

        if (!modal.data('iframeHandled')) {
            modal.data('iframeHandled', true);
            iframe.attr(iframe.data('attributes'));
        }
    });

    var updateIndex = function () {
        $('#es-list-file > li').each(function (index) {
            $(this).find('[name^="jform[files]"]').each(function () {
                var name = $(this).attr('name').replace(/\[[0-9]+\]/, '[' + index + ']');
                $(this).attr('name', name);
            });
        });
    };

    $('#es-product-media').find('[uk-accordion]').sortable({
        update: function (e, ui) {
            updateIndex();
        }
    });

    $('#es-list-image').on('click', 'a.es-media-remove', function (e) {
        e.preventDefault();
        if (confirm(Joomla.Text._('COM_EASYSHOP_REMOVE_CONFIRM'))) {
            $(this).parents('li:eq(0)').remove();
        }
    });

    $('#es-list-file').on('click', 'a.es-media-remove', function (e) {
        e.preventDefault();
        if (confirm(Joomla.Text._('COM_EASYSHOP_REMOVE_CONFIRM'))) {
            $(this).parents('li:eq(0)').remove();
            updateIndex();
        }
    });

    $('#es-product-media [uk-accordion]').disableSelection();
    var imageTemplate = $('<span>' + $('script[type="product-image/template"]').text() + '</span>');
    var hasFile = {$hasFile};

    if (hasFile) {
        var fileAccess = {$fileAccess};
        var fileTemplate = $('<span>' + $('script[type="product-file/template"]').text() + '</span>');
        for (var i = 0, n = fileAccess.length; i < n; i++) {
            var file = fileAccess[i], s;
            s = $('[name="jform[files][user_groups][' + i + '][]"]');
            s.find('>option').prop('selected', false);
            if (typeof file.user_groups === 'object') {
                $.each(file.user_groups, function (idx, v) {
                    s.find('>option[value="' + v + '"]').prop('selected', true);
                });
            }

            s = $('[name="jform[files][order_status][' + i + '][]"]');
            s.find('>option').prop('selected', false);
            if (typeof file.order_status === 'object') {
                $.each(file.order_status, function (idx, v) {
                    s.find('>option[value="' + v + '"]').prop('selected', true);
                });
            }

            s = $('[name="jform[files][order_paid][' + i + ']"]');
            if (typeof file.order_paid === 'string' || typeof file.order_paid === 'number') {
                s.find('>option[value="' + file.order_paid + '"]').prop('selected', true);
            }

            s = $('[name="jform[files][durations][' + i + ']"]');
            if (typeof file.durations === 'string' || typeof file.durations === 'number') {
                s.val(file.durations);
            }
        }

        $('#es-list-file select').trigger('liszt:updated');
    }

    $('#es-iframe-image, #es-iframe-file').each(function () {
        $(this).on('load', function () {
            var
                contents = $(this).contents(),
                isFile = $(this).attr('id') === 'es-iframe-file';
            contents.find('.file-selected-insert').on('click', function () {
                var filesSelected = contents.find('#es-files .es-file-selected'),
                    i, n, file, t, fPath;
                if (filesSelected.length < 1) {
                    alert(Joomla.Text._('COM_EASYSHOP_WARNING_NO_' + (isFile ? 'FILES' : 'IMAGE') + '_SELECTED'));
                    return false;
                }

                if (isFile) {
                    for (i = 0, n = filesSelected.length; i < n; i++) {
                        file = filesSelected.eq(i);
                        fPath = file.parent().data('file');

                        if ($('#es-list-file [value="' + fPath + '"]').length) {
                            continue;
                        }

                        t = fileTemplate.html()
                            .replace(/##file_name##/g, file.find('.name').text())
                            .replace(/##title##/g, '')
                            .replace(/##description##/g, '')
                            .replace(/##file_path##/g, fPath)
                            .replace(/##index##/g, $('#es-list-file > li:last').index() + 1);
                        $('#es-list-file').append($('<li/>').html(t));
                        _es.initChosen('#es-list-file');
                    }
                    _es.uikit.modal('#es-media-modal-file').hide();
                } else {
                    for (i = 0, n = filesSelected.length; i < n; i++) {
                        file = filesSelected.eq(i);
                        fPath = file.parent().data('file');

                        if ($('#es-list-image [value="' + fPath + '"]').length) {
                            continue;
                        }

                        t = imageTemplate.html()
                            .replace(/##file_name##/g, file.find('.name').text())
                            .replace(/##title##/g, '')
                            .replace(/##description##/g, '')
                            .replace(/##file_path##/g, fPath)
                            .replace(/##src##/g, file.find('img').attr('src'));

                        if (file.attr('type') === 'video') {
                            t = t.replace(/##preview##/g, '<span uk-icon="icon: play-circle; ratio: 1.5"></span>');
                        } else {
                            t = t.replace(/##preview##/g, '<img src="' + file.find('img').attr('src') + '" alt=""/>');
                        }

                        $('#es-list-image').append($('<li/>').html(t));
                    }
                    _es.uikit.modal('#es-media-modal-image').hide();
                }

                contents.find('#es-files .es-file-selected').removeClass('es-file-selected');
            });
        });
    });
});

JAVASCRIPT
);

echo $this->loadTemplate('modal');
