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

?>

<div class="uk-button-group uk-width-1-1 uk-margin-small">
    <a href="<?php echo Route::_(EasyshopHelperRoute::getCartRoute('default'), false); ?>"
       class="uk-button uk-button-small uk-button-default uk-width-1-2">
        <span uk-icon="icon: reply"></span>
		<?php echo Text::_('COM_EASYSHOP_BACK'); ?>
    </a>
    <button type="button" class="uk-button uk-button-small uk-button-primary uk-width-1-2"
            onclick="_es.checkout.saveAddress();">
		<?php echo Text::_('COM_EASYSHOP_NEXT'); ?>
        <span uk-icon="icon: check"></span>
    </button>
</div>