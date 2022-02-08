<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

echo $this->getFormLayout('head');
$vars          = $this->get('layout.storage');
$user          = $vars['user'];
$userId        = (int) $user->get()->id;
$this->columns = 6;
$reflector     = $this->escape($this->state->get('filter.reflector'));
$coreCreate    = $user->core('create');
$coreEdit      = $user->core('edit');
$coreEditState = $user->core('edit.state');
$coreEditOwn   = $user->core('edit.own');
$coreCheckIn   = $user->core('manage');
?>
<thead>
<tr>
    <th class="uk-table-shrink uk-text-center uk-text-nowrap uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $vars['listDirn'], $vars['listOrder'], null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
    </th>
    <th class="uk-table-shrink uk-text-center uk-text-nowrap">
		<?php echo HTMLHelper::_('easyshop.gridCheckall'); ?>
    </th>
    <th style="min-width:55px" class="uk-table-shrink uk-text-center uk-text-nowrap">
		<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th>
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_NAME', 'a.name', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
	<?php if ($reflector == 'com_easyshop.user'): ?>
        <th class="uk-table-shrink uk-text-center uk-text-nowrap">
			<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_CHECKOUT_FIELD', 'a.checkout_field', $vars['listDirn'], $vars['listOrder']); ?>
        </th>
	<?php else: ?>
        <th class="uk-table-shrink uk-text-center uk-text-nowrap">
			<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_GROUP', 'group_title', $vars['listDirn'], $vars['listOrder']); ?>
        </th>
	<?php endif; ?>
    <th class="uk-table-shrink uk-text-center uk-text-nowrap">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_ID', 'a.id', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
</tr>
</thead>
<tbody>
<?php foreach ($this->items as $i => $item):
	$canCheckin = $coreCheckIn || $item->checked_out == $userId || $item->checked_out == 0;
	$canEditOwn = $coreEditOwn && $item->created_by == $userId;
	$canChange = $coreEditState && $canCheckin;
	?>
    <tr>
        <td class="order uk-text-center uk-text-nowrap uk-visible@m">
			<?php
			$iconClass = '';
			if (!$canChange)
			{
				$iconClass = ' inactive';
			}
            elseif (!$vars['saveOrder'])
			{
				$iconClass = ' inactive tip-top hasTooltip" title="' . HTMLHelper::tooltipText('JORDERINGDISABLED');
			}
			?>
            <span class="sortable-handler<?php echo $iconClass ?>">
				    <span uk-icon="more-vertical"><span>
			    </span>
				<?php if ($canChange && $vars['saveOrder']) : ?>
                    <input type="text" style="display:none" name="order[]" size="5"
                           value="<?php echo $item->ordering; ?>"
                           class="width-20 text-area-order "/>
				<?php endif; ?>
        </td>
        <td class="uk-text-center uk-text-nowrap">
			<?php echo HTMLHelper::_('easyshop.gridId', $i, $item->id); ?>
        </td>
        <td class="uk-text-center uk-text-nowrap">
			<?php if ($item->protected): ?>
                <a class="uk-button uk-button-default uk-button-small"
                   uk-tooltip
                   uk-icon="icon: lock"
                   title="<?php echo Text::_('COM_EASYSHOP_FIELD_BE_PROTECTED'); ?>">
                </a>
			<?php else: ?>
				<?php if ($item->checked_out) : ?>
					<?php echo HTMLHelper::_('easyshop.gridCheckedOut', $i, $item->editor, $item->checked_out_time, $vars['prefix'], $canCheckin); ?>
				<?php else: ?>
					<?php echo HTMLHelper::_('easyshop.gridPublished', $item->state, $i, $vars['prefix'], $canChange, 'cb'); ?>
				<?php endif; ?>
			<?php endif; ?>
        </td>
        <td>
			<?php if ($coreEdit || $coreEditOwn) : ?>
                <a href="<?php echo Route::_('index.php?option=com_easyshop&task=customfield.edit&id=' . $item->id . '&reflector=' . $reflector, false); ?>"
                   title="<?php echo Text::_('JACTION_EDIT'); ?>" uk-tooltip>
					<?php echo $this->escape($item->name); ?></a>
			<?php else : ?>
				<?php echo $this->escape($item->name); ?>
			<?php endif; ?>
            <span class="uk-text-small">
				    <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                </span>
        </td>
		<?php if ($reflector == 'com_easyshop.user'): ?>
            <td class="uk-text-center uk-text-nowrap">
                <a href="<?php echo Route::_('index.php?option=com_easyshop&task=customfield.checkoutField&reflector=' . $reflector . '&id=' . $item->id . '&' . JSession::getFormToken() . '=1', false); ?>"
                   class="uk-button uk-button-default uk-button-small">
                    <span uk-icon="icon: <?php echo $item->checkout_field ? 'check' : 'close'; ?>"></span>
                </a>
            </td>
		<?php else: ?>
            <td class="uk-text-center uk-text-nowrap">
				<?php echo $item->group_title; ?>
            </td>
		<?php endif; ?>
        <td class="uk-text-center uk-text-nowrap">
			<?php echo $item->id; ?>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
<?php echo $this->getFormLayout('foot'); ?>
