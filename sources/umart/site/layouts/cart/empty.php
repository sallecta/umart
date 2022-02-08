<?php
/**
 * @package     com_easyshop
 * @version     1.0.5
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2019 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;
?>
<div data-cart-body>
    <div uk-alert>
        <p>
            <span uk-icon="icon: warning"></span>
			<?php echo Text::_('COM_EASYSHOP_YOUR_CART_EMPTY'); ?>
        </p>
    </div>
</div>
