<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use ES\Classes\Media;
use ES\Classes\Utility;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

echo $this->getFormLayout('head');
$vars          = $this->get('layout.storage');
$user          = $vars['user'];
$loggedUser    = $user->get();
$userId        = (int) $loggedUser->id;
$this->columns = 7;
$vendor        = easyshop('app')->input->getInt('filter_vendor');
$utility       = easyshop(Utility::class);
easyshop('doc')->addStyleDeclaration('#es-body{float:none; width: 100%}');
$mediaClass = easyshop(Media::class);
$rootUrl    = Uri::root(true);
?>
<thead>
<tr>
    <th class="uk-table-shrink uk-text-center uk-text-nowrap">
		<?php echo HTMLHelper::_('easyshop.gridCheckall'); ?>
    </th>
    <th class="uk-table-shrink uk-text-center uk-text-nowrap">
		<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-table-shrink uk-text-center uk-text-nowrap">
		<?php echo Text::_('COM_EASYSHOP_AVATAR'); ?>
    </th>
    <th>
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_NAME', 'uuu.name', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-width-small@s uk-text-nowrap">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_USERNAME', 'uuu.username', $vars['listDirn'], $vars['listOrder']); ?>
    </th>

    <th class="uk-width-small@s uk-text-nowrap">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_CREATED_DATE', 'a.created_date', $vars['listDirn'], $vars['listOrder']); ?>
    </th>

    <th class="uk-table-shrink uk-text-center uk-text-nowrap uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_ID', 'a.id', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
</tr>
</thead>
<tbody>
<?php foreach ($this->items as $i => $item):

	if ($item->avatar)
	{
		$avatar = $rootUrl . '/' . $mediaClass->getResizeImageBasePath($item->avatar, '30x30', true);
	}
	else
	{
		$avatar = ES_MEDIA_URL . '/images/no-avatar.jpg';
	}

	?>
    <tr>
        <td class="uk-text-center uk-text-nowrap">
			<?php echo HTMLHelper::_('easyshop.gridId', $i, $item->id); ?>
        </td>
        <td class="uk-text-center uk-text-nowrap">
			<?php if ($item->state == 1): ?>
                <i class="fa fa-check-circle uk-text-success"></i>
			<?php elseif ($item->state == -2): ?>
                <i class="fa fa-trash"></i>
			<?php else: ?>
                <i class="fa fa-times-circle uk-text-danger"></i>
			<?php endif; ?>
        </td>
        <td class="es-user-avatar uk-text-center">
            <img class="uk-preserve-width uk-border-circle" src="<?php echo $avatar; ?>" width="30"
                 alt="<?php echo $this->escape($item->name); ?>"/>
        </td>
        <td>
            <a href="#" data-user-id="<?php echo (int) $item->id; ?>">
				<?php echo $this->escape($item->name); ?>
            </a>
        </td>
        <td class="uk-text-nowrap">
			<?php echo $this->escape($item->username); ?>
        </td>
        <td class="uk-text-nowrap uk-visible@m">
			<?php echo $utility->displayDate($item->created_date); ?>
        </td>
        <td class="uk-text-center uk-text-nowrap uk-visible@m">
			<?php echo $item->id; ?>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>

<?php echo $this->getFormLayout('foot'); ?>
