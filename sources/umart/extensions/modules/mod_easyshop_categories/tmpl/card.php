<?php

/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

/** @var $root \Joomla\Cms\Categories\CategoryNode */
$gridClass       = 'uk-grid-small uk-grid-match uk-flex-center uk-margin';
$rootUrl         = Uri::root(true);
$gridWidthMaps   = [
	''    => 'widthXSmall',
	'@s'  => 'widthSmall',
	'@m'  => 'widthMedium',
	'@l'  => 'widthLarge',
	'@xl' => 'widthXLarge',
];

foreach ($gridWidthMaps as $suffix => $paramName)
{
	$number = trim($params->get($paramName, ''));

	if (is_numeric($number))
	{
		$gridClass .= ' uk-child-width-1-' . $number . $suffix;
	}
}

?>
<div class="mod-easyshop-categories category-thumbnail uk-scope es-scope">
    <div class="<?php echo $gridClass; ?>" uk-grid uk-margin>
		<?php

		foreach ($root->getChildren() as $node)
		{
			ModEasyshopCategoriesHelper::loadCardLayout($node, $params);
		}

		?>
    </div>
</div>
