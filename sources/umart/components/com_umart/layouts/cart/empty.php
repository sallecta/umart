<?php
/**
 
 * @version     1.0.5
 
 * @copyright   Copyright (C) 2015 - 2019 github.com/sallecta/umart All Rights Reserved.
 
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;
?>
<div data-cart-body>
    <div uk-alert>
        <p>
            <span uk-icon="icon: warning"></span>
			<?php echo Text::_('COM_UMART_YOUR_CART_EMPTY'); ?>
        </p>
    </div>
</div>
