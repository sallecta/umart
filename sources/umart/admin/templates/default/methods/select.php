<?php

/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\User;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$i          = 0;
$user       = easyshop(User::class);
$type       = $this->state->get('filter.type');
$link       = 'index.php?option=com_easyshop&task=method.add&filter_type=' . $type;
$methodName = Text::_('COM_EASYSHOP_' . strtoupper($type) . '_METHOD');
$coreCreate = $user->core('create');

?>
<table class="uk-table uk-table-hover uk-table-divider uk-table-small">
    <thead>
    <tr>
        <th class="uk-table-shrink uk-text-center uk-text-nowrap">#</th>
        <th><?php echo Text::_('COM_EASYSHOP_NAME') ?></th>
        <th class="uk-table-shrink uk-text-center uk-text-nowrap">
			<?php echo Text::_('COM_EASYSHOP_TYPE'); ?>
        </th>
        <th class="uk-table-shrink uk-text-center uk-text-nowrap uk-visible@m">
			<?php echo Text::_('COM_EASYSHOP_ID'); ?>
        </th>
    </tr>
    </thead>
    <tbody>
	<?php foreach ($this->get('Methods') as $id => $method): ?>
        <tr>
            <td class="uk-table-shrink uk-text-center uk-text-nowrap">
				<?php echo ++$i; ?>
            </td>
            <td>
				<?php if ($coreCreate): ?>
                    <a href="<?php echo Route::_($link . '&method_id=' . $id, false); ?>">
						<?php echo Text::_(strtoupper($method->origin_name)); ?>
                    </a>
				<?php else: ?>
					<?php echo Text::_(strtoupper($method->origin_name)); ?>
				<?php endif; ?>
            </td>
            <td class="uk-text-center uk-text-nowrap">
				<?php echo $methodName; ?>
            </td>
            <td class="uk-text-center uk-text-nowrap uk-visible@m">
				<?php echo $method->method_id; ?>
            </td>
        </tr>
	<?php endforeach; ?>
    </tbody>
</table>
