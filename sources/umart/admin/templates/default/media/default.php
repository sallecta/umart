<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

easyshop('addLangText', [
	'COM_EASYSHOP_INPUT_INVALID_REQUIRED',
	'COM_EASYSHOP_INPUT_INVALID_MIN',
	'COM_EASYSHOP_INPUT_INVALID_MAX',
	'COM_EASYSHOP_INPUT_INVALID_REGEX',
	'COM_EASYSHOP_REMOVE_CONFIRM',
]);

$paths          = $this->directory->get('list.path');
$type           = $this->isFile ? 'file' : 'image';
$this->method   = easyshop('app')->input->getString('method');
$uploadUrl      = $this->uri->get('media.ajax');
$removeDirUrl   = EasyshopHelperMedia::getLink($this->directory->get('base'), $type, 'media.removeFolder');
$removeMediaUrl = EasyshopHelperMedia::getLink($this->directory->get('base'), $type, 'media.remove');
$createDirUrl   = EasyshopHelperMedia::getLink($this->directory->get('base'), $type, 'media.createFolder');
$multiple       = easyshop('app')->input->get('multiple') == 'true' ? 'true' : 'false';

easyshop('doc')->addScriptDeclaration(<<<JAVASCRIPT
_es.$(function ($) {
    var bar = document.getElementById('es-progressbar');
    _es.uikit.upload('#es-media-upload', {
        url: '{$uploadUrl}',
        type: 'post',
        dataType: 'json',
        multiple: true,
        allow: '*.({$this->allowedFiles})',
        loadStart: function (e) {
            bar.removeAttribute('hidden');
            bar.max = e.total;
            bar.value = e.loaded;
        },
        progress: function (e) {
            bar.max = e.total;
            bar.value = e.loaded;
        },
        loadEnd: function (e) {
            bar.max = e.total;
            bar.value = e.loaded;
        },
        completeAll: function (response) {
            var res = $.parseJSON(response.responseText);
            if (res.success) {
                $('#es-files').html($(res.data).find('#es-files').html());
            } else {
                alert(res.message);
            }
            setTimeout(function () {
                bar.setAttribute('hidden', 'hidden');
            }, 1000);
        }
    });

    $('#es-component').on('click', '.files > .directory > .remove', function (e) {
        e.preventDefault();
        if (!confirm(_es.lang._('COM_EASYSHOP_REMOVE_CONFIRM'))) {
            return false;
        }
        var el = $(this);
        _es.$.ajax({
            url: '{$removeDirUrl}',
            type: 'post',
            dataType: 'json',
            data: {
                dirName: el.data('directory')
            },
            success: function (response) {
                var
                    data = response.data,
                    lblState = _es.$('[data-message]');

                if (!data.error) {
                    el.parent('.directory').remove();
                }

                lblState
                    .addClass(data.error ? 'uk-text-danger' : 'uk-text-success')
                    .html('<i class="fa fa-' + (data.error ? 'times-circle' : 'check-circle') + '"></i> ' + data.message)
                    .removeClass('uk-hidden');

                setTimeout(function () {
                    lblState.addClass('uk-hidden');
                }, 3500);
            }
        });
    });

    $('#es-component').on('click', '.files > .file > .remove', function (e) {
        e.preventDefault();
        if (!confirm(_es.lang._('COM_EASYSHOP_REMOVE_CONFIRM'))) {
            return false;
        }
        var el = $(this);
        _es.$.ajax({
            url: '{$removeMediaUrl}',
            type: 'post',
            dataType: 'json',
            data: {
                file: el.parent('.file').data('file')
            },
            success: function (response) {
                var lblState = _es.$('[data-message]');

                if (response.success) {
                    el.parent('.file').remove();
                }

                lblState
                    .attr('class', response.success ? 'uk-text-success' : 'uk-text-danger')
                    .html('<i class="fa fa-' + (response.success ? 'check-circle' : 'times-circle') + '"></i> ' + response.message)
                    .removeClass('uk-hidden');

                setTimeout(function () {
                    lblState.addClass('uk-hidden');
                }, 5000);
            }
        });
    });

    $('#es-component').on('click', '#es-folder > .create', function (e) {
        e.preventDefault();

        var el = $('#es-input-data');
        if (el.es_validate()) {
            $.ajax({
                url: '{$createDirUrl}',
                type: 'post',
                dataType: 'json',
                data: {
                    dirName: el.val()
                },
                success: function (response) {
                    var lblState = _es.$('[data-message]');
                    if (response.success) {
                        $('#es-files').html($(response.data).find('#es-files').html());
                    }

                    lblState
                        .attr('class', response.success ? 'uk-text-success' : 'uk-text-danger')
                        .html('<i class="fa fa-' + (response.success ? 'check-circle' : 'times-circle') + '"></i> ' + response.message)
                        .removeClass('uk-hidden');
                    el.val('');
                    setTimeout(function () {
                        lblState.addClass('uk-hidden');
                    }, 3500);
                }
            });
        }
    });

    if ('{$this->method}' === 'importMedia') {
        $('#es-component').on('click', '.file a', function (e) {
            e.preventDefault();

            if ({$multiple}) {
                $(this).toggleClass('es-file-selected');
            } else {
                $(this).parent().siblings().find('a').removeClass('es-file-selected');
                $(this).addClass('es-file-selected');
            }
        });

        $('#es-component').on('click', '.file-selected-reset', function () {
            $('#es-component #es-files .es-file-selected').removeClass('es-file-selected');
        });
    }
});
JAVASCRIPT
);

?>
<div id="es-body"
     class="<?php echo $this->method === 'importMedia' ? 'es-modal' : 'uk-width-3-4@m uk-width-4-5@xl uk-width-2-3@s'; ?>">
    <div id="es-media-bars" class="uk-margin">
		<?php echo $this->loadTemplate('breadcrumbs'); ?>
    </div>
    <div id="es-media">
        <div id="es-media-body">
			<?php echo $this->loadTemplate('body'); ?>
        </div>
        <div id="es-media-upload" class="uk-placeholder uk-text-center">
            <span uk-icon="icon: cloud-upload"></span>
            <span class="uk-text-middle">
				<?php echo Text::_('COM_EASYSHOP_ATTACH_IMAGES_OR_DROPPING'); ?>
			</span>
            <div uk-form-custom>
                <input id="es-upload-select" type="file" multiple/>
                <span class="uk-link">
				    <?php echo Text::_('COM_EASYSHOP_SELECT_FILES'); ?>
			    </span>
            </div>
        </div>
        <progress id="es-progressbar" class="uk-progress" value="0" max="100" hidden></progress>
    </div>
</div>
