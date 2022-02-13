<?php

/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\Currency;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var Currency $currency */
echo $this->getFormLayout('head');
$vars          = $this->get('layout.storage');
$user          = $vars['user'];
$userId        = (int) $user->get()->id;
$this->columns = 6;
$currency      = plg_sytem_umart_main(Currency::class)->getDefault();
$coreCreate    = $user->core('create');
$coreEdit      = $user->core('edit');
$coreEditState = $user->core('edit.state');
$coreEditOwn   = $user->core('edit.own');
$coreCheckIn   = $user->core('manage');
?>
<thead>
<tr>
    <th class="uk-table-shrink uk-text-nowrap uk-text-center uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $vars['listDirn'], $vars['listOrder'], null, 'asc',
			'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
    </th>

    <th class="uk-table-shrink uk-text-nowrap uk-text-center">
		<?php echo HTMLHelper::_('umart.gridCheckall'); ?>
    </th>

    <th class="uk-table-shrink uk-text-nowrap uk-text-center">
		<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $vars['listDirn'], $vars['listOrder']); ?>
    </th>

    <th>
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_UMART_NAME', 'a.name', $vars['listDirn'], $vars['listOrder']); ?>
    </th>

    <th class="uk-table-expand@m uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_UMART_SEND_SUBJECT', 'a.send_subject', $vars['listDirn'], $vars['listOrder']); ?>
    </th>

    <th class="uk-table-shrink uk-text-nowrap uk-text-center uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_UMART_ID', 'a.id', $vars['listDirn'], $vars['listOrder']); ?>
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
                           class="width-20 text-area-order"/>
				<?php endif; ?>
        </td>
        <td class="uk-text-nowrap uk-text-center">
			<?php echo HTMLHelper::_('umart.gridId', $i, $item->id); ?>
        </td>
        <td class="uk-text-nowrap uk-text-center">
			<?php if ($item->checked_out) : ?>
				<?php echo HTMLHelper::_('umart.gridCheckedOut', $i, $item->editor, $item->checked_out_time, $vars['prefix'], $canCheckin); ?>
			<?php else: ?>
				<?php echo HTMLHelper::_('umart.gridPublished', $item->state, $i, $vars['prefix'], $canChange, 'cb'); ?>
			<?php endif; ?>
        </td>
        <td>
			<?php if ($coreEdit || $coreEditOwn) : ?>
                <a href="<?php echo $this->getItemLink($item->id); ?>"
                   title="<?php echo Text::_('JACTION_EDIT'); ?>" uk-tooltip>
					<?php echo $this->escape($item->name); ?></a>
			<?php else : ?>
				<?php echo $this->escape($item->name); ?>
			<?php endif; ?>
        </td>
        <td>
			<?php echo $this->escape($item->send_subject); ?>
        </td>
        <td class="uk-text-nowrap uk-text-center uk-visible@m">
			<?php echo $item->id; ?>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>

<?php echo $this->getFormLayout('foot'); ?>
