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

$formSmall = !empty($displayData['formSmall']);

?>

<div class="uk-flex es-coupon-container">
    <div class="uk-width-expand">
        <input type="text" name="coupon" data-coupon
               class="uk-input<?php echo $formSmall ? ' uk-form-small' : ''; ?>"
               onkeypress="if(event.keyCode === 13) _es.cart.coupon(_es.$(this).val());"
               placeholder="<?php echo Text::_('COM_EASYSHOP_ENTER_YOUR_COUPON'); ?>"/>
    </div>
    <div class="uk-width-auto">
        <a class="uk-button uk-button-primary<?php echo $formSmall ? ' uk-button-small' : ''; ?>"
           href="javascript:void(0);"
           onclick="_es.cart.coupon(_es.$(this).parents('.es-coupon-container').find('[data-coupon]').val());">
			<?php echo Text::_('COM_EASYSHOP_USE'); ?>
            <span uk-icon="icon: code"></span>
        </a>
    </div>
</div>