<?php

/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\Currency;
use ES\Classes\Utility;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var Currency $currency */
echo $this->getFormLayout('head');
$vars          = $this->get('layout.storage');
$user          = $vars['user'];
$userId        = (int) $user->get()->id;
$this->columns = 11;
$currency      = easyshop(Currency::class)->getDefault();
$utility       = easyshop(Utility::class);
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
		<?php echo HTMLHelper::_('easyshop.gridCheckall'); ?>
    </th>
    <th class="uk-table-shrink uk-text-nowrap uk-text-center">
		<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th>
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_NAME', 'a.name', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-table-shrink uk-text-nowrap uk-text-center uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_TYPE', 'a.type', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-table-shrink uk-text-nowrap uk-text-center">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_CODE', 'a.coupon_code', $vars['listDirn'],
			$vars['listOrder']); ?>
    </th>
    <th class="uk-table-shrink uk-text-nowrap uk-text-center">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_FLAT_AMOUNT', 'a.flat', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-table-shrink uk-text-nowrap uk-text-center uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_PERCENTAGE_AMOUNT', 'a.percentage', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-table-shrink uk-text-nowrap uk-text-center uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_START_DATE', 'a.start_date', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-table-shrink uk-text-nowrap uk-text-center uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_END_DATE', 'a.end_date', $vars['listDirn'],
			$vars['listOrder']); ?>
    </th>
    <th class="uk-table-shrink uk-text-nowrap uk-text-center uk-visible@m">
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
        <td class="order uk-table-shrink uk-text-nowrap uk-text-center uk-visible@m">
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
        <td class="uk-text-center uk-text-nowrap">
			<?php echo HTMLHelper::_('easyshop.gridId', $i, $item->id); ?>
        </td>
        <td class="uk-text-center uk-text-nowrap">
			<?php if ($item->checked_out) : ?>
				<?php echo HTMLHelper::_('easyshop.gridCheckedOut', $i, $item->editor, $item->checked_out_time, $vars['prefix'], $canCheckin); ?>
			<?php else: ?>
				<?php echo HTMLHelper::_('easyshop.gridPublished', $item->state, $i, $vars['prefix'], $canChange, 'cb'); ?>
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
        <td class="uk-text-center uk-text-nowrap uk-visible@m">
			<?php switch ((int) $item->type)
			{
				case 0:
					echo Text::_('COM_EASYSHOP_ON_PRODUCTS');
					break;
				case 1:
					echo Text::_('COM_EASYSHOP_ON_COUPON');
					break;
				case 2:
					echo Text::_('COM_EASYSHOP_ON_ORDER');
					break;
			} ?>
        </td>
        <td class="uk-text-center uk-text-nowrap">
			<?php if ($item->type == 1): ?>
                <code><?php echo $this->escape($item->coupon_code); ?></code>
			<?php endif; ?>
        </td>
        <td class="uk-text-center uk-text-nowrap">
			<?php echo $currency->toFormat($item->flat); ?>
        </td>
        <td class="uk-text-center uk-text-nowrap">
			<?php echo $item->percentage . '%'; ?>
        </td>
        <td class="uk-text-center uk-text-nowrap uk-visible@m">
			<?php echo $utility->displayDate($item->start_date); ?>
        </td>
        <td class="uk-text-center uk-text-nowrap uk-visible@m">
			<?php echo $utility->displayDate($item->end_date); ?>
        </td>
        <td class="uk-text-center uk-visible@m">
			<?php echo $item->id; ?>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>

<?php echo $this->getFormLayout('foot'); ?>