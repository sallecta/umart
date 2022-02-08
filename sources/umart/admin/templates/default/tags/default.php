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

echo $this->getFormLayout('head');
$vars          = $this->get('layout.storage');
$user          = $vars['user'];
$userId        = (int) $user->get()->id;
$this->columns = 5;
$context       = $this->escape($this->state->get('filter.context'));

?>
<thead>
<tr>
    <th width="1%" class="nowrap center uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $vars['listDirn'], $vars['listOrder'], null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
    </th>
    <th width="1%" class="center">
		<?php echo HTMLHelper::_('easyshop.gridCheckall'); ?>
    </th>
    <th width="1%" style="min-width:55px" class="nowrap center">
		<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th>
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_NAME', 'a.name', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th width="1%">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_ID', 'a.id', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
</tr>
</thead>
<tbody>
<?php foreach ($this->items as $i => $item):
	$canCreate = $user->core('create');
	$canEdit = $user->core('edit');
	$canCheckin = $user->core('manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
	$canEditOwn = $user->core('edit.own') && $item->created_by == $userId;
	$canChange = $user->core('edit.state') && $canCheckin;
	?>
    <tr>
        <td class="order nowrap center uk-visible@m">
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
				<span class="icon-menu"></span>
			</span>
			<?php if ($canChange && $vars['saveOrder']) : ?>
                <input type="text" style="display:none" name="order[]" size="5"
                       value="<?php echo $item->ordering; ?>"
                       class="width-20 text-area-order "/>
			<?php endif; ?>
        </td>
        <td class="uk-text-center">
			<?php echo HTMLHelper::_('easyshop.gridId', $i, $item->id); ?>
        </td>
        <td class="center">
			<?php echo HTMLHelper::_('easyshop.gridPublished', $item->state, $i, $vars['prefix'], $canChange, 'cb'); ?>
        </td>
        <td class="has-context">
            <div class="pull-left break-word">
				<?php if ($item->checked_out) : ?>
					<?php echo HTMLHelper::_('easyshop.gridCheckedOut', $i, $item->editor, $item->checked_out_time, $vars['prefix'], $canCheckin); ?>
				<?php endif; ?>
				<?php if ($canEdit || $canEditOwn) : ?>
                    <a class="hasTooltip"
                       href="<?php echo JRoute::_('index.php?option=com_easyshop&task=tag.edit&id=' . $item->id . '&context=' . $context, false); ?>"
                       title="<?php echo Text::_('JACTION_EDIT'); ?>">
						<?php echo $this->escape($item->name); ?></a>
				<?php else : ?>
					<?php echo $this->escape($item->name); ?>
				<?php endif; ?>
                <span class="small break-word">
						<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
					</span>
            </div>
        </td>
        <td>
			<?php echo $item->id; ?>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
<?php echo $this->getFormLayout('foot'); ?>
