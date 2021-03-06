<?php

/**
 
 
 
 
 
 */

use Umart\Classes\User;
use Umart\Helper\Navbar;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

defined('_JEXEC') or die;
echo Navbar::render();

plg_sytem_umart_main('doc')->addScriptDeclaration('_umart.$(document).ready(function($){
    $("#esLanguageList a[data-lang]").on("click", function(e){
        e.preventDefault();
        $("#esLangTag").val($(this).data("lang"));
        $("#esPackage").trigger("click");
    });
    
    $("#esPackage").on("change", function(){
        if (this.files && this.files.length) {
            $("#adminForm").submit();
        }
    });
    
    $("#esLanguageList a.es-remove-lang").on("click", function(e){
        e.preventDefault();
        if (confirm("' . Text::_('COM_UMART_REMOVE_CONFIRM', true) . '")) {
            location.href = this.href;
        }        
    });
});');
$admin = plg_sytem_umart_main(User::class)->core('admin');
$token = '&' . Session::getFormToken() . '=1';
$i     = 0;
?>

<div id="umart_body" class="umartui_width-3-4@m umartui_width-4-5@xl umartui_width-2-3@s">
    <div class="uk-alert uk-alert-warning">
        <i class="fa fa-warning"></i>
		<?php echo Text::_('COM_UMART_LANGUAGE_OVERRIDE_NOTE'); ?>
    </div>
    <form action="<?php echo Route::_('index.php?option=com_umart&view=languages', false); ?>" method="post"
          name="adminForm" id="adminForm" enctype="multipart/form-data">
        <div id="uk-main-container">
            <div class="uk-overflow-auto">
                <table class="uk-table uk-table-hover uk-table-divider uk-table-small" id="esLanguageList">
                    <thead>
                    <tr>
                        <th class="uk-table-shrink uk-text-center">#</th>
                        <th><?php echo Text::_('COM_UMART_LANGUAGE'); ?></th>
                        <th class="uk-table-shrink uk-text-center"><?php echo Text::_('COM_UMART_ACTION'); ?></th>
                        <th class="uk-table-shrink uk-text-center"><?php echo Text::_('COM_UMART_ID'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
					<?php foreach ($this->languages as $language): ?>
                        <tr>
                            <td class="uk-text-center">
								<?php echo sprintf('%02d', ++$i); ?>
                            </td>
                            <td>
								<?php
								$text        = $language->title_native . ' <small>' . $language->title . '</small>';
								$hasLanguage = is_file(UMART_COMPONENT_ADMINISTRATOR . '/language/' . $language->lang_code . '/' . $language->lang_code . '.com_umart.ini');

								if (!empty($language->image))
								{
									$text = HTMLHelper::_('image', 'mod_languages/' . $language->image . '.gif', $language->title, null, true) . ' ' . $text;
								}

								if ($admin && $hasLanguage)
								{
									echo '<a href="' . Route::_('index.php?option=com_umart&view=language&tag=' . $language->lang_code) . '">' . $text . '</a>';
								}
								else
								{
									echo $text;
								}
								?>


                            </td>
                            <td class="uk-text-center">
                                <div class="uk-button-group">
                                    <a href="<?php echo Route::_('index.php?option=com_umart&task=language.download&tag=' . $language->lang_code . $token, false); ?>"
                                       class="uk-button uk-button-small uk-button-default uk-text-primary"
                                       data-lang="<?php echo $language->lang_code; ?>"
                                       title="<?php echo Text::_('COM_UMART_UPLOAD'); ?>"
                                       uk-tooltip>
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </a>
									<?php if ($hasLanguage): ?>
                                        <a href="<?php echo Route::_('index.php?option=com_umart&task=language.download&tag=' . $language->lang_code . $token, false); ?>"
                                           class="uk-button uk-button-small uk-button-default uk-text-success"
                                           title="<?php echo Text::_('COM_UMART_DOWNLOAD'); ?>"
                                           uk-tooltip>
                                            <i class="fas fa-cloud-download-alt"></i>
                                        </a>
									<?php else: ?>
                                        <a href="<?php echo Route::_('index.php?option=com_umart&task=language.cloneLanguage&tag=' . $language->lang_code . $token, false); ?>"
                                           class="uk-button uk-button-small uk-button-default uk-text-success"
                                           title="<?php echo Text::_('COM_UMART_COPY_FROM_ENGLISH'); ?>"
                                           uk-tooltip>
                                            <i class="fa fa-clone"></i>
                                        </a>
									<?php endif; ?>

									<?php if ($language->lang_code == 'en-GB' || !$hasLanguage): ?>
                                        <a href="javascript:"
                                           class="uk-button uk-button-small uk-button-default uk-text-danger disabled">
                                            <i class="fa fa-ban"></i>
                                        </a>
									<?php else: ?>
                                        <a href="<?php echo Route::_('index.php?option=com_umart&task=language.removeLanguage&tag=' . $language->lang_code . $token, false); ?>"
                                           class="uk-button uk-button-small uk-button-default uk-text-danger es-remove-lang"
                                           title="<?php echo Text::_('COM_UMART_REMOVE'); ?>"
                                           uk-tooltip>
                                            <i class="fa fa-times-circle"></i>
                                        </a>
									<?php endif; ?>
                                </div>
                            </td>
                            <td class="uk-text-center">
								<?php echo $language->lang_id; ?>
                            </td>
                        </tr>
					<?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <input type="file" id="esPackage" name="package" accept=".zip" style="display: none"/>
        <input type="hidden" id="esLangTag" name="tag" value=""/>
        <input type="hidden" name="task" value="language.upload"/>
		<?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
