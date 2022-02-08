<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use ES\Classes\Currency;
use Joomla\CMS\Language\Text;

/**
 * @var array $displayData
 */
extract($displayData);
$currencyClass = easyshop(Currency::class);
?>
<div class="es-best es-best-product uk-card uk-card-small uk-card-body uk-card-<?php echo $style; ?> uk-margin uk-overflow-auto">
    <h3 class="uk-heading-bullet">
		<?php echo Text::_('COM_EASYSHOP_BEST_PRODUCTS'); ?>
    </h3>
    <table class="uk-table uk-table-small uk-table-striped">
        <thead>
        <tr>
            <th class="uk-table-shrink">#</th>
            <th class="uk-width-medium@m">
				<?php echo Text::_('COM_EASYSHOP_NAME'); ?></th>
            <th class="uk-table-shrink uk-text-nowrap uk-text-center">
				<?php echo Text::_('COM_EASYSHOP_SALE_PRICE'); ?>
            </th>
            <th class="uk-table-shrink uk-text-nowrap uk-text-center">
				<?php echo Text::_('COM_EASYSHOP_NUM_NO'); ?>
            </th>
        </tr>
        </thead>
        <tbody>
		<?php foreach ($products as $i => $product):
			$currencyClass->load($product->currency_id);
			?>
            <tr>
                <td>
					<?php echo sprintf('%02d', $i + 1); ?>
                </td>
                <td>
					<?php echo $product->name; ?>
                </td>
                <td class="uk-text-nowrap uk-text-center">
					<?php echo $currencyClass->toFormat($product->price); ?>
                </td>
                <td class="uk-text-nowrap uk-text-center">
					<?php echo sprintf('%02d', $product->orderQuantity); ?>
                </td>
            </tr>
		<?php endforeach; ?>
        </tbody>
    </table>
</div>
