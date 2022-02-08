<?php

/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\Utility;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var Utility $utility */
echo $this->getFormLayout('head');
$vars          = $this->get('layout.storage');
$this->columns = 7;
$showLogKey    = easyshop('config', 'show_log_key', 0);
$utility       = easyshop(Utility::class);

?>
<thead>
<tr>
    <th class="uk-table-shrink uk-text-center uk-text-nowrap">
		<?php echo HTMLHelper::_('easyshop.gridCheckall'); ?>
    </th>

    <th class="uk-table-expand">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_SUMMARY', 'a.string_key', $vars['listDirn'], $vars['listOrder']); ?>
    </th>

    <th class="uk-table-shrink uk-text-center uk-text-nowrap">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_DATE', 'a.created_date', $vars['listDirn'], $vars['listOrder']); ?>
    </th>

    <th class="uk-table-shrink uk-text-center uk-text-nowrap uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_USER', 'author', $vars['listDirn'], $vars['listOrder']); ?>
    </th>

    <th class="uk-width-small@m uk-table-shrink uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_REFERER', 'a.referer', $vars['listDirn'], $vars['listOrder']); ?>
    </th>

    <th class="uk-table-shrink uk-text-center uk-text-nowrap uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_IP', 'a.ip', $vars['listDirn'], $vars['listOrder']); ?>
    </th>

    <th class="uk-table-shrink uk-text-center uk-text-nowrap uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_ID', 'a.id', $vars['listDirn'], $vars['listOrder']); ?>
    </th>

</tr>
</thead>
<tbody>
<?php foreach ($this->items as $i => $item): ?>
    <tr>
        <td class="uk-text-center uk-text-nowrap">
			<?php echo HTMLHelper::_('easyshop.gridId', $i, $item->id); ?>
        </td>
        <td>
			<?php
			if (!empty($item->sprintf_data))
			{
				$sprintsData = json_decode($item->sprintf_data, true);
				array_unshift($sprintsData, $item->string_key);
				echo call_user_func_array('Joomla\\CMS\\Language\\Text::sprintf', array_values($sprintsData));
			}
			else
			{
				echo Text::_($item->string_key);
			}

			if ($showLogKey)
			{
				echo '<br/>' . '<small><strong>' . Text::_('COM_EASYSHOP_TRANSLATE_BY_KEY') . ': </strong>' . $item->string_key . '</small>';
			}
			?>
        </td>
        <td class="uk-text-center uk-text-nowrap">
			<?php echo $utility->displayDate($item->created_date, true, true); ?>
        </td>
        <td class="uk-text-center uk-text-nowrap uk-visible@m">
			<?php if (!empty($item->author)): ?>
				<?php echo $item->author . '<small>(' . $item->username . ')</small>'; ?>
			<?php endif; ?>
        </td>

        <td class="uk-visible@m uk-text-truncate">
            <div title="<?php echo htmlspecialchars($item->referer); ?>">
				<?php echo $item->referer; ?>
            </div>
        </td>

        <td class="uk-text-center uk-text-nowrap uk-visible@m">
			<?php echo easyshop('prepare.logip', $item->ip); ?>
        </td>

        <td class="uk-text-center uk-text-nowrap uk-visible@m">
			<?php echo $item->id; ?>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>

<?php echo $this->getFormLayout('foot'); ?>
