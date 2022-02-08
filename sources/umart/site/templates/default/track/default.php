<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;
?>
<?php if (easyshop('config', 'enable_track_order', 1)): ?>
    <div class="uk-flex uk-flex-center">
        <div class="uk-tile uk-tile-muted">
			<?php echo easyshop('renderer')->render('customer.tracking', [
				'return' => base64_encode(Uri::getInstance()->toString()),
			]); ?>
        </div>
    </div>
<?php else: ?>
    <div class="uk-alert uk-alert-warning">
		<?php echo Text::_('COM_EASYSHOP_TRACK_ORDER_WARNING_MESSAGE'); ?>
    </div>
<?php endif; ?>
