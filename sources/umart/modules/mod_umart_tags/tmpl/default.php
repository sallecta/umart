<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

?>

<div id="mod-umart-tags<?php echo $module->id; ?>"
     class="<?php echo $params->get('moduleclass_sfx'); ?>mod-umart-tags">
	<?php echo umart('renderer')->render('product.tags', ['tags' => $tags]); ?>
</div>
