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
$countAll = $params->get('count_products', 1) ? ModEasyshopCategoriesHelper::getCountAll($source) : [];
$columns  = (int) $params->get('columns', 2);
$children = $root->getChildren();
$count    = count($children);
$colWidth = ceil($count / $columns);
$showIcon = $params->get('show_icon', 1);
?>
<div id="mod-easyshop-categories-<?php echo $module->id; ?>"
     class="uk-scope es-scope mod-easyshop-categories<?php echo $moduleClassSfx; ?>">
    <div class="uk-grid-small uk-child-width-1-<?php echo $columns; ?>@m" uk-grid>
		<?php foreach ($root->getChildren() as $i => $child): ?>
			<?php if ($i % $colWidth === 0): ?>
				<?php echo '<div><ul class="uk-nav uk-nav-default">'; ?>
			<?php endif; ?>
            <li<?php echo $activeId == $child->id ? ' class="uk-active"' : ''; ?>>
                <a href="<?php echo ModEasyshopCategoriesHelper::getLink($child, $source); ?>">
					<?php

					if ($showIcon)
					{
						$nodeParams = new Registry((string) $child->params);

						if ($icon = $nodeParams->get('icon'))
						{
							echo HTMLHelper::_('easyshop.icon', $icon) . ' ';
						}
					}

					echo $child->title;

					?>
					<?php if (isset($countAll[$child->id])): ?>
                        <span class="es-product-count uk-text-meta">
                            <?php echo ' (' . $countAll[$child->id] . ')'; ?>
                        </span>
					<?php endif; ?>
                </a>
            </li>
			<?php if (($i + 1) % $colWidth === 0 || ($i + 1) > $count): ?>
				<?php echo '</ul></div>'; ?>
			<?php endif; ?>
		<?php endforeach; ?>
    </div>
</div>
