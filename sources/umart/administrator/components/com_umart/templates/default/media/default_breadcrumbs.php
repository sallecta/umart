<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\User;
use Joomla\CMS\Language\Text;

$userClass = plg_sytem_umart_main(User::class);
$type      = $this->isFile ? 'file' : 'image';

?>
<?php if ($userClass->core('create')): ?>
    <div class="uk-margin">
        <ol class="uk-breadcrumb">
            <li>
                <a href="<?php echo UmartHelperMedia::getLink('', $type); ?>">
                    <span uk-icon="icon: home"></span>
                </a>
            </li>
			<?php foreach ($this->breadcrumbs as $breadcrumb): ?>
                <li>
                    <a href="<?php echo $breadcrumb['link']; ?>">
                        <em><?php echo $breadcrumb['title']; ?></em>
                    </a>
                </li>
			<?php endforeach; ?>
            <li>
                <a href="#" class="uk-text-primary" uk-toggle="target: #es-folder">
                    <i class="fa fa-plus-circle"></i>
					<?php echo Text::_('COM_UMART_BTN_CREATE_FOLDER'); ?>
                </a>
            </li>
            <li>
                <div id="es-folder" class="uk-flex" hidden>
                    <input type="text"
                           id="es-input-data"
                           class="uk-input"
                           data-rule-required="true"
                           data-container="body"
                           data-placement="bottom"
                           data-rule-regex="^[A-Za-z0-9_\/-]+[A-Za-z0-9_\.-]*([\\\\\/][A-Za-z0-9_-]+[A-Za-z0-9_\.-]*)*$"
                           onkeydown="if (event.keyCode == 13) _umart.$(this).next('.create').trigger('click');"/>
                    <a href="#" class="uk-button uk-button-default uk-button-small create">
                        <span uk-icon="icon: check"></span>
                    </a>
                </div>
                <span data-message class="uk-hidden"></span>
            </li>
        </ol>
    </div>
<?php endif; ?>
<?php if (plg_sytem_umart_main('app')->input->getString('method') == 'importMedia'): ?>
    <div class="uk-button-group">
        <button type="button" class="uk-button uk-button-small uk-button-primary file-selected-insert">
            <i class="uk-icon-check-circle"></i>
			<?php echo Text::_('COM_UMART_INSERT'); ?>
        </button>
        <button type="button" class="uk-button uk-button-default uk-button-small file-selected-reset">
            <i class="uk-icon-eraser"></i>
			<?php echo Text::_('COM_UMART_RESET'); ?>
        </button>
    </div>
<?php endif; ?>
