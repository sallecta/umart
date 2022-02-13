<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$confirmMessage = Text::_('COM_UMART_UPDATE_TO_VERSION_CONFIRM', true);
$config         = plg_sytem_umart_main('config');
$seconds        = (int) $config->get('notification_seconds', 30);
$fetchUrl       = Route::_('index.php?option=com_umart&task=notification.fetch', false);
$postUpdateUrl  = Route::_('index.php?option=com_umart&task=system.postUpdate', false);
$token          = Session::getFormToken();
$js             = <<<JAVASCRIPT
_umart.$(document).ready(function ($) {
    var
        container = $('#es-notification')        
        , seconds = {$seconds}        
        , k
        , updateForm = $('#es-post-update-form')
        , updateLink = container.find('.es-notification-update a')
        , fetch = function () {
            _umart.ajax('{$fetchUrl}', {}, function (response) {
                 for (k in response.data) {
                    if (k === 'updateInfo') {
                        if (response.data[k].version) { 
                            updateLink.parent().removeClass('uk-hidden').find('sup').text(response.data[k].version);
                            updateLink.attr('uk-tooltip', response.data[k].message);
                            updateForm.find('input[name="downloadUrl"]').val(response.data[k].downloadUrl);
                            updateForm.data('confirmMessage', '{$confirmMessage}' + response.data[k].version + '?');
                        }
                    } else {
                        container.find('li[data-target="' + k + '"]').data('html', response.data[k].html)
                            .find('sup').text(response.data[k].count);
                    }                    
                 }
            }, true);
        };

    container.find('li[data-target]').on('click', function (e) {
        e.preventDefault(); 
        _umart.umartui.modal.dialog($(this).data('html'));        
    });
    
    updateLink.on('click', function (e) {
        e.preventDefault();        
        if (updateForm.data('confirmMessage')) {
            _umart.umartui.modal.confirm(updateForm.data('confirmMessage')).then(function(){
                updateForm.submit();
            }, function(){});
        }        
    });	

    fetch();
        
    if (seconds > 0) {
        window.setInterval(fetch, seconds*1000);
    }
});
JAVASCRIPT;
plg_sytem_umart_main('doc')->addScriptDeclaration($js);

if (plg_sytem_umart_main('app')->input->get('task') === 'loadNavigation')
{
	echo '<script>' . $js . '</script>';
}

?>

<div id="es-notification">
    <ul class="uk-iconnav uk-margin-remove-left uk-float-right">
        <li class="es-notification-order" data-target="order">
            <a href="#" class="uk-link-reset">
				<?php echo HTMLHelper::_('umart.icon', 'es-icon-chart-bars'); ?>
                <sup>0</sup>
            </a>
        </li>
        <li class="es-notification-log" data-target="log">
            <a href="#" class="uk-link-reset">
	            <?php echo HTMLHelper::_('umart.icon', 'es-icon-history'); ?>
                <sup>0</sup>
            </a>
        </li>
        <li class="es-notification-update uk-hidden">
            <a href="#" class="uk-link-reset">
                <strong style="color: #009688;">
	                <?php echo HTMLHelper::_('umart.icon', 'es-icon-cloud-upload'); ?>
                    <sup></sup>
                </strong>
            </a>
        </li>
    </ul>
</div>
<form action="<?php echo $postUpdateUrl; ?>" method="post" id="es-post-update-form">
    <input type="hidden" name="downloadUrl" value=""/>
    <input type="hidden" name="return" value="<?php echo base64_encode(Uri::getInstance()->toString()); ?>"/>
    <input type="hidden" name="<?php echo $token; ?>" value="1"/>
</form>
