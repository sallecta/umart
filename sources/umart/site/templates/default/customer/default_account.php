<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$renderer = $this->getRenderer();
?>
<div id="es-customer-account">
    <form action="<?php echo Route::_('index.php?option=com_easyshop&task=customer.save', false); ?>" method="post"
          data-validate
          novalidate>
        <div id="es-toolbar" class="uk-clearfix uk-padding-small uk-background-muted uk-margin">
            <button type="submit" class="uk-button uk-button-primary uk-button-small uk-float-right">
                <i class="fa fa-save"></i>
				<?php echo Text::_('COM_EASYSHOP_SAVE'); ?>
            </button>
        </div>
        <div class="uk-tile uk-tile-muted">
            <div class="uk-grid-small uk-child-width-1-2@s" uk-grid>
                <div>
                    <h4 class="uk-h5 uk-heading-bullet">
                        <span uk-icon="icon: lock"></span>
						<?php echo Text::_('COM_EASYSHOP_ACCOUNT'); ?>
                    </h4>
                    <div class="es-customer-profile">
						<?php echo $renderer->render('form.fields', [
							'fields' => $this->form->getFieldset('profile'),
						]); ?>
                    </div>
                </div>
                <div>
                    <h4 class="uk-h5 uk-heading-bullet">
                        <span uk-icon="icon: location"></span>
						<?php echo Text::_('COM_EASYSHOP_ADDRESS'); ?>
                    </h4>
                    <div class="es-custom-fields" data-zone-group>
						<?php echo $renderer->render('form.fields', [
							'fields' => $this->form->getGroup('customfields'),
						]); ?>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="return" value="<?php echo base64_encode(Uri::getInstance()->toString()); ?>"/>
		<?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
