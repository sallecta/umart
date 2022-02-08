<?php

/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;

/** @var $root \Joomla\Cms\Categories\CategoryNode */
$accordion = $params->get('accordion', 1);
$open      = $accordion && $params->get('visible', 1) ? ' uk-open' : '';
$countAll  = $params->get('count_products', 1) ? ModEasyshopCategoriesHelper::getCountAll($source) : [];
$showIcon  = $params->get('show_icon', 1);
?>
<div id="mod-easyshop-categories-<?php echo $module->id; ?>"
     class="uk-scope es-scope mod-easyshop-categories<?php echo $moduleClassSfx; ?>">
    <ul class="uk-nav uk-nav-<?php echo $params->get('nav_style', 'default') ?><?php echo $accordion ? ' uk-nav-parent-icon' : ''; ?>"
		<?php echo $accordion ? ' uk-nav="multiple: true"' : ''; ?>>
		<?php foreach ($root->getChildren() as $node):
			$hasChildren = $node->hasChildren();
			$class = $hasChildren ? 'uk-parent' . $open : '';
			$class .= ($activeId == $node->id ? ' uk-active' : '');

			?>
            <li class="<?php echo trim($class); ?>">
                <a href="<?php echo ModEasyshopCategoriesHelper::getLink($node, $source); ?>">
					<?php

					if ($showIcon)
					{
						$nodeParams = new Registry((string) $node->params);

						if ($icon = $nodeParams->get('icon'))
						{
							echo HTMLHelper::_('easyshop.icon', $icon) . ' ';
						}
					}

					echo $node->title;

					?>

					<?php if (isset($countAll[$node->id])): ?>
                        <span class="es-product-count uk-text-meta">
                            <?php echo ' (' . $countAll[$node->id] . ')'; ?>
                        </span>
					<?php endif; ?>
                </a>
				<?php if ($hasChildren): ?>
                    <ul class="uk-nav-sub">
						<?php echo ModEasyshopCategoriesHelper::loadChildren($node, $activeId, $countAll, $source, $showIcon); ?>
                    </ul>
				<?php endif; ?>
            </li>
		<?php endforeach; ?>
    </ul>
</div>
