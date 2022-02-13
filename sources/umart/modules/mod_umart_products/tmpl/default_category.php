<?php

/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
?>
<div class="uk-margin">
	<?php if ($config->get('show_category', 0)): ?>
		<?php echo $renderer->render('product.category', [
			'config'   => $config,
			'category' => $categories[$categoryId],
		]); ?>
	<?php endif; ?>
	<?php if ($params->get('show_sub_categories', 0)): ?>
		<?php echo $renderer->render('product.categories', [
			'config'   => $config,
			'category' => $products[0]->category,
		]); ?>
	<?php endif; ?>
	<?php echo $renderer->render($layoutId, [
		'config'        => $config,
		'products'      => $products,
		'slideNav'      => $slideNav,
		'slideDots'     => $slideDots,
		'slideAutoPlay' => $slideAutoPlay,
		'columnInRow'   => $columnInRow,
		'slideRows'     => $slideRows,
	]); ?>
</div>
