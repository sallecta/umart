<?php
/**
 
 
 
 
 
 */

defined('_JEXEC') or die;
extract($displayData);
?>
<div class="es-best es-best-customer uk-card uk-card-small uk-card-body uk-card-<?php echo $style; ?> uk-overflow-auto">
    <h3 class="uk-heading-bullet">
		<?php echo JText::_('COM_UMART_BEST_CUSTOMERS'); ?>
    </h3>
    <table class="uk-table uk-table-small uk-table-striped">
        <thead>
        <tr>
            <th class="uk-table-shrink">#</th>
            <th class="umartui_width-medium@m">
                <?php echo JText::_('COM_UMART_NAME'); ?>
            </th>
            <th class="uk-table-shrink uk-text-nowrap uk-text-center">
                <?php echo JText::_('COM_UMART_NUM_NO'); ?>
            </th>
        </tr>
        </thead>
        <tbody>
		<?php foreach ($customers as $i => $customer): ?>
            <tr>
                <td>
                    <?php echo sprintf('%02d', $i + 1); ?>
                </td>
                <td>
                    <?php echo $customer->name; ?>
                </td>
                <td class="uk-text-center">
                    <?php echo sprintf('%02d', $customer->orderQuantity); ?>
                </td>
            </tr>
		<?php endforeach; ?>
        </tbody>
    </table>
</div>
