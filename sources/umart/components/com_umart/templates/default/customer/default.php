<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
?>
<div id="es-customer" class="uk-margin">
    <!-- The same $this->loadTemplate('navbar') -->
	<?php echo $this->state->get('customer.navbar'); ?>

    <!-- The same $this->loadTemplate('subLayout') OR a buffer HTML string was rendered by some plugin -->
	<?php echo $this->state->get('customer.page'); ?>
</div>
