<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use ES\Classes\Renderer;

defined('_JEXEC') or die;
/** @var $renderer Renderer */
$renderer           = $this->getRenderer();
$enableTrackForm    = $this->config->get('enable_track_order', 1);
$enableRegistration = $this->config->get('enable_registration', 1);

?>
<div id="es-customer-login" class="uk-margin">
	<?php if ($this->order): ?>
		<?php echo $this->loadTemplate('order'); ?>
	<?php else: ?>
        <div uk-alert>
            <h4 class="uk-margin-remove-bottom uk-h5">
				<?php echo JText::_('COM_EASYSHOP_NOTICE'); ?>
            </h4>
			<?php echo JText::_('COM_EASYSHOP_CUSTOMER_GUEST_WARNING'); ?>
            <a class="uk-alert-close" uk-close></a>
        </div>
        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-1-2@s">
                <div class="uk-tile uk-tile-small uk-tile-muted uk-margin">
					<?php echo $renderer->render('customer.login', [
						'form' => $this->form,
					]); ?>
                </div>

				<?php if ($enableTrackForm && $enableRegistration): ?>
                    <div class="uk-tile uk-tile-small uk-tile-muted">
						<?php echo $this->loadTemplate('form'); ?>
                    </div>
				<?php endif; ?>
            </div>
			<?php if ($enableRegistration): ?>
                <div class="uk-width-1-2@s">
					<?php echo $renderer->render('customer.registration', [
						'form' => $this->form,
					]); ?>
                </div>
			<?php elseif ($enableTrackForm): ?>
                <div class="uk-width-1-2@s">
					<?php echo $this->loadTemplate('form'); ?>
                </div>
			<?php endif; ?>
        </div>
	<?php endif; ?>
</div>
