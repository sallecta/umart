<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use ES\Classes\Renderer;
use ES\Classes\Utility;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;
/**
 * @var $config      Registry
 * @var $renderer    Renderer
 * @var $displayData array
 * @var $products    array
 */

if (empty($displayData['utility']))
{
	$displayData['utility'] = easyshop(Utility::class);
}

extract($displayData);
$imageSize = $config->get('product_list_image_size', 'medium');

if (!in_array($imageSize, ['tiny', 'small', 'medium', 'large', 'xlarge']))
{
	$imageSize = 'medium';
}

$dataGrid = 'uk-grid';

switch ($config->get('grid_mode', 'normal'))
{
	case 'match';
		$dataGrid .= ' uk-height-match="target: .uk-card"';
		break;

	case 'dynamic':
		$dataGrid = 'uk-grid="{gutter: ' . (int) $config->get('grid_dynamic_gutter', 20) . '}"';
		break;

	case 'parallax':
		$dataGrid = 'uk-grid-parallax';
		break;
}

if (!isset($slideRows) || (int) $slideRows < 1)
{
	$slideRows = 1;
}

$count = count($products);

// @since 1.1.9
$columnChildWidth = $utility->parseColumnClassSizes($config, true);
?>

<div data-product-list uk-slider="autoplay: <?php echo empty($slideAutoPlay) ? 'false' : 'true'; ?>">
    <div class="uk-position-relative uk-visible-toggle">
        <ul class="uk-grid-small uk-slider-items <?php echo $columnChildWidth; ?>" <?php echo $dataGrid; ?>>
			<?php
			foreach ($products as $i => $product)
			{
				echo $i % $slideRows === 0 ? '<li uk-margin>' : '';
				echo $renderer->render('product.product', [
					'config'      => $config,
					'product'     => $product,
					'columnWidth' => 'product-column',
					'imageSize'   => $imageSize,
					'showOptions' => $config->get('product_list_options', 1),
				]);

				echo ($i + 1) % $slideRows === 0 || ($i + 1) >= $count ? '</li>' : '';
			}
			?>
        </ul>
		<?php if (!empty($slideNav)): ?>
            <a class="uk-position-center-left uk-position-small uk-hidden-hover" href="#"
               uk-slidenav-previous uk-slider-item="previous"></a>
            <a class="uk-position-center-right uk-position-small uk-hidden-hover" href="#"
               uk-slidenav-next uk-slider-item="next"></a>
		<?php endif; ?>
    </div>
	<?php if (!empty($slideDots)): ?>
        <ul class="uk-slider-nav uk-dotnav uk-flex-center uk-margin"></ul>
	<?php endif; ?>
</div>
