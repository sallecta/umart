<?php

/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\Media;
use Umart\Classes\Utility;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

echo $this->getFormLayout('head');
$vars          = $this->get('layout.storage');
$user          = $vars['user'];
$loggedUser    = $user->get();
$userId        = (int) $loggedUser->id;
$this->columns = 8;
$type          = plg_sytem_umart_main('app')->input->getWord('filter_user_type');
$utility       = plg_sytem_umart_main(Utility::class);
$coreCreate    = $user->core('create');
$coreEdit      = $user->core('edit');
$coreEditState = $user->core('edit.state');
$coreEditOwn   = $user->core('edit.own');
$coreCheckIn   = $user->core('manage');
$mediaClass    = plg_sytem_umart_main(Media::class);
$rootUrl       = Uri::root(true);

?>
<thead>
<tr>
    <th class="uk-table-shrink uk-text-center uk-text-nowrap">
		<?php echo HTMLHelper::_('umart.gridCheckall'); ?>
    </th>
    <th class="uk-table-shrink uk-text-center uk-text-nowrap">
		<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-table-shrink uk-text-center uk-text-nowrap">
		<?php echo Text::_('COM_UMART_AVATAR'); ?>
    </th>
    <th class="uk-table-shrink uk-text-nowrap">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_UMART_USERNAME', 'uuu.username', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th>
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_UMART_NAME', 'uuu.name', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-table-shrink uk-text-nowrap">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_UMART_ORDERS', 'totalOrders', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="umartui_width-small@s uk-text-nowrap">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_UMART_CREATED_DATE', 'a.created_date', $vars['listDirn'], $vars['listOrder']); ?>
    </th>

    <th class="uk-table-shrink uk-text-center uk-text-nowrap uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_UMART_ID', 'a.id', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
</tr>
</thead>
<tbody>
<?php foreach ($this->items as $i => $item):
	$canCheckin = $coreCheckIn || $item->checked_out == $userId || $item->checked_out == 0;
	$canEditOwn = $coreEditOwn && $item->created_by == $userId;
	$canChange = $coreEditState && $canCheckin;

	if ($item->avatar)
	{
		$avatar = $rootUrl . '/' . $mediaClass->getResizeImageBasePath($item->avatar, '30x30', true);
	}
	else
	{
		$avatar = UMART_MEDIA_URL . '/images/no-avatar.jpg';
	}

	?>
    <tr>
        <td class="uk-text-center uk-text-nowrap">
			<?php echo HTMLHelper::_('umart.gridId', $i, $item->id); ?>
        </td>
        <td class="uk-text-nowrap uk-text-center">
			<?php if ($item->checked_out) : ?>
				<?php echo HTMLHelper::_('umart.gridCheckedOut', $i, $item->editor, $item->checked_out_time, $vars['prefix'], $canCheckin); ?>
			<?php else: ?>
				<?php echo HTMLHelper::_('umart.gridPublished', $item->state, $i, $vars['prefix'], $canChange, 'cb'); ?>
			<?php endif; ?>
        </td>
        <td class="uk-text-center">
            <img class="uk-preserve-width uk-border-circle" src="<?php echo $avatar; ?>" width="30"
                 alt="<?php echo $this->escape($item->name); ?>"/>
        </td>
        <td class="uk-text-nowrap">
			<?php echo $this->escape($item->username); ?>
        </td>
        <td class="uk-table-link">
			<?php if ($coreEdit || $coreEditOwn) : ?>
                <a href="<?php echo $this->getItemLink($item->id, ['filter_user_type' => $type]); ?>"
                   title="<?php echo Text::_('JACTION_EDIT'); ?>" uk-tooltip>
					<?php echo $this->escape($item->name); ?></a>
			<?php else : ?>
				<?php echo $this->escape($item->name); ?>
			<?php endif; ?>
        </td>
        <td class="uk-text-nowrap">
			<?php echo HTMLHelper::_('umart.icon', 'es-icon-chart-bars'); ?>
            <sup><?php echo (int) $item->totalOrders; ?></sup>
        </td>
        <td class="uk-text-nowrap uk-visible@m">
			<?php echo $utility->displayDate($item->created_date); ?>
        </td>
        <td class="uk-text-nowrap uk-text-center uk-visible@m">
			<?php echo $item->id; ?>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
<?php echo $this->getFormLayout('foot'); ?>
