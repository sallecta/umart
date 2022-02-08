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
use Joomla\CMS\Router\Route;

$imageIframeAttributes = [
	'src'   => Route::_('index.php?option=com_easyshop&view=media&tmpl=component&method=importMedia&multiple=true', false),
	'class' => 'uk-height-large',
	'width' => '100%',
];
$fileIframeAttributes  = [
	'src'   => Route::_('index.php?option=com_easyshop&view=media&media_type=file&tmpl=component&method=importMedia&multiple=true', false),
	'class' => 'uk-height-large',
	'width' => '100%',
];
?>
<div id="es-media-modal-image" class="uk-modal-container" uk-modal>
    <div class="uk-modal-dialog">
        <a class="uk-modal-close uk-close-large uk-modal-close-default" uk-close></a>
        <div class="uk-modal-header">
            <h4 class="uk-modal-title">
				<?php echo Text::_('COM_EASYSHOP_MEDIA_MANAGER'); ?>
            </h4>
        </div>
        <div class="uk-modal-body">
            <iframe id="es-iframe-image"
                    data-attributes="<?php echo htmlspecialchars(json_encode($imageIframeAttributes), ENT_COMPAT, 'UTF-8'); ?>"></iframe>
        </div>
    </div>
</div>

<?php if ($this->fileEnabled): ?>
    <div id="es-media-modal-file" class="uk-modal-container" uk-modal>
        <div class="uk-modal-dialog">
            <div class="uk-modal-body">
                <a class="uk-modal-close uk-close-large uk-modal-close-default" uk-close></a>
                <div class="uk-modal-header">
                    <h4 class="uk-modal-title">
						<?php echo Text::_('COM_EASYSHOP_MEDIA_MANAGER'); ?>
                    </h4>
                </div>
                <iframe id="es-iframe-file"
                        data-attributes="<?php echo htmlspecialchars(json_encode($fileIframeAttributes), ENT_COMPAT, 'UTF-8'); ?>"></iframe>
            </div>
        </div>
    </div>
<?php endif; ?>
