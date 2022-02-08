<?php

/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\Product;
use Joomla\CMS\Router\Route;

/**
 * @var array   $displayData
 * @var Product $productClass
 */

extract($displayData);
$productClass = easyshop(Product::class);
$count        = count($products);
$src          = 'index.php?option=com_easyshop&view=products&layout=modal&tmpl=component';

if (!empty($filterType))
{
	$src .= '&filter_type=' . urlencode($filterType);
}

$iframeAttributes = [
	'src'    => Route::_($src, false),
	'width'  => '100%',
	'height' => '450',
	'class'  => 'uk-height-large',
];

?>

<div class="uk-scope">
	<?php if ($multiple): ?>

		<?php ob_start(); ?>
        <div data-target-id="{value}">
            <div class="uk-card uk-card-small uk-card-body uk-background-default uk-position-relative uk-text-center es-border">
                <a class="uk-icon-link uk-position-small uk-position-top-right es-product-remove"
                   uk-icon="icon: trash"></a>
                <div class="uk-card-media-top">{image}</div>
                <div class="uk-h6 uk-margin-remove es-product-name">{name}</div>
                <input name="<?php echo $name; ?>" type="hidden" value="{value}"/>
            </div>
        </div>
		<?php $template = ob_get_clean(); ?>
        <button type="button" class="uk-button uk-button-small uk-button-primary"
                data-uk-toggle="target: #<?php echo $id; ?>_modal">
            <span uk-icon="icon: thumbnails"></span>
			<?php echo JText::_('COM_EASYSHOP_SELECT'); ?>
        </button>

        <div class="uk-placeholder uk-margin uk-padding-small">
            <script type="text/es-js-template">
				<?php echo $template; ?>
            </script>
            <div id="<?php echo $id; ?>-list"
                 class="es-product-field-list uk-grid-small uk-child-width-1-2 uk-child-width-1-6@xl uk-child-width-1-4@m uk-child-width-1-4@s"
                 uk-grid="masonry: true" uk-sortable>
				<?php

				if ($count)
				{
					foreach ($products as $product)
					{
						$images = $productClass->getImages($product->id);
						$title  = htmlspecialchars($product->name, ENT_COMPAT, 'UTF-8');

						if (empty($images[0]))
						{
							$image = '<img src="' . ES_MEDIA_URL . '/images/no-image.png"/>';
						}
						else
						{
							$image = $renderer->render('media.image', [
								'image'      => $images[0],
								'size'       => 'small',
								'attributes' => [
									'alt'   => $title,
									'class' => 'imageHandled',
								],

							]);
						}

						echo str_replace(['{value}', '{name}', '{image}'], [$product->id, $product->name, $image], $template);
					}
				}

				?>
            </div>
        </div>

	<?php else:
		$activeId = isset($products[0]) ? $products[0]->id : '';
		$activeName = isset($products[0]) ? $products[0]->name : '';
		?>
        <div class="uk-inline es-icon-input">
            <input type="text" id="<?php echo $id; ?>_name" class="uk-input" readonly
                   value="<?php echo $activeName; ?>"
				<?php echo empty($hint) ? '' : 'placeholder="' . htmlspecialchars(JText::_($hint)) . '"'; ?>/>
            <input type="hidden" name="<?php echo $name; ?>" id="<?php echo $id; ?>"
                   value="<?php echo $activeId; ?>"
				<?php echo $onChange; ?>/>
            <a id="<?php echo $id; ?>_select"
               class="uk-form-icon uk-form-icon-flip<?php echo $activeId ? ' uk-hidden' : ''; ?>" href="#"
               uk-toggle="target:#<?php echo $id; ?>_modal"
               uk-icon="icon: plus"></a>
            <a id="<?php echo $id; ?>_clear"
               class="uk-form-icon uk-form-icon-flip<?php echo !$activeId ? ' uk-hidden' : ''; ?>" href="#"
               onclick="_es.$(this).siblings('input').val('').trigger('change');"
               uk-icon="icon: close"></a>
        </div>
	<?php endif; ?>
    <div id="<?php echo $id; ?>_modal" class="uk-modal-container" uk-modal>
        <div class="uk-modal-dialog uk-modal-body">
            <a class="uk-modal-close-default" uk-close></a>
			<?php if ($multiple): ?>
                <div class="uk-modal-header">
                    <button type="button" class="uk-button uk-button-primary es-button-insert">
                        <span uk-icon="icon: check"></span>
						<?php echo JText::_('COM_EASYSHOP_INSERT'); ?>
                    </button>
                </div>
			<?php endif; ?>
            <iframe data-attributes="<?php echo htmlspecialchars(json_encode($iframeAttributes), ENT_COMPAT, 'UTF-8'); ?>"></iframe>
        </div>
    </div>
</div>
