<?php

/**
 
 
 
 
 
 */

use Umart\Classes\Html;
use Umart\Classes\Utility;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;
$html     = umart(Html::class);
$renderer = umart('renderer');
$carousel = (int) $params->get('use_carousel', 0);

if ($carousel > 0)
{
	$layoutId = 'product.slider';
}
else
{
	$layoutId = 'product.products';
}

$slideNav      = $params->get('carousel_nav', '1');
$slideDots     = $params->get('carousel_dots', '1');
$slideAutoPlay = $params->get('carousel_autoplay', '1');
$groupsInTabs  = $params->get('group_in_tabs', '1');
$columnInRow   = (int) $params->get('product_list_columns', 3);
$slideRows     = (int) $params->get('slider_rows', 1);

// @since 1.1.6
$gridFilters   = $params->get('grid_filters', '0');
$filterNav     = '';
$filterContent = '';
$utility       = umart(Utility::class);
$imageSize     = $config->get('product_list_image_size', 'medium');
$columnWidth   = $utility->parseColumnClassSizes($config);

if (!in_array($imageSize, ['tiny', 'small', 'medium', 'large', 'xlarge']))
{
	$imageSize = 'medium';
}

?>
<div id="mod-umart-products-<?php echo $module->id; ?>"
     class="uk-scope es-scope mod-umart-products <?php echo $moduleClassSfx; ?> product-list <?php echo $params->get('list_mode', 'grid'); ?>-view">
	<?php if ($params->get('product_mode') == 'products'): ?>
		<?php echo $renderer->render($layoutId, [
			'config'        => $config,
			'products'      => $items,
			'slideNav'      => $slideNav,
			'slideDots'     => $slideDots,
			'slideAutoPlay' => $slideAutoPlay,
			'columnInRow'   => $columnInRow,
			'slideRows'     => $slideRows,
		]); ?>
	<?php else: ?>
		<?php foreach ($items as $categoryId => $products): ?>
			<?php if ($gridFilters): ?>
				<?php
				$filterNav .= '<li uk-filter-control="[data-category-id=\'' . $categoryId . '\']"><a href="#">' . $categories[$categoryId]->title . '</a></li>';

				foreach ($products as $product)
				{
					if (!empty($product->images[0]->{$imageSize}))
					{
						$product->image      = $product->images[0];
						$product->image->src = $product->image->{$imageSize};
					}

					$filterContent .= $renderer->render('product.product', [
						'config'      => $config,
						'product'     => $product,
						'columnWidth' => $columnWidth . ' es-category-id' . $categoryId,
						'imageSize'   => $imageSize,
						'showOptions' => $config->get('product_list_options', 1),
						'utility'     => $utility,
					]);
				}
				?>
			<?php elseif ($groupsInTabs): ?>
				<?php HTMLHelper::_('umart_ui.addTab', $categories[$categoryId]->title); ?>
				<?php include ModuleHelper::getLayoutPath('mod_umart_products', $layout . '_category'); ?>
				<?php HTMLHelper::_('umart_ui.endTab'); ?>
			<?php else: ?>
				<?php include ModuleHelper::getLayoutPath('mod_umart_products', $layout . '_category'); ?>
			<?php endif; ?>
		<?php endforeach; ?>

		<?php if ($gridFilters) : ?>
            <div uk-filter="target: .es-filter">
                <ul class="uk-subnav uk-flex-center uk-subnav-pill" uk-margin>
					<?php echo $filterNav; ?>
                </ul>
                <div class="es-filter uk-grid-small" uk-grid="masonry: true">
					<?php echo $filterContent; ?>
                </div>
            </div>
		<?php elseif ($groupsInTabs): ?>
			<?php echo HTMLHelper::_('umart_ui.renderTab', $params->get('tab_layout', 'tab-default')); ?>
		<?php endif; ?>

	<?php endif; ?>
</div>