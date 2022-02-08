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
use ES\Classes\User;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Language\Text;

/**
 * @var array $displayData
 */

$view           = $displayData['view'];
$product        = $displayData['product'];
$priceConfig    = $displayData['config']->get('product_' . $view . '_prices', 'price_n_taxes');
$showPriceRules = $displayData['config']->get('product_' . $view . '_show_price_by_qty', 1);

// @since 1.2.3, check permission
$userClass = easyshop(User::class);
$groups    = $displayData['config']->get('groups_can_view_price', []);
$canView   = (empty($groups) || $userClass->accessGroups($groups, true)) && ($priceConfig == 'price_n_taxes' || $priceConfig == 'price');

?>

<?php if ($canView): ?>
    <div class="product-price" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
        <meta itemprop="price" content="<?php echo $product->price; ?>"/>
        <meta itemprop="priceCurrency" content="<?php echo $product->currency; ?>"/>

		<?php if (!empty($product->oldPriceFormat)): ?>
            <div class="product-price-old uk-display-inline-block uk-text-meta">
				<?php echo $product->oldPriceFormat; ?>
            </div>
		<?php endif; ?>

        <div class="product-sale-price uk-display-inline-block"
             data-product-price="<?php echo $product->price; ?>">
			<?php echo $product->priceFormat; ?>

			<?php if ($displayData['config']->get('price_include_taxes', 0)): ?>
                <small class="uk-text-meta">
					<?php echo '(' . Text::_('COM_EASYSHOP_INCL_TAXES') . ')'; ?>
                </small>
			<?php endif; ?>
        </div>

		<?php

		if ($showPriceRules && $product->prices)
		{
			$outputHtml  = '';
			$prices      = [];
			$currency    = easyshop(Currency::class)->getActive();
			$currencyId  = $currency->get('id');
			$nullDate    = easyshop('db')->getNullDate();
			$nowDateTime = CMSFactory::getDate('UTC')->format('Y-m-d H:i:s');

			foreach ($product->prices as $price)
			{
				$validFromDate = $price->valid_from_date;
				$validToDate   = $price->valid_to_date;

				if (!empty($validFromDate)
					&& !empty($validToDate)
					&& $validFromDate !== $nullDate
					&& $validToDate !== $nullDate
				)
				{
					try
					{
						$isValid = $nowDateTime >= $validFromDate && $nowDateTime <= $validToDate;

						if (!$isValid)
						{
							continue;
						}
					}
					catch (Exception $e)
					{

					}
				}

				if (!$price->currency_id || $currencyId == $price->currency_id)
				{
					$prices[] = $price;
				}
			}

			foreach ($prices as $i => $price)
			{
				$minQty      = (int) $price->min_quantity;
				$next        = $i + 1;
				$priceFormat = $currency->toFormat($price->price_value, true);
				$maxQty      = isset($prices[$next]) ? $prices[$next]->min_quantity - 1 : false;
				$class       = isset($product->cart['quantity']) && $product->cart['quantity'] >= $minQty && $product->cart['quantity'] <= $maxQty ? ' class="active"' : '';

				if (false !== $maxQty)
				{
					$outputHtml .= '<li' . $class . ' data-range-qty="[' . $price->min_quantity . ', ' . $maxQty . ']">'
						. '<span uk-icon="icon: check"></span> '
						. Text::sprintf('COM_EASYSHOP_PRICE_FOR_RANGE_QUANTITY_FORMAT', $price->min_quantity, $maxQty, $priceFormat)
						. '</li>';
				}
				else
				{
					$outputHtml .= '<li' . $class . ' data-range-qty="[' . $price->min_quantity . ', 999999]">'
						. '<span uk-icon="icon: check"></span> '
						. Text::sprintf('COM_EASYSHOP_PRICE_FOR_MIN_QUANTITY_FORMAT', $price->min_quantity, $priceFormat)
						. '</li>';
				}
			}

			if ($outputHtml)
			{
				echo '<ul class="uk-list uk-margin-small es-price-range-qty">' . $outputHtml . '</ul>';
			}
		}

		?>

		<?php if ($priceConfig == 'price_n_taxes' && $product->totalTaxes > 0.00): ?>
            <div class="product-taxes">
                <div class="uk-flex">
                    <div class="uk-text-meta">
			            <?php echo Text::_('COM_EASYSHOP_TAXES'); ?>
                    </div>
                    <div class="uk-margin-small-left" data-product-taxes="<?php echo $product->totalTaxes; ?>">
			            <?php echo $product->taxesFormat; ?>
                    </div>
                </div>
            </div>
		<?php endif; ?>
    </div>
<?php endif; ?>