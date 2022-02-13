<?php
/**
 
 
 
 
 
 */

use Umart\Classes\Html;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;
$dropdownMode = $params->get('dropdown_mode');
$align        = $params->get('dropdown_display') ? ('pos:' . $params->get('dropdown_display') . ';') : '';
$flyToCart    = (int) $params->get('fly_to_cart', 0);
$duration     = (float) $params->get('fly_to_cart_duration', 1000) / 1000;

if ($flyToCart)
{
	umart(Html::class)->addJs('simple-transfer.min.js');
}

$moduleId = uniqid('es-cart-');
umart('doc')->addScriptDeclaration(<<<JAVASCRIPT
_es.$(document).on('esCartResponse', function (e, request, response) {
    var
        $ = _es.$,
        flyToCart = {$flyToCart};
    if (flyToCart && request.requestType === 'addToCart') {
        var
            image = $('[data-product-id="' + request.productId + '"]:eq(0) img:eq(0)'),
            el = $('#{$moduleId} .es-icon-cart');       

        if (image.length && el.is(':visible')) {
            response.data.html = '';
            new SimpleTransfer({
                from: image[0],
                to: el[0],               
                transition: 'all {$duration}s ease',
            }); 
        }
    }
});

JAVASCRIPT
);

?>
<div class="es-scope uk-scope">
    <div class="cart-boundary mod-umart-cart<?php echo $dropdownMode ? ' boundary-align' : ''; ?><?php echo $moduleClassSfx; ?>"
         id="<?php echo $moduleId; ?>" data-module-id="<?php echo $module->id; ?>">
        <div class="uk-text-meta">
			<?php if ($params->get('your_cart_label', 1)): ?>
				<?php echo Text::_('COM_UMART_YOUR_CART'); ?>
			<?php endif; ?>
            <span class="es-icon-cart" uk-icon="icon: cart"></span>
            <div data-cart-count class="uk-badge uk-badge-notification">
				<?php echo $data['count']; ?>
            </div>
        </div>
		<?php if (!empty($data['items'])): ?>
			<?php if ($dropdownMode): ?>
                <div uk-drop="<?php echo $align; ?>boundary: #<?php echo $moduleId; ?>; offset: 10; boundary-align: true; mode: <?php echo $dropdownMode; ?>">
                    <div class="uk-height-max-large uk-overflow-auto">
						<?php echo $cartInfo; ?>
                    </div>
                </div>
			<?php else: ?>
				<?php echo $cartInfo; ?>
			<?php endif; ?>
		<?php endif; ?>
    </div>
</div>