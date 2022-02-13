<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
/** @var array $displayData */
extract($displayData);

?>
<div data-cart-modal tabindex="-1" uk-modal="center: true">
    <div class="uk-modal-dialog umartui_width-2xlarge umartui_width-xxlarge uk-modal-body es-cart-modal-detail">
        <a class="uk-modal-close-default" data-uk-close></a>
        <div data-cart-output>
			<?php echo $displayData['cartOutputHTML']; ?>
        </div>
    </div>
</div>
