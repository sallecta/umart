<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use ES\Classes\User;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * @var array    $displayData
 * @var stdClass $product
 * @var Registry $config
 * @var string   $view
 * @var User     $userClass
 */

extract($displayData);

$direct        = $product->params->get('product_detail_direct', 0);
$text          = $direct ? Text::_('COM_EASYSHOP_BUY_NOW') : Text::_('COM_EASYSHOP_ADD_TO_CART');
$disableOnZero = $config->get('disable_btn_on_zero', 0);
$currentPrice  = isset($product->cart['price']) ? $product->cart['price'] : $product->price;
$disabled      = $disableOnZero && $currentPrice < 0.01;

// @since 1.2.3, check permission
$userClass = easyshop(User::class);
$groups    = $config->get('groups_can_add_to_cart', []);

?>

<?php if (empty($groups) || $userClass->accessGroups($groups, true)): ?>
    <div class="es-addToCart">
        <a href="javascript:"
           class="btn-add-to-cart uk-button-primary uk-icon-button<?php echo $disabled ? ' uk-disabled' : ''; ?>"
           uk-icon="icon: cart"
           title="<?php echo htmlspecialchars($text, ENT_COMPAT, 'UTF-8'); ?>"
           data-disable-on-zero="<?php echo $disableOnZero; ?>"
			<?php echo $disabled ? ' disabled="disabled"' : ''; ?>
           uk-tooltip data-add-to-cart></a>
    </div>
<?php endif; ?>
