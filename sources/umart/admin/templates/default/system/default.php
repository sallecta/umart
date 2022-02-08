<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use ES\Classes\System;
use ES\Helper\Navbar;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

defined('_JEXEC') or die;
echo Navbar::render();

if (!is_dir(JPATH_SITE . '/media/com_easyshop/images'))
{
	@JFolder::create(JPATH_SITE . '/media/com_easyshop/assets/images', 0755);
}
$systemAfterInformation = easyshop('app')->triggerEvent('onEasyshopSystemAfterInformation');
$systemInfo             = [
	'PHP version'            => PHP_VERSION,
	'Joomla! version'        => JVERSION,
	'EasyShop version'       => ES_VERSION,
	'CURL'                   => extension_loaded('curl') && function_exists('curl_version') ? true : false,
	'Image GD library'       => extension_loaded('gd') && function_exists('gd_info') ? true : false,
	'Media path is writable' => is_writable(JPATH_SITE . '/media/com_easyshop/assets/images') ? true : false,
];

$ajaxUrl = Route::_('index.php?option=com_easyshop&task=system.regenerateThumbnails', false);
$token   = Session::getFormToken();
easyshop('doc')->addScriptDeclaration(<<<JAVASCRIPT
_es.$(document).ready(function ($) {
    $('#es-regenerate-images').on('click', function (e) {
        e.preventDefault();
        _es.uikit.modal.confirm('Click OK to confirm and continue progress!').then(function () {
            if (!window.XMLHttpRequest) {
                alert('Your browser\'s not support XMLHttpRequest');
                return false;
            }

            try {
                var
                    bar = document.getElementById('es-progressbar'),
                    text = document.getElementById('es-progress-text'),
                    xhr = new window.XMLHttpRequest,
                    response;

                bar.removeAttribute('hidden');
                text.removeAttribute('hidden');
                text.innerText = '0%';
                bar.value = 0;
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4) {
                        text.innerText = '100%';
                        bar.value = 100;

                        setTimeout(function () {
                            bar.setAttribute('hidden', 'hidden');
                            text.setAttribute('hidden', 'hidden');
                        }, 400);

                        _es.uikit.notification('<span uk-icon="icon: check"></span> Completed!', {status: 'success'});

                    } else if (xhr.readyState > 2) {
                        response = $.trim(xhr.responseText).replace(/^\[|\]$/gmi, '').split('][');

                        if (response.length) {
                            response = $.parseJSON(response[response.length - 1].replace(/\s+$/gmi, ''));

                            if (response.type === 'Error') {
                                _es.uikit.notification('<span uk-icon="icon: close"></span> ' + response.message, {status: 'warning'});
                            } else {
                                bar.value = response.progress;
                                text.innerText = response.progress + '%';
                                _es.uikit.notification.closeAll();
                                _es.uikit.notification('<span uk-icon="icon: thumbnails"></span> ' + response.message, {status: 'success'});
                            }
                        }
                    }
                };

                xhr.open('POST', '{$ajaxUrl}', true);
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhr.send('{$token}=1');
            } catch (Err) {
                _es.uikit.notification('<span uk-icon="icon: close"></span> ' + Err, {status: 'danger'});
            }

        }, function () {

        });
    });
});
JAVASCRIPT
);

?>
<div id="es-body" class="uk-width-3-4@m uk-width-4-5@xl uk-width-2-3@s">
    <div class="uk-child-width-1-2@s" uk-grid>
        <div>
            <h4 class="uk-margin-remove uk-text-uppercase"><span uk-icon="icon: server"></span> Main information</h4>
            <table class="uk-table uk-table-small uk-table-divider uk-table-responsive">
                <tbody>
				<?php foreach ($systemInfo as $text => $value): ?>
                    <tr>
                        <th class="uk-table-shrink uk-text-nowrap">
							<?php echo $text; ?>
                        </th>
                        <td class="uk-table-expand">
							<?php if (is_bool($value)): ?>
								<?php if ($value): ?>
                                    <span class="uk-text-success" uk-icon="icon: check"></span>
								<?php else: ?>
                                    <span class="uk-text-danger" uk-icon="icon: close"></span>
								<?php endif; ?>
							<?php else: ?>
								<?php echo $value; ?>
							<?php endif; ?>
                        </td>
                    </tr>
				<?php endforeach; ?>
                </tbody>
            </table>

			<?php if ($layouts = easyshop(System::class)->getExpiredLayoutsFiles()): ?>
                <h4 class="uk-margin-remove uk-text-uppercase">
                    <span uk-icon="icon: file-edit"></span> Expired layouts
                </h4>
                <ul uk-accordion>
					<?php foreach ($layouts as $template => $layout): ?>
                        <li class="uk-open">
                            <a class="uk-accordion-title" href="#">
                                Template: <?php echo $template ?>
                            </a>
                            <div class="uk-accordion-content">
								<?php foreach ($layout as $version => $file): ?>
                                    <div title="<?php echo htmlspecialchars($file); ?>">
										<?php echo str_replace(JPATH_ROOT . '/templates/' . $template . '/html/com_easyshop', '...', $file) . '?v=' . $version; ?>
                                    </div>
								<?php endforeach; ?>
                            </div>
                        </li>
					<?php endforeach; ?>
                </ul>
			<?php endif; ?>
			<?php echo implode(PHP_EOL, $systemAfterInformation); ?>

        </div>
        <div>
            <h4 class="uk-margin-remove uk-text-uppercase"><span uk-icon="icon: database"></span> Database</h4>
            <p>Use fix schemas feature that will be compared the installation sql file with your local database and may
                be it
                will run some change queries to fix the wrong schemas (if exists).<br/>
                Fix schema will do (maybe): <strong>Add missed columns</strong>, <strong>correct the type of
                    columns</strong>
                and <strong>ADD TABLE IF NOT EXISTS.</strong>
                <br/>Fix schema will not drop any columns, tables from your database, so your database always keeps
                safe.
            </p>
            <a href="<?php echo JRoute::_('index.php?option=com_easyshop&task=system.fixSchemas&' . JSession::getFormToken() . '=1', false); ?>"
               class="uk-button uk-button-primary uk-button-small" uk-icon="icon: database">
                Fix schemas
            </a>

            <h4 class="uk-text-uppercase"><span uk-icon="icon: thumbnails"></span>
                Regenerate images
            </h4>
            <p>This tool will re-generate all EasyShop media's thumbnails that were uploaded via EasyShop Media
                Manager.<br/>Note: Regenerate images will not handle resize original images.<br/><strong>Since version
                    1.1.6 If you have an image advance mode then this will remove all the thumbnails to expand storage
                    for your host</strong></p>
            <div class="uk-position-relative">
                <div id="es-progress-text" class="uk-position-center" hidden>0%</div>
                <progress id="es-progressbar" class="uk-progress" value="0" max="100" hidden></progress>
            </div>
            <a href="#" id="es-regenerate-images" class="uk-button uk-button-primary uk-button-small"
               uk-icon="icon: cog">
                Progress now
            </a>
        </div>
    </div>
</div>
