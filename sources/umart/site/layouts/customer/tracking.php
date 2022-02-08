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
<form action="<?php echo Route::_('index.php?option=com_easyshop&task=customer.trackOrder', false); ?>"
      method="post" data-validate>
    <h4 class="uk-h5 uk-heading-bullet">
		<?php echo Text::_('COM_EASYSHOP_TRACK_YOUR_ORDER'); ?>
    </h4>
    <div class="uk-margin-small">
        <label for="es-guest-email">
			<?php echo Text::_('COM_EASYSHOP_YOUR_EMAIL') . '*'; ?>
        </label>
        <div class="uk-form-controls">
            <input type="text" name="email" id="es-guest-email" class="uk-input" data-rule-required
                   data-rule-email/>
        </div>
    </div>
    <div class="uk-margin-small">
        <label for="es-guest-orderCode">
			<?php echo Text::_('COM_EASYSHOP_YOUR_ORDER_CODE') . '*'; ?>
        </label>
        <div class="uk-form-controls">
            <input type="text" name="orderCode" id="es-guest-orderCode" class="uk-input"
                   data-rule-required/>
        </div>
    </div>
    <button type="submit" class="uk-button uk-button-default">
        <span uk-icon="icon: search"></span>
		<?php echo Text::_('COM_EASYSHOP_FIND_ORDER'); ?>
    </button>
	<?php if (!empty($displayData['return'])): ?>
        <input name="return" type="hidden" value="<?php echo $displayData['return']; ?>"/>
	<?php endif; ?>
</form>
