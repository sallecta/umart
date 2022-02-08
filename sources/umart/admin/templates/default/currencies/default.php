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

echo $this->getFormLayout('head');
$vars          = $this->get('layout.storage');
$user          = $vars['user'];
$userId        = (int) $user->get()->id;
$this->columns = 9;
$coreCreate    = $user->core('create');
$coreEdit      = $user->core('edit');
$coreEditState = $user->core('edit.state');
$coreEditOwn   = $user->core('edit.own');
$coreCheckIn   = $user->core('manage');
?>
<thead>
<tr>
    <th class="uk-table-shrink uk-text-center uk-text-nowrap uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $vars['listDirn'], $vars['listOrder'], null, 'asc',
			'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
    </th>
    <th class="uk-table-shrink uk-text-center uk-text-nowrap">
		<?php echo HTMLHelper::_('easyshop.gridCheckall'); ?>
    </th>
    <th class="uk-table-shrink uk-text-center uk-text-nowrap">
		<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th>
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_NAME', 'a.name', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-table-shrink uk-text-center uk-text-nowrap">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_DEFAULT', 'a.is_default', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-table-shrink uk-text-center uk-text-nowrap">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_CODE', 'a.code', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-table-shrink uk-text-center uk-text-nowrap">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_SYMBOL', 'a.symbol', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-table-shrink uk-text-center uk-text-nowrap uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_RATE', 'a.rate', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-table-shrink uk-text-center uk-text-nowrap uk-visible@m">
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
        <td class="order uk-text-nowrap uk-text-center uk-visible@m">
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
            <span class="sortable-handler<?php echo $iconClass; ?>">
				    <span uk-icon="more-vertical"><span>
			    </span>
				<?php if ($canChange && $vars['saveOrder']) : ?>
                    <input type="text" style="display:none" name="order[]" size="5"
                           value="<?php echo $item->ordering; ?>"
                           class="width-20 text-area-order "/>
				<?php endif; ?>
        </td>
        <td class="uk-text-nowrap uk-text-center">
			<?php echo HTMLHelper::_('easyshop.gridId', $i, $item->id); ?>
        </td>
        <td class="uk-text-nowrap uk-text-center">
			<?php if ($item->checked_out) : ?>
				<?php echo HTMLHelper::_('easyshop.gridCheckedOut', $i, $item->editor, $item->checked_out_time, $vars['prefix'], $canCheckin); ?>
			<?php else: ?>
				<?php echo HTMLHelper::_('easyshop.gridPublished', $item->state, $i, $vars['prefix'], $canChange, 'cb'); ?>
			<?php endif; ?>
        </td>
        <td>
			<?php if ($coreEdit || $coreEditOwn) : ?>
                <a href="<?php echo $this->getItemLink($item->id); ?>"
                   title="<?php echo JText::_('JACTION_EDIT'); ?>" uk-tooltip>
					<?php echo $this->escape($item->name); ?></a>
			<?php else : ?>
				<?php echo $this->escape($item->name); ?>
			<?php endif; ?>
        </td>
        <td class="uk-text-nowrap uk-text-center">
			<?php if ($item->is_default): ?>
                <a class="uk-button uk-button-small" style="color:#EF9624;">
                    <class uk-icon="icon: star"></class>
                </a>
			<?php else: ?>
                <a class="uk-button uk-button-small"
                   href="javascript:void(0);"
                   onclick="return listItemTask('cb<?php echo $i; ?>','<?php echo $vars['prefix']; ?>setDefault')"
                   title="" data-original-title="<?php echo JText::_('COM_EASYSHOP_SET_AS_DEFAULT'); ?>">
                    <class uk-icon="icon: star"></class>
                </a>
			<?php endif; ?>
        </td>
        <td class="uk-text-nowrap uk-text-center">
			<?php echo $this->escape($item->code); ?>
        </td>
        <td class="uk-text-nowrap uk-text-center">
			<?php echo $this->escape($item->symbol); ?>
        </td>
        <td class="uk-text-nowrap uk-text-center uk-visible@m">
			<?php echo $item->rate; ?>
        </td>
        <td class="uk-text-nowrap uk-text-center uk-visible@m">
			<?php echo $item->id; ?>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>

<?php echo $this->getFormLayout('foot'); ?>
