<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

?>
<div id="mod-umart-tracking<?php echo $module->id; ?>"
     class="es-scope uk-scope mod-umart-tracking<?php echo $params->get('moduleclass_sfx'); ?>">
	<?php echo umart('renderer')->render('customer.tracking', [
		'return' => base64_encode(JRoute::_(UmartHelperRoute::getCustomerRoute(), false)),
	]); ?>
</div>
