<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * @var array $displayData
 */

extract($displayData);
$productStock    = (int) $product->stock;
$lowStockWarning = (int) $config->get('notification_low_stock', 5);

if ($productStock !== -1)
{
	if ($productStock === 0)
	{
		echo '<span class="uk-text-danger"><link itemprop="availability" href="http://schema.org/OutOfStock"/>' . Text::_('COM_UMART_OUT_OF_STOCK') . '</span>';
	}
	elseif ($productStock > $lowStockWarning)
	{
		echo '<span class="uk-text-primary"><link itemprop="availability" href="http://schema.org/InStock"/>' . Text::_('COM_UMART_IN_STOCK') . '</span>';
	}
	else
	{
		echo '<span class="uk-text-warning">' . Text::sprintf('COM_UMART_LOW_STOCK_WARNING_FORMAT', $productStock) . '</span>';
	}
}
