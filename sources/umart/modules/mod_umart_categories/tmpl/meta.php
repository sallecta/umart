<?php

/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;

/** @var $root \Joomla\Cms\Categories\CategoryNode */
$countAll = $params->get('count_products', 1) ? ModUmartCategoriesHelper::getCountAll($source) : [];
$columns  = (int) $params->get('columns', 2);
$iconDirection = $params->get('icon_direction', 'right');
$showIcon = $params->get('show_icon', 1);
?>
<div id="mod-umart-categories-<?php echo $module->id; ?>"
     class="uk-scope es-scope mod-umart-categories<?php echo $moduleClassSfx; ?>">
    <ul class="uk-nav uk-nav-default">
		<?php foreach ($root->getChildren() as $node):
			$hasChildren = $node->hasChildren();
			$class = $hasChildren ? 'uk-parent' : '';
			$class .= ($activeId == $node->id ? ' uk-active' : '');
			?>
            <li class="<?php echo trim($class); ?>">
                <a href="<?php echo ModUmartCategoriesHelper::getLink($node, $source); ?>" class=" uk-position-relative">
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
					<?php if ($hasChildren):?>
                        <span uk-icon="icon: chevron-<?php echo $iconDirection; ?>" class="uk-position-center-right"></span>
					<?php endif; ?>
                </a>
				<?php if ($hasChildren):
				    $children = $node->getChildren();
				    $count = count($children);
				    $colWidth = ceil($count / $columns);
				 ?>
                    <div style="width: 750px; max-width: none;"
                         uk-drop="pos: <?php echo $params->get('position', 'bottom-left'); ?>, boundary-align: true">
                        <div class="uk-card uk-card-body uk-card-<?php echo $params->get('card_layout', 'default'); ?> uk-width-<?php echo $params->get('drop_width', 'large'); ?>">
                            <div class="uk-drop-grid uk-grid-small uk-child-width-1-<?php echo $columns; ?>@m" uk-grid>
								<?php foreach ($children as $i => $child): ?>
									<?php if ($i % $colWidth === 0): ?>
										<?php echo '<div><ul class="uk-nav uk-nav-default">'; ?>
									<?php endif; ?>
                                            <li>
                                                <a href="<?php echo ModUmartCategoriesHelper::getLink($child, $source); ?>">
													<?php

                                                    if ($showIcon)
                                                    {
                                                        $nodeParams = new Registry((string) $child->params);

                                                        if ($icon = $nodeParams->get('icon'))
                                                        {
                                                            echo HTMLHelper::_('umart.icon', $icon) . ' ';
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
                                    <?php if (($i+1) % $colWidth === 0 || ($i+1) > $count): ?>
										<?php echo '</ul></div>'; ?>
									<?php endif; ?>
								<?php endforeach; ?>
                            </div>
                        </div>
                    </div>
				<?php endif; ?>
            </li>
		<?php endforeach; ?>
    </ul>
</div>
