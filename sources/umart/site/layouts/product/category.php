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

/**
 * @var array $displayData
 */
extract($displayData);
$image       = $category->getParams()->get('image') ? Uri::root(true) . '/' . $category->params->get('image') : null;
$description = $config->get('category_description') ? trim($category->description) : null;
$cols        = $image ? '2-3@s' : '1-1';

/**
 * @var  $showCatTitle
 * @since 1.2.3
 */
$showCatTitle = $config->get('showCategoryTitle', 1);

?>
<div class="product-category uk-margin">
    <div class="uk-grid-small uk-flex-middle" uk-grid>
		<?php if ($image): ?>
            <div class="category-image uk-width-1-3@m uk-width-1-2@s">
                <img src="<?php echo $image; ?>"
                     alt="<?php echo htmlspecialchars($category->title, ENT_COMPAT, 'UTF-8'); ?>"/>
            </div>
		<?php endif; ?>
        <div class="uk-width-2-3@m uk-width-1-2@s">
			<?php if ($showCatTitle): ?>
                <h2 class="category-title uk-text-uppercase uk-h4 uk-margin-small-bottom">
					<?php echo $category->title; ?>
                </h2>
			<?php endif; ?>
			<?php if ($description): ?>
                <div class="category-desc">
					<?php echo $description; ?>
                </div>
			<?php endif; ?>
        </div>
    </div>
</div>
