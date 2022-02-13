<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>

<div class="uk-button-group umartui_width-1-1 uk-margin-small">
    <a href="<?php echo Route::_(UmartHelperRoute::getCartRoute('default'), false); ?>"
       class="uk-button uk-button-small uk-button-default umartui_width-1-2">
        <span uk-icon="icon: reply"></span>
		<?php echo Text::_('COM_UMART_BACK'); ?>
    </a>
    <button type="button" class="uk-button uk-button-small uk-button-primary umartui_width-1-2"
            onclick="_umart.checkout.saveAddress();">
		<?php echo Text::_('COM_UMART_NEXT'); ?>
        <span uk-icon="icon: check"></span>
    </button>
</div>