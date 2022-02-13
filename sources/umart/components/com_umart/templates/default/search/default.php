<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$renderer = $this->getRenderer();
?>

<?php if ($this->task === 'search'): ?>
	<?php if (empty($this->products)): ?>
        <div uk-alert>
            <a class="uk-alert-close" uk-close></a>
			<?php echo Text::_('COM_UMART_WARNING_NO_PRODUCTS'); ?>
        </div>
	<?php else: ?>
        <div uk-alert>
            <a class="uk-alert-close" uk-close></a>
			<?php echo Text::sprintf('COM_UMART_WARNING_PRODUCTS_FOUND', count($this->products)); ?>
        </div>
        <div class="product-list <?php echo $this->config->get('list_mode', 'toggle'); ?>-view" data-view-mode>
			<?php if ($this->config->get('show_search_filter_bar', 1)): ?>
				<?php echo $renderer->render('product.filters', [
					'displayLimit'     => $this->config->get('list_limit'),
					'showToggleButton' => $this->config->get('show_search_toggle_button', 1),
					'filters'          => $this->filters,
					'filterKey'        => 'search',
				]); ?>
			<?php endif; ?>

			<?php echo $renderer->render('product.products', [
				'config'   => $this->config,
				'products' => $this->products,
			]); ?>
			<?php if ($this->pagination->getPagesCounter()): ?>
				<?php echo $renderer->render('pagination.pagination', $this->pagination->getData()); ?>
			<?php endif; ?>
        </div>
	<?php endif; ?>
<?php endif; ?>
