<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;
extract($displayData);

?>
<tr>
    <td>
        <input id="pName" class="uk-input" data-rule-required
               value="<?php echo htmlspecialchars($name, ENT_COMPAT, 'UTF-8'); ?>"/>
    </td>
    <td>
        <input type="number" min="0.00" id="pPrice" class="uk-input" data-rule-number
               value="<?php echo (float) $price; ?>"/>
        <!-- Options -->
        <div id="pOptions" class="uk-margin">
			<?php echo $options; ?>
        </div>
    </td>
    <td>
        <input type="number" min="0.00" id="pTaxes" class="uk-input" data-rule-number
               value="<?php echo (float) $taxes; ?>"/>
    </td>
    <td>
        <input type="number" id="pQuantity" class="uk-input uk-width-small" data-rule-number
               value="<?php echo $quantity; ?>" min="1"/>
    </td>
    <td>
        <input id="pSubtotal" class="uk-input uk-width-small" readonly
               value="<?php echo (float) $price * (int) $quantity; ?>"/>
    </td>
    <td>
        <ul class="uk-iconnav">
            <li>
                <a href="#" id="pSave" uk-icon="icon: check"
                   data-order-product-id="<?php echo (int) $orderProductId; ?>"></a>
            </li>
            <li>
                <a href="#" id="pCancel" uk-icon="icon: close"></a>
            </li>
        </ul>
        <input type="hidden" id="pID" value="<?php echo (int) $id; ?>"/>
    </td>
</tr>
