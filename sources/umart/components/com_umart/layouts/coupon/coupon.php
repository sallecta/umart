<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$formSmall = !empty($displayData['formSmall']);

?>

<div class="uk-flex es-coupon-container">
    <div class="umartui_width-expand">
        <input type="text" name="coupon" data-coupon
               class="uk-input<?php echo $formSmall ? ' uk-form-small' : ''; ?>"
               onkeypress="if(event.keyCode === 13) _umart.cart.coupon(_umart.$(this).val());"
               placeholder="<?php echo Text::_('COM_UMART_ENTER_YOUR_COUPON'); ?>"/>
    </div>
    <div class="umartui_width-auto">
        <a class="uk-button uk-button-primary<?php echo $formSmall ? ' uk-button-small' : ''; ?>"
           href="javascript:void(0);"
           onclick="_umart.cart.coupon(_umart.$(this).parents('.es-coupon-container').find('[data-coupon]').val());">
			<?php echo Text::_('COM_UMART_USE'); ?>
            <span uk-icon="icon: code"></span>
        </a>
    </div>
</div>