<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\Currency;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

echo $this->getFormLayout('head');

$vars          = $this->get('layout.storage');
$user          = $vars['user'];
$userId        = (int) $user->get()->id;
$currency      = plg_sytem_umart_main(Currency::class)->getDefault();
$this->columns = 8;
$coreCreate    = $user->core('create');
$coreEdit      = $user->core('edit');
$coreEditState = $user->core('edit.state');
$coreEditOwn   = $user->core('edit.own');
$coreCheckIn   = $user->core('manage');

//@since 1.1.6
$renderer = $this->getRenderer();
?>
<thead>
<tr>
    <th class="uk-table-shrink uk-text-center uk-text-nowrap uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $vars['listDirn'], $vars['listOrder'], null, 'asc',
			'JGRID_HEADING_ORDERING', 'fa fa-sort'); ?>
    </th>
    <th class="uk-table-shrink uk-text-center uk-text-nowrap">
		<?php echo HTMLHelper::_('umart.gridCheckall'); ?>
    </th>
    <th class="uk-table-shrink uk-text-center uk-text-nowrap">
		<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-table-expand@m">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_UMART_NAME', 'a.name', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-table-shrink uk-text-nowrap uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_UMART_CATEGORY', 'category_name', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-table-shrink uk-text-nowrap">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_UMART_PRODUCT_PRICE', 'a.price', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-table-shrink uk-text-center uk-text-nowrap uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_UMART_HITS', 'a.hits', $vars['listDirn'], $vars['listOrder']); ?>
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
	$image = !empty($item->images[0]) ? $item->images[0] : null;
	?>
    <tr sortable-group-id="<?php echo $item->category_id; ?>">
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

			$title = htmlspecialchars($item->name, ENT_COMPAT, 'UTF-8');
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
            <div class="uk-grid-small" uk-grid>
                <div class="umartui_width-auto" uk-lightbox>
					<?php if ($image): ?>
                        <a href="<?php echo $image->image; ?>" type="image" title="<?php echo $title; ?>"
                           style="display: block; width: 32px;" uk-tooltip>
							<?php echo $renderer->render('media.image', [
								'image'      => $image,
								'size'       => 'tiny',
								'attributes' => [
									'alt'   => $item->name,
									'class' => 'imageHandled uk-preserve-width',
									'width' => 32,
								],
							]); ?>
                        </a>
					<?php else: ?>
                        <a href="#" type="image" uk-tooltip class="uk-cover-container"
                           title="<?php echo $title; ?>">
                            <img src="<?php echo UMART_MEDIA_URL . '/images/no-image.png'; ?>"
                                 class="uk-preserve-width" width="32"/>
                        </a>
					<?php endif; ?>
                </div>
                <div class="umartui_width-expand">
					<?php if ($coreEdit || $coreEditOwn) : ?>
                        <a href="<?php echo $this->getItemLink($item->id); ?>"
							<?php echo !empty($item->summary) ? 'title="' . $this->escape($item->summary) . '" uk-tooltip' : ''; ?>>
							<?php echo $this->escape($item->name); ?>
                        </a>
					<?php else : ?>
                        <div style="display: inline-block; padding-left: 5px">
							<?php echo $this->escape($item->name); ?>
                        </div>
					<?php endif; ?>
                    <small>
						<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
						<?php if (!empty($item->sku)): ?>
                            <i class="fa fa-barcode"></i>
							<?php echo $item->sku; ?>
						<?php endif; ?>

						<?php if ($item->vendor_id && !$item->approved): ?>
                            <span class="uk-text-warning">
                                <?php echo Text::_('COM_UMART_PRODUCT_NOT_APPROVED'); ?>
                            </span>
						<?php endif; ?>
                    </small>
					<?php if ($item->expired): ?>
                        <span class="uk-label uk-label-warning">
                            <?php echo Text::_('COM_UMART_PRODUCT_EXPIRED'); ?>
                        </span>
					<?php endif; ?>
                </div>
            </div>
        </td>
        <td class="uk-text-nowrap uk-visible@m">
			<?php echo $item->category->path; ?>
        </td>
        <td class="uk-text-nowrap uk-text-center">
			<?php echo $currency->toFormat($item->price); ?>
        </td>
        <td class="uk-text-nowrap uk-text-center uk-visible@m">
			<?php echo $item->hits; ?>
        </td>
        <td class="uk-text-nowrap uk-text-center uk-visible@m">
		    <?php echo $item->id; ?>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>

<?php echo $this->getFormLayout('foot'); ?>
