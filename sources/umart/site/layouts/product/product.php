<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use ES\Classes\Renderer;
use ES\Classes\User;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;
/**
 * @var array    $displayData
 * @var integer  $columnWidth
 * @var stdClass $product
 * @var string   $imageSize
 * @var Registry $config
 * @var Renderer $renderer
 */
extract($displayData);
/**
 *
 * @since 1.1.0
 */

$countdown = (int) $config->get('product_list_countdown', 0);

/**
 *
 * @since 1.1.6
 */
$cartIconGroup = $config->get('cart_button_type', 'text_only') === 'icon_group';

/**
 * @var $unpublished
 * @since 1.2.5
 */
$unpublished = easyshop(User::class)->core('admin') && (int) $product->state !== 1;
?>
<div class="product-column <?php echo $columnWidth; ?>" data-category-id="<?php echo $product->category_id; ?>">
    <div class="uk-card uk-card-small uk-box-shadow-hover-small">
        <div class="product-box product-box-<?php echo $product->id; ?> uk-position-relative"
             data-product-id="<?php echo $product->id; ?>">
			<?php if (!empty($product->badgeData)): ?>
				<?php echo $renderer->render('product.badge.badge', $product->badgeData); ?>
			<?php endif; ?>
			<?php if (!empty($product->images)): ?>
				<?php // @since 1.2.3 Use image slider if this product has more than one image ?>
				<?php if ($config->get('product_list_images_slider', '1')): ?>
                    <div class="es-product-images">
                        <div class="uk-position-relative uk-visible-toggle" uk-slider>
                            <ul class="uk-slider-items uk-child-width-1-1">
								<?php foreach ($product->images as $image): ?>
                                    <li data-image="<?php echo htmlspecialchars($image->originBasePath); ?>">
										<?php if ($config->get('product_list_slider_cover')): ?>
                                            <div class="uk-cover-container">
                                                <canvas width="100%"
                                                        height="<?php echo (int) $config->get('product_list_cover_height', '200'); ?>"></canvas>
												<?php echo $renderer->render('media.image', [
													'image'      => $image,
													'size'       => $imageSize,
													'attributes' => [
														'alt'      => $image->title ?: $product->name,
														'uk-cover' => '',
													],
												]); ?>
                                            </div>
										<?php else: ?>
											<?php echo $renderer->render('media.image', [
												'image'      => $image,
												'size'       => $imageSize,
												'attributes' => [
													'alt' => $image->title ?: $product->name,
												],
											]); ?>
										<?php endif; ?>
                                    </li>
								<?php endforeach; ?>
                            </ul>
                            <a class="uk-position-center-left uk-position-small uk-hidden-hover" href="#"
                               uk-slidenav-previous uk-slider-item="previous"></a>
                            <a class="uk-position-center-right uk-position-small uk-hidden-hover" href="#"
                               uk-slidenav-next uk-slider-item="next"></a>
                        </div>
                    </div>
				<?php else: ?>
                    <div class="product-image es-main-image uk-card-media-top">
                        <a href="<?php echo $product->link; ?>"
                           title="<?php echo empty($product->images[0]->title) ? htmlspecialchars($product->name) : htmlspecialchars($product->images[0]->title); ?>"
                           class="uk-display-block uk-text-center">
							<?php echo $renderer->render('media.image', [
								'image'      => $product->images[0],
								'size'       => $imageSize,
								'attributes' => [
									'alt' => $product->images[0]->title ?: $product->name,
								],
							]); ?>
                        </a>
                    </div>
				<?php endif; ?>
			<?php endif; ?>
            <div class="product-body uk-card-body">
                <div class="product-caption">

                    <h3 class="product-name uk-h5 uk-margin-small">
                        <a href="<?php echo $product->link; ?>" class="uk-link-reset">
							<?php echo $product->name; ?>

							<?php if ($unpublished): ?>
                                <small class="uk-text-danger">
									<?php echo Text::_('JUNPUBLISHED'); ?>
                                    <span uk-icon="icon: close"></span>
                                </small>
							<?php endif; ?>

							<?php echo $renderer->render('product.stock', [
								'product' => $product,
								'config'  => $config,
							]); ?>
                        </a>
                    </h3>

					<?php if ($config->get('product_list_intro', 0) && !empty($product->summary)): ?>
                        <div class="product-description">
							<?php echo $product->summary; ?>
                        </div>
					<?php endif; ?>

					<?php if ($config->get('product_list_meta', 0)): ?>
						<?php echo $renderer->render('product.meta', [
							'product' => $product,
							'config'  => $config,
							'view'    => 'list',
						]); ?>
					<?php endif; ?>

					<?php if ($config->get('product_list_tags', 1) && !empty($product->productTags)): ?>
						<?php echo $renderer->render('product.tags', [
							'tags' => $product->productTags,
						]); ?>
					<?php endif; ?>

					<?php if ($countdown): ?>
						<?php echo $renderer->render('product.countdown', [
							'product'        => $product,
							'showLabel'      => (int) $config->get('product_list_countdown_show_label', 0),
							'countdownLabel' => (int) $config->get('product_list_countdown_label', 0),
						]); ?>
					<?php endif; ?>

                </div>

				<?php echo $renderer->render('product.price', [
					'product' => $product,
					'config'  => $config,
					'view'    => 'list',
				]); ?>

				<?php
				if ($config->get('product_list_add_to_cart', 1)
                    && true !== $product->expireDate
                    && !$product->outOfStock
                )
				{
					if ($config->get('product_list_options', 1) && !empty($product->options))
					{
						echo '<div class="product-options uk-form uk-margin-small-top" data-product-options>' . $product->options . '</div>';
					}

					$addToCartHtml = $renderer->render('cart.button.' . ($cartIconGroup ? 'icon' : 'normal'), [
						'product' => $product,
						'config'  => $config,
						'view'    => 'list',
					]);

					if ($cartIconGroup)
					{
						$product->extraBlockFlexDisplay = array_merge(
							[
								'addToCart' => $addToCartHtml,
							],
							$product->extraBlockFlexDisplay);
					}
					else
					{
						echo $addToCartHtml;
					}
				}
				?>

				<?php if ($product->extraBlockFlexDisplay): ?>
                    <div class="es-extra-flex-display uk-flex">
						<?php echo implode(PHP_EOL, $product->extraBlockFlexDisplay); ?>
                    </div>
				<?php endif; ?>

				<?php if ($product->extraBlockDisplay): ?>
					<?php echo implode(PHP_EOL, $product->extraBlockDisplay); ?>
				<?php endif; ?>
            </div>
        </div>
    </div>
</div>
