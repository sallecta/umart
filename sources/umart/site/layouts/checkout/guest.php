<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

/**
 * @var array $displayData
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$return     = base64_encode(Uri::getInstance()->toString());
$emailField = $displayData['form']->getField('email', 'guest');
?>
<form action="<?php echo Route::_('index.php', false); ?>" data-validate novalidate method="post">
    <h4 class="uk-h5 uk-heading-bullet">
        <span uk-icon="icon: user"></span>
		<?php echo Text::_('COM_EASYSHOP_GUEST_CHECKOUT'); ?>
    </h4>
    <p>
		<?php echo Text::_('COM_EASYSHOP_GUEST_CHECKOUT_TIP'); ?>
    </p>
    <div class="uk-flex">
        <div class="uk-width-expand">
			<?php echo $emailField->input; ?>
        </div>
        <div class="uk-width-auto">
            <button type="submit" class="uk-button uk-button-primary">
				<?php echo Text::_('COM_EASYSHOP_CONTINUE'); ?>
                <span uk-icon="icon: mail"></span>
            </button>
        </div>
    </div>
    <input type="hidden" name="option" value="com_easyshop"/>
    <input type="hidden" name="task" value="checkout.guest"/>
    <input type="hidden" name="return" value="<?php echo $return; ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
