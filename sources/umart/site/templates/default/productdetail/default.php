<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\Media;
use ES\Classes\User;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$renderer  = $this->getRenderer();
$config    = $this->config;
$imageSize = $config->get('product_detail_image_size', 'large');

if (!in_array($imageSize, ['tiny', 'small', 'medium', 'large', 'xlarge']))
{
	$imageSize = 'large';
}

$images = $this->product->images;

/**
 * @var Media $mediaClass
 * @since 1.3.0
 */
$mediaClass = easyshop(Media::class);
$media      = [];

foreach ($this->product->images as $file)
{
	$mime    = $mediaClass->getMimeByFile(ES_MEDIA . '/' . $file->file_path);
	$isVideo = strpos($mime, 'video') === 0;

	if ($isVideo)
	{
		$mediaObject              = new stdClass;
		$mediaObject->type        = 'video';
		$mediaObject->title       = $file->title;
		$mediaObject->description = $file->description;
		$mediaObject->source      = ES_MEDIA_URL . '/' . $file->file_path;
		$mediaObject->preview     = '<video src="' . $mediaObject->source . '" controls playsinline uk-video></video>';
		$mediaObject->poster      = '<span uk-icon="icon: play-circle; ratio: 2.5"></span>';
	}
	else
	{
		$mediaObject = $file;

		if (!empty($file->title)
			&& preg_match('/^https?:\/\//', ltrim($file->title))
		)
		{
			$mediaObject->type   = 'video';
			$mediaObject->source = $file->title;
			$file->title         = null;
			$mediaObject->poster = $renderer->render('media.image',
				[
					'image'      => $file,
					'size'       => 'tiny',
					'attributes' => [
						'alt' => $file->title,
					],
				]
			);
		}
		else
		{
			$mediaObject->type   = 'image';
			$mediaObject->source = $file->image;
		}

		$mediaObject->preview = $renderer->render('media.image',
			[
				'image'      => $file,
				'size'       => $imageSize,
				'attributes' => [
					'alt' => $file->title,
				],
			]
		);
	}

	$media[] = $mediaObject;
}

$countMedia   = count($media);
$leftWidth    = $countMedia ? 'uk-width-1-2@s uk-width-1-3@m uk-width-2-5@l' : 'uk-width-1-1';
$rightWidth   = $countMedia ? 'uk-width-1-2@s uk-width-2-3@m uk-width-3-5@l' : 'uk-width-1-1';
$absoluteLink = Uri::getInstance()->toString(['scheme', 'host']) . $this->product->link;
$thumbs       = '';

// Countdown @since 1.1.0
$countdown = (int) $this->product->params->get('product_detail_countdown', 0);

/**
 * @var $cartIconGroup boolean
 * @since 1.1.6
 */
$cartIconGroup = $config->get('cart_button_type', 'text_only') === 'icon_group';

/**
 * @var $unpublished
 * @since 1.2.5
 */
$unpublished = easyshop(User::class)->core('admin') && (int) $this->product->state !== 1;

?>
<div id="product-detail" itemscope itemtype="http://schema.org/Product">
    <meta itemprop="interactionCount" content="UserPageVisits:<?php echo $this->product->hits; ?>"/>
	<?php echo $this->event->execute('onProductBeforeDisplay'); ?>
    <div class="uk-grid uk-grid-small uk-margin-bottom" data-product-id="<?php echo $this->product->id; ?>">
		<?php if ($countMedia): ?>
            <div class="<?php echo $leftWidth; ?>" uk-lightbox="toggle: a.es-media">
                <div class="es-product-images">
                    <div class="es-main-media es-main-<?php echo $media[0]->type; ?> uk-margin-small-bottom uk-text-center">
                        <a class="es-media" href="<?php echo $media[0]->source; ?>"
							<?php echo $media[0]->title ? 'data-alt="' . htmlspecialchars($media[0]->title) . '"' : ''; ?>
							<?php echo $media[0]->description ? 'data-caption="' . htmlspecialchars($media[0]->description) . '"' : ''; ?>
                        >
							<?php echo $media[0]->preview; ?>
                        </a>
						<?php array_shift($media); ?>
                    </div>
					<?php if ($countMedia > 1): ?>
                        <div uk-slider>
                            <div class="uk-position-relative uk-visible-toggle">
                                <ul class="uk-slider-items es-thumbnails-slider uk-child-width-1-2 uk-child-width-1-4@m uk-grid-small uk-grid">
									<?php foreach ($media as $i => $mediaObject): ?>
										<?php if ($mediaObject->type === 'image'): ?>
                                            <li data-image="<?php echo htmlspecialchars($mediaObject->originBasePath); ?>">
												<?php if ($config->get('product_detail_thumb_cover')): ?>
                                                    <a class="uk-display-block uk-cover-container es-media"
                                                       href="<?php echo $mediaObject->large; ?>"
														<?php echo $mediaObject->title ? 'data-alt="' . htmlspecialchars($mediaObject->title) . '"' : ''; ?>
														<?php echo $mediaObject->description ? 'data-caption="' . htmlspecialchars($mediaObject->description) . '"' : ''; ?>
                                                    >
                                                        <canvas width="100%"
                                                                height="<?php echo (int) $config->get('product_detail_thumb_height', '90'); ?>"></canvas>
														<?php echo $renderer->render('media.image', [
															'image'      => $mediaObject,
															'size'       => $imageSize,
															'attributes' => [
																'alt'      => $mediaObject->title ?: $this->product->name,
																'uk-cover' => '',
															],
														]); ?>
                                                    </a>
												<?php else: ?>
                                                    <a class="es-media" href="<?php echo $mediaObject->large; ?>"
														<?php echo $mediaObject->title ? 'data-alt="' . htmlspecialchars($mediaObject->title) . '"' : ''; ?>
														<?php echo $mediaObject->description ? 'data-caption="' . htmlspecialchars($mediaObject->description) . '"' : ''; ?>
                                                    >
														<?php echo $renderer->render('media.image', [
															'image'      => $mediaObject,
															'size'       => 'tiny',
															'attributes' => [
																'alt' => $mediaObject->title ?: $this->product->name,
															],
														]); ?>
                                                    </a>
												<?php endif; ?>
                                            </li>
										<?php else: ?>
                                            <li>
                                                <a class="es-media uk-link-muted uk-flex uk-flex-middle uk-flex-center uk-height-1-1 es-border"
                                                   href="<?php echo $mediaObject->source; ?>"
													<?php echo $mediaObject->title ? 'data-alt="' . htmlspecialchars($mediaObject->title) . '"' : ''; ?>
													<?php echo $mediaObject->description ? 'data-caption="' . htmlspecialchars($mediaObject->description) . '"' : ''; ?>
                                                >
													<?php echo $mediaObject->poster; ?>
                                                </a>
                                            </li>
										<?php endif; ?>
									<?php endforeach; ?>
                                </ul>
                                <a class="uk-position-center-left uk-position-small uk-hidden-hover" href="#"
                                   uk-slidenav-previous uk-slider-item="previous"></a>
                                <a class="uk-position-center-right uk-position-small uk-hidden-hover" href="#"
                                   uk-slidenav-next uk-slider-item="next"></a>
                            </div>
                        </div>
					<?php endif; ?>
                </div>
            </div>
		<?php endif; ?>
        <div class="<?php echo $rightWidth; ?> product-summary uk-position-relative">

			<?php if (!empty($this->product->badgeData)): ?>
				<?php echo $renderer->render('product.badge.badge', $this->product->badgeData); ?>
			<?php endif; ?>

            <h1 itemprop="name" class="product-name uk-h2" data-product-name>
				<?php echo $this->escape($this->product->name); ?>

				<?php if ($unpublished): ?>
                    <small class="uk-text-danger">
						<?php echo Text::_('JUNPUBLISHED'); ?>
                        <span uk-icon="icon: close"></span>
                    </small>
				<?php endif; ?>

				<?php echo $renderer->render('product.stock', [
					'product' => $this->product,
					'config'  => $config,
				]); ?>
            </h1>

			<?php echo $this->event->execute('onProductAfterDisplayName'); ?>

			<?php if ($config->get('product_detail_meta', 0)): ?>
				<?php echo $renderer->render('product.meta', [
					'product' => $this->product,
					'config'  => $config,
					'view'    => 'detail',
				]); ?>
			<?php endif; ?>

			<?php if ($config->get('product_detail_intro', 1) && !empty($this->product->summary)): ?>
                <div class="product-intro" itemprop="description" data-product-intro>
					<?php echo $this->product->summary; ?>
                </div>
			<?php endif; ?>

			<?php if ($config->get('product_detail_field', 'tab') == 'info'
				&& count($this->product->customfields)
			): ?>
                <div class="es-product-fields-info">
					<?php foreach ($this->product->customfields as $customfield): ?>
						<?php if (count($customfield['fields'])): ?>
                            <div class="uk-margin">
                                <div class="uk-heading-bullet">
									<?php echo $customfield['title']; ?>
                                </div>
								<?php echo $renderer->render('product.field.' . $customfield['params']->get('fields_output_layout', 'table'), [
									'fields' => $customfield['fields'],
								]); ?>
                            </div>
						<?php endif; ?>
					<?php endforeach; ?>
                </div>
				<?php echo $this->event->execute('onProductAfterDisplayFields'); ?>
			<?php endif; ?>

			<?php echo $renderer->render('product.price', [
				'product' => $this->product,
				'config'  => $config,
				'view'    => 'detail',
			]); ?>

			<?php

			if ($this->product->params->get('product_detail_add_to_cart', 1))
			{
				// @since 1.1.6
				if (!empty($this->product->options))
				{
					echo '<div class="product-options uk-form uk-margin-small-top" data-product-options>' . $this->product->options . '</div>';
				}

				$addToCartHtml = $renderer->render('cart.button.' . ($cartIconGroup ? 'icon' : 'normal'), [
					'product' => $this->product,
					'config'  => $config,
					'view'    => 'detail',
				]);

				if ($cartIconGroup)
				{
					$this->product->extraDetailFlexDisplay = array_merge([
						'addToCart' => $addToCartHtml,
					], $this->product->extraDetailFlexDisplay);
				}
				else
				{
					echo $addToCartHtml;
				}
			}

			?>

			<?php if ($this->product->extraDetailFlexDisplay): ?>
                <div class="es-extra-flex-display uk-flex">
					<?php echo implode(PHP_EOL, $this->product->extraDetailFlexDisplay); ?>
                </div>
			<?php endif; ?>

			<?php if ($this->product->extraDetailDisplay): ?>
				<?php echo implode(PHP_EOL, $this->product->extraDetailDisplay); ?>
			<?php endif; ?>

			<?php if ($config->get('product_detail_tags', 1) && !empty($this->product->productTags)): ?>
				<?php echo $renderer->render('product.tags', [
					'tags' => $this->product->productTags,
				]); ?>
			<?php endif; ?>

			<?php echo $this->event->execute('onProductAfterDisplaySummary'); ?>

			<?php if ($countdown): ?>
				<?php echo $renderer->render('product.countdown', [
					'product'        => $this->product,
					'showLabel'      => (int) $this->product->params->get('product_detail_countdown_show_label', 0),
					'countdownLabel' => (int) $this->product->params->get('product_detail_countdown_label', 0),
				]); ?>
			<?php endif; ?>
        </div>
    </div>
    <div class="uk-clearfix"></div>
	<?php HTMLHelper::_('ukui.addTab', Text::_('COM_EASYSHOP_DESCRIPTION'), 'es-icon-info'); ?>
    <div class="product-text">
		<?php echo $this->product->description; ?>
    </div>
	<?php HTMLHelper::_('ukui.endTab'); ?>

	<?php if ($config->get('product_detail_field', 'tab') == 'tab'
		&& count($this->product->customfields)
	): ?>
		<?php foreach ($this->product->customfields as $customfield): ?>
			<?php if (count($customfield['fields'])): ?>
				<?php HTMLHelper::_('ukui.addTab', $customfield['title'], $customfield['params']->get('icon')); ?>
				<?php echo $renderer->render('product.field.' . $customfield['params']->get('fields_output_layout', 'table'), [
					'fields' => $customfield['fields'],
				]); ?>
				<?php HTMLHelper::_('ukui.endTab'); ?>
			<?php endif; ?>
		<?php endforeach; ?>
		<?php echo $this->event->execute('onProductAfterDisplayFields'); ?>
	<?php endif; ?>
	<?php echo $this->event->execute('onProductBeforeRenderTab'); ?>
	<?php echo HTMLHelper::_('ukui.renderTab', $config->get('product_detail_tab_layout', 'tab-default'), ['responsive' => true]); ?>
	<?php echo $this->event->execute('onProductAfterDisplay'); ?>
</div>
