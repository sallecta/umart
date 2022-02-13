<?php
/**
 
 
 
 
 
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;
?>
<?php if (plg_sytem_umart_main('config', 'enable_track_order', 1)): ?>
    <div class="uk-flex uk-flex-center">
        <div class="uk-tile uk-tile-muted">
			<?php echo plg_sytem_umart_main('renderer')->render('customer.tracking', [
				'return' => base64_encode(Uri::getInstance()->toString()),
			]); ?>
        </div>
    </div>
<?php else: ?>
    <div class="uk-alert uk-alert-warning">
		<?php echo Text::_('COM_UMART_TRACK_ORDER_WARNING_MESSAGE'); ?>
    </div>
<?php endif; ?>
