<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * @var array $displayData
 */
extract($displayData);

/** @var $category CategoryNode */
$showSubCatTitle = (int) $config->get('title_sub_categories', 2);
$rootUrl         = Uri::root(true);

?>
<?php if ($category->hasChildren()): ?>
    <div class="product-sub-categories uk-margin">
        <div class="uk-grid-match uk-grid-small uk-child-width-1-2 uk-child-width-1-4@m uk-child-width-1-3@s" uk-grid>
			<?php foreach ($category->getChildren() as $subCategory):
				$params = $subCategory->getParams();
				$image = $params->get('image') ? $rootUrl . '/' . $params->get('image') : null;
				?>
                <div class="product-sub-category">
                    <div class="uk-card uk-card-default uk-card-small">
						<?php if ($image): ?>
                            <div class="category-image uk-card-media-top">
                                <img src="<?php echo $image; ?>"
                                     alt="<?php echo htmlspecialchars($subCategory->title, ENT_COMPAT, 'UTF-8'); ?>"/>
                            </div>
						<?php endif; ?>
						<?php if ($showSubCatTitle !== 0): ?>
                            <div class="uk-card-body">
								<?php if ($showSubCatTitle === 2): ?>
                                    <h4 class="product-sub-category-title uk-h5">
                                        <a class="uk-link-reset"
                                           href="<?php echo Route::_(EasyshopHelperRoute::getCategoryRoute($subCategory, $subCategory->language), false); ?>">
											<?php echo $subCategory->title; ?>
                                        </a>
                                    </h4>
								<?php else: ?>
                                    <h4 class="product-sub-category-title uk-h5">
										<?php echo $subCategory->title; ?>
                                    </h4>
								<?php endif; ?>
                            </div>
						<?php endif; ?>
                    </div>
                </div>
			<?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

