<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;
$renderer = $this->getRenderer();
$shopInfo = $this->config->get('shop_cart_info', '1');
$i        = 0;
?>
<div id="es-cart-page">
    <div data-cart-output>
		<?php foreach ($this->cartData as $vendorId => $extractData): ?>
			<?php if ($extractData['count']): ?>
				<?php echo $renderer->render('cart.cart', [
					'extractData' => $extractData,
					'vendorId'    => $vendorId,
					'shopInfo'    => $shopInfo ? $this->utility->getShopInformation($vendorId) : '',
				]); ?>
			<?php endif; ?>
		<?php endforeach; ?>
    </div>
</div>
