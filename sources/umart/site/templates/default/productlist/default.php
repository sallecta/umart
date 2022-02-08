<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$config   = $this->config;
$category = $this->category;
$renderer = $this->getRenderer();
$products = $this->items;

?>
<?php if ($config->get('show_category', 0)): ?>
	<?php echo $renderer->render('product.category', [
		'config'   => $config,
		'category' => $category,
	]); ?>
<?php endif; ?>
<?php if ($config->get('show_sub_categories', 0)): ?>
	<?php echo $renderer->render('product.categories', [
		'config'   => $config,
		'category' => $category,
	]); ?>
<?php endif; ?>
<?php if (empty($products)): ?>
    <div uk-alert>
        <a class="uk-alert-close" uk-close></a>
		<?php echo Text::_('COM_EASYSHOP_WARNING_NO_PRODUCTS'); ?>
    </div>
<?php else: ?>
    <div class="product-list <?php echo $config->get('list_mode', 'toggle'); ?>-view" data-view-mode>
		<?php if ($config->get('show_filter_bar', 1)): ?>
			<?php echo $renderer->render('product.filters', [
				'displayLimit'     => $config->get('list_limit'),
				'showToggleButton' => $config->get('show_toggle_button', 1),
				'filters'          => $this->filters,
				'filterKey'        => 'list',
			]); ?>
		<?php endif; ?>
		<?php echo $renderer->render('product.products', [
			'config'   => $config,
			'products' => $products,
		]); ?>
		<?php if ($this->pagination->getPagesCounter()): ?>
			<?php echo $renderer->render('pagination.pagination', $this->pagination->getData()); ?>
		<?php endif; ?>
    </div>
<?php endif; ?>
