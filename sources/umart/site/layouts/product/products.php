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

/** Utility for countdown @since 1.1.0 */

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

$gridMode  = $config->get('grid_mode', 'normal');
$gridClass = 'uk-grid-small';
$dataGrid  = 'uk-grid';

switch (strtolower($gridMode))
{
	case 'match';
		$gridClass .= ' uk-grid-match';
		break;

	case 'dynamic':
		$dataGrid = 'uk-grid="masonry: true"';
		break;

	case 'parallax':
		$dataGrid = 'uk-grid="parallax: 150"';
		break;
}

// @since 1.1.6
$columnWidth = $utility->parseColumnClassSizes($config);

?>
<div class="<?php echo $gridClass; ?>" <?php echo $dataGrid; ?> data-product-list>
	<?php
	foreach ($products as $product)
	{
		echo $renderer->render('product.product', [
			'config'      => $config,
			'product'     => $product,
			'columnWidth' => $columnWidth,
			'imageSize'   => $imageSize,
			'utility'     => $utility,
		]);
	}
	?>
</div>
