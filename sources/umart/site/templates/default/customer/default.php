<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;
?>
<div id="es-customer" class="uk-margin">
    <!-- The same $this->loadTemplate('navbar') -->
	<?php echo $this->state->get('customer.navbar'); ?>

    <!-- The same $this->loadTemplate('subLayout') OR a buffer HTML string was rendered by some plugin -->
	<?php echo $this->state->get('customer.page'); ?>
</div>
