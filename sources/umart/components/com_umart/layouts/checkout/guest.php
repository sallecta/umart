<?php
/**
 
 
 
 
 
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
		<?php echo Text::_('COM_UMART_GUEST_CHECKOUT'); ?>
    </h4>
    <p>
		<?php echo Text::_('COM_UMART_GUEST_CHECKOUT_TIP'); ?>
    </p>
    <div class="uk-flex">
        <div class="umartui_width-expand">
			<?php echo $emailField->input; ?>
        </div>
        <div class="umartui_width-auto">
            <button type="submit" class="uk-button uk-button-primary">
				<?php echo Text::_('COM_UMART_CONTINUE'); ?>
                <span uk-icon="icon: mail"></span>
            </button>
        </div>
    </div>
    <input type="hidden" name="option" value="com_umart"/>
    <input type="hidden" name="task" value="checkout.guest"/>
    <input type="hidden" name="return" value="<?php echo $return; ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
