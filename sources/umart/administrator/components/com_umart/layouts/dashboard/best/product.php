<?php
/**
 
 
 
 
 
 */

defined('_JEXEC') or die;

use Umart\Classes\Currency;
use Joomla\CMS\Language\Text;

/**
 * @var array $displayData
 */
extract($displayData);
$currencyClass = plg_sytem_umart_main(Currency::class);
?>
<div class="es-best es-best-product uk-card uk-card-small uk-card-body uk-card-<?php echo $style; ?> uk-margin uk-overflow-auto">
    <h3 class="uk-heading-bullet">
		<?php echo Text::_('COM_UMART_BEST_PRODUCTS'); ?>
    </h3>
    <table class="uk-table uk-table-small uk-table-striped">
        <thead>
        <tr>
            <th class="uk-table-shrink">#</th>
            <th class="umartui_width-medium@m">
				<?php echo Text::_('COM_UMART_NAME'); ?></th>
            <th class="uk-table-shrink uk-text-nowrap uk-text-center">
				<?php echo Text::_('COM_UMART_SALE_PRICE'); ?>
            </th>
            <th class="uk-table-shrink uk-text-nowrap uk-text-center">
				<?php echo Text::_('COM_UMART_NUM_NO'); ?>
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
