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
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

echo $this->getFormLayout('head');
$vars          = $this->get('layout.storage');
$user          = $vars['user'];
$userId        = (int) $user->get()->id;
$language      = Multilanguage::isEnabled();
$this->columns = $language ? 8 : 7;
$type          = $this->state->get('filter.type');
$method        = Text::_('COM_EASYSHOP_' . strtoupper($type) . '_METHOD');
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
    <th class="uk-table-shrink uk-text-center uk-text-nowrap uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_TYPE', 'e.folder', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
	<?php if ($language): ?>
        <th class="uk-table-shrink uk-text-center uk-text-nowrap uk-visible@m">
			<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_LANGUAGE', 'a.language', $vars['listDirn'], $vars['listOrder']); ?>
        </th>
	<?php endif; ?>
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
                <a href="<?php echo Route::_('index.php?option=com_easyshop&task=method.edit&filter_type=' . $type . '&method_id=' . $item->method_id . '&id=' . $item->id, false); ?>"
                   title="<?php echo Text::_('JACTION_EDIT'); ?>" uk-tooltip>
					<?php echo $item->name; ?>
                </a>
			<?php else : ?>
				<?php echo $item->name; ?>
			<?php endif; ?>
        </td>
        <td class="uk-text-nowrap uk-text-center">
			<?php if ($item->is_default): ?>
                <a class="uk-button uk-button-small" style="color:#EF9624;">
                    <class uk-icon="icon: star"></class>
                </a>
			<?php else: ?>
                <a
                        class="uk-button uk-button-small"
                        href="javascript:void(0);"
                        onclick="return listItemTask('cb<?php echo $i; ?>','<?php echo $vars['prefix']; ?>setDefault')"
                        title="" data-original-title="<?php echo Text::_('COM_EASYSHOP_SET_AS_DEFAULT'); ?>">
                    <span uk-icon="icon: star"></span>
                </a>
			<?php endif; ?>
        </td>
        <td class="uk-text-nowrap uk-text-center uk-visible@m">
			<?php echo $method; ?>
        </td>
		<?php if ($language): ?>
            <td class="uk-text-nowrap uk-text-center uk-visible@m">
				<?php if ($item->language == '*'): ?>
					<?php echo Text::alt('JALL', 'language'); ?>
				<?php else: ?>
					<?php echo $item->language_title ? HTMLHelper::_('image', 'mod_languages/' . $item->language_image . '.gif', $item->language_title, ['title' => $item->language_title], true) . '&nbsp;' . $this->escape($item->language_title) : Text::_('JUNDEFINED'); ?>
				<?php endif; ?>
            </td>
		<?php endif; ?>
        <td class="uk-text-nowrap uk-text-center uk-visible@m">
			<?php echo $item->id; ?>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
<?php echo $this->getFormLayout('foot'); ?>
