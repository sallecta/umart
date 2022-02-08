<?php

/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
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
