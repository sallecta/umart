<?php
/**
 * @version        1.1.4
 * @package        plg_system_umartukui
 * @author         JoomTech Team - http://github.com/sallecta/umart/
 * @copyright      Copyright (C) 2015 - 2020 github.com/sallecta/umart All Rights Reserved
 * @license        http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

/**
 * @var array $displayData
 */

extract($displayData);

?>
<div class="umartui-accordion">
    <ul uk-accordion="<?php echo empty($params['multiple']) ? '' : 'multiple: true'; ?>">
		<?php foreach ($items as $item): ?>
            <li<?php echo empty($item['params']['open']) ? '' : ' class="uk-open"'; ?>>
                <a class="uk-accordion-title"
                   href="#"<?php echo empty($item['params']['titleAnimation']) ? '' : ' uk-scrollspy="cls:uk-animation-' . $item['params']['titleAnimation'] . '"'; ?>>
					<?php if (isset($item['params']['icon'])): ?>
						<?php echo HTMLHelper::_('umartui.icon', $item['params']['icon']); ?>
					<?php endif; ?>
					<?php echo $item['title']; ?>
                </a>
                <div class="uk-accordion-content"<?php echo empty($item['params']['descriptionAnimation']) ? '' : ' uk-scrollspy="cls:uk-animation-' . $item['params']['descriptionAnimation'] . '"'; ?>>
					<?php echo $item['content']; ?>
                </div>
            </li>
		<?php endforeach; ?>
    </ul>
</div>
