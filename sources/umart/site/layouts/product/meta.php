<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

/**
 * @var array    $displayData
 * @var stdClass $product
 * @var Registry $config
 * @var string   $view
 */

extract($displayData);

?>
<div class="product-meta">
    <ul class="uk-list">
		<?php if ($config->get('product_' . $view . '_sku', 1) && !empty($product->sku)): ?>
            <li>
                <span itemprop="sku">
                    <?php echo Text::sprintf('COM_EASYSHOP_PRODUCT_SKU_FORMAT', $product->sku); ?>
                </span>
            </li>
		<?php endif; ?>
		<?php if ((int) $config->get('product_' . $view . '_category', 1) > 0): ?>
            <li>
                <div class="uk-grid-small" uk-grid>
                    <div class="uk-width-auto">
						<?php echo Text::_('COM_EASYSHOP_CATEGORY') . ': '; ?>
                    </div>
                    <div class="uk-width-expand">
                        <ul class="uk-list uk-padding-remove uk-flex uk-flex-middle">
							<?php
							$showWithLink = $config->get('product_' . $view . '_category') == 1;

							if ($view == 'detail')
							{
								$slugs         = [];
								$categoryAlias = explode('/', $product->category->path);
								$categories    = Categories::getInstance('Easyshop');

								if ($categoryAlias)
								{
									array_pop($categoryAlias);

									foreach ($categoryAlias as $alias)
									{
										if ($categoryNode = $categories->getByAlias($alias))
										{
											if ($showWithLink)
											{
												echo '<li><a href="' . Route::_(EasyshopHelperRoute::getCategoryRoute($categoryNode, $categoryNode->language), false) . '" uk-icon="icon: triangle-right">' . $categoryNode->title . '</a></li>';
											}
											else
											{
												echo '<li><span uk-icon="icon: triangle-right">' . $categoryNode->title . '</span></li>';
											}
										}
									}
								}
							}

							?>

                            <li>
								<?php if ($showWithLink): ?>
                                    <a href="<?php echo $product->category->link; ?>" class="uk-link-reset">
										<?php echo $product->category->title; ?>
                                    </a>
								<?php else: ?>
                                    <span><?php echo $product->category->title; ?></span>
								<?php endif; ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </li>
		<?php endif; ?>
		<?php if ((int) $config->get('product_' . $view . '_brand', 1) > 0
			&& !empty($product->brand)
			&& (int) $config->get('product_' . $view . '_brand', 1) > 0
		): ?>
            <li>
				<?php if ($config->get('product_' . $view . '_brand') == 1): ?>
                    <a href="<?php echo $product->brand->link; ?>" class="uk-link-reset">
						<?php echo Text::sprintf('COM_EASYSHOP_PRODUCT_BRAND_FORMAT', $product->brand->title); ?>
                    </a>
				<?php else: ?>
                    <span><?php echo Text::sprintf('COM_EASYSHOP_PRODUCT_BRAND_FORMAT', $product->brand->title); ?></span>
				<?php endif; ?>
            </li>
		<?php endif; ?>
    </ul>
</div>
