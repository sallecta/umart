<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * @var array $displayData
 */

$return = plg_sytem_umart_main('app')->input->getBase64('return', base64_encode(Uri::getInstance()->toString()));

?>
<form action="<?php echo Route::_('index.php?option=com_umart&task=customer.login', false); ?>" method="post"
      id="es-customer-login-form" class="uk-form-stacked" autocomplete="off" data-validate
      novalidate>
    <h4 class="uk-h5 uk-heading-bullet">
		<?php echo Text::_('COM_UMART_LOGIN'); ?>
    </h4>
	<?php echo $displayData['renderer']->render('form.fields', ['fields' => $displayData['form']->getGroup('login')]); ?>
	<?php if (PluginHelper::isEnabled('system', 'remember')) : ?>
        <div class="uk-margin-small">
            <label>
				<?php echo Text::_('COM_UMART_LOGIN_REMEMBER_ME'); ?>
                <input id="es-user-remember" type="checkbox" name="remember" class="uk-checkbox" value="yes"/>
            </label>
        </div>
	<?php endif; ?>
    <ul class="uk-list uk-list-line uk-margin-remove">
        <li>
            <a class="uk-link-muted" href="<?php echo Route::_('index.php?option=com_users&view=reset', false); ?>">
                <span uk-icon="icon: chevron-right"></span>
				<?php echo Text::_('COM_UMART_LOGIN_RESET'); ?>
            </a>
        </li>
        <li>
            <a class="uk-link-muted" href="<?php echo Route::_('index.php?option=com_users&view=remind', false); ?>">
                <span uk-icon="icon: chevron-right"></span>
				<?php echo Text::_('COM_UMART_LOGIN_REMIND'); ?>
            </a>
        </li>
    </ul>

    <button type="submit" class="uk-button uk-button-primary umartui_width-1-1 uk-margin">
        <span uk-icon="icon: sign-in"></span>
		<?php echo Text::_('COM_UMART_LOGIN'); ?>
    </button>

	<?php echo implode(PHP_EOL, plg_sytem_umart_main('app')->triggerEvent('onUmartAfterLoginButton')); ?>

    <input type="hidden" name="return" value="<?php echo $return; ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
