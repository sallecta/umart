<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

if (empty($displayData['return']))
{
	$return = plg_sytem_umart_main('app')->input->getBase64('return', base64_encode(Uri::getInstance()->toString()));
}
else
{
	$return = $displayData['return'];
}

?>
<form action="<?php echo Route::_('index.php?option=com_umart&task=customer.register', false); ?>" method="post"
      id="es-customer-registration-form" class="uk-form-stacked uk-margin" data-validate
      data-zone-group novalidate>
    <div class="uk-grid-small uk-child-width-1-<?php echo empty($displayData['isCheckoutPage']) ? '1' : '2'; ?>@s"
         uk-grid>
        <div>
            <h4 class="uk-h5 uk-heading-bullet">
				<?php echo Text::_('COM_UMART_ACCOUNT'); ?>
            </h4>
			<?php echo $displayData['renderer']->render('form.fields', [
				'fields' => $displayData['form']->getGroup('registration'),
			]); ?>
        </div>
        <div>
            <div id="es-customer-fields">
                <h4 class="uk-h5 uk-heading-bullet">
					<?php echo Text::_('COM_UMART_ADDRESS'); ?>
                </h4>
				<?php

				echo $displayData['renderer']->render('form.fields', [
					'fields' => $displayData['form']->getGroup('customfields'),
				]);
				?>
            </div>

			<?php if ($extraGroups = $displayData['form']->getGroup('extras')): ?>
				<?php echo $displayData['renderer']->render('form.fields', [
					'fields' => $extraGroups,
				]); ?>
			<?php endif; ?>
        </div>
    </div>
    <button type="submit" class="uk-button uk-button-primary uk-float-right">
        <span uk-icon="icon: plus"></span>
		<?php echo Text::_('COM_UMART_REGISTER'); ?>
    </button>
    <div class="uk-clearfix"></div>
    <input type="hidden" name="return" value="<?php echo $return; ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
