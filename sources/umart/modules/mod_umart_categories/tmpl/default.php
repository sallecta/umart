<?php

/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;

/** @var $root \Joomla\Cms\Categories\CategoryNode */
$accordion = $params->get('accordion', 1);
$open      = $accordion && $params->get('visible', 1) ? ' uk-open' : '';
$countAll  = $params->get('count_products', 1) ? ModUmartCategoriesHelper::getCountAll($source) : [];
$showIcon  = $params->get('show_icon', 1);
?>
<div id="mod-umart-categories-<?php echo $module->id; ?>"
     class="uk-scope es-scope mod-umart-categories<?php echo $moduleClassSfx; ?>">
    <ul class="uk-nav uk-nav-<?php echo $params->get('nav_style', 'default') ?><?php echo $accordion ? ' uk-nav-parent-icon' : ''; ?>"
		<?php echo $accordion ? ' uk-nav="multiple: true"' : ''; ?>>
		<?php foreach ($root->getChildren() as $node):
			$hasChildren = $node->hasChildren();
			$class = $hasChildren ? 'uk-parent' . $open : '';
			$class .= ($activeId == $node->id ? ' uk-active' : '');

			?>
            <li class="<?php echo trim($class); ?>">
                <a href="<?php echo ModUmartCategoriesHelper::getLink($node, $source); ?>">
					<?php

					if ($showIcon)
					{
						$nodeParams = new Registry((string) $node->params);

						if ($icon = $nodeParams->get('icon'))
						{
							echo HTMLHelper::_('umart.icon', $icon) . ' ';
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
						<?php echo ModUmartCategoriesHelper::loadChildren($node, $activeId, $countAll, $source, $showIcon); ?>
                    </ul>
				<?php endif; ?>
            </li>
		<?php endforeach; ?>
    </ul>
</div>
