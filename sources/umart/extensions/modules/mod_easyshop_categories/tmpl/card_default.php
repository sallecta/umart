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
use Joomla\CMS\HTML\HTMLHelper;

?>
<div>
    <div class="uk-card uk-card-default uk-card-small">
		<?php if ($image = $nodeParams->get('image')): ?>
            <div class="category-image uk-card-media-top">
                <img src="<?php echo Uri::root(true) . '/' . $image; ?>"
                     alt="<?php echo htmlspecialchars($node->title, ENT_COMPAT, 'UTF-8'); ?>"/>
            </div>
		<?php endif; ?>
        <div class="uk-card-body">
            <h4 class="category-title uk-h6">
                <a class="uk-link-reset"
                   href="<?php echo ModEasyshopCategoriesHelper::getLink($node, $params->get('source', 'category')); ?>">

					<?php

					if ($params->get('show_icon', 1))
					{
						if ($icon = $nodeParams->get('icon'))
						{
							echo '<div class="category-icon uk-margin-small">' . HTMLHelper::_('easyshop.icon', $icon) . '</div>';
						}
					}

					echo $node->title;

					if (isset($countAll[$node->id]))
					{
						echo '<span class="es-product-count uk-text-small uk-text-meta"> (' . $countAll[$node->id] . ')</span>';
					}

					?>
                </a>
            </h4>
        </div>
    </div>
</div>
