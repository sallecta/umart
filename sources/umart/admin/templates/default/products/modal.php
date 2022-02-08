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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

echo $this->getFormLayout('head');
$vars          = $this->get('layout.storage');
$user          = $vars['user'];
$userId        = (int) $user->get()->id;
$currency      = easyshop(Currency::class)->getDefault();
$this->columns = 8;
easyshop('doc')->addStyleDeclaration('#es-body{float:none; width: 100%}');

//@since 1.1.6
$renderer = $this->getRenderer();
?>
<thead>
<tr>
    <th class="uk-table-shrink uk-text-nowrap uk-text-center">
		<?php echo HTMLHelper::_('easyshop.gridCheckall'); ?>
    </th>
    <th class="uk-table-shrink uk-text-nowrap uk-text-center">
		<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-table-expand@m">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_NAME', 'a.name', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-table-shrink uk-text-nowrap uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_CATEGORY', 'category_name', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-table-shrink uk-text-nowrap uk-text-center">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_PRODUCT_PRICE', 'a.price', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-table-shrink uk-text-center uk-text-nowrap uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_HITS', 'a.hits', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-table-shrink uk-text-nowrap uk-text-center uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_EASYSHOP_ID', 'a.id', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
</tr>
</thead>
<tbody>
<?php foreach ($this->items as $i => $item):
	$image = !empty($item->images[0]) ? $item->images[0] : '';
	$title = htmlspecialchars($item->name, ENT_COMPAT, 'UTF-8');
	?>
    <tr>
        <td class="uk-text-nowrap uk-text-center">
			<?php echo HTMLHelper::_('easyshop.gridId', $i, $item->id); ?>
        </td>
        <td class="uk-text-nowrap uk-text-center">
			<?php if ($item->state == 1): ?>
                <i class="fa fa-check-circle uk-text-success"></i>
			<?php elseif ($item->state == -2): ?>
                <i class="fa fa-trash"></i>
			<?php else: ?>
                <i class="fa fa-times-circle uk-text-danger"></i>
			<?php endif; ?>
        </td>
        <td>
            <div class="uk-grid-small" uk-grid>
                <div class="uk-width-auto" uk-lightbox>
					<?php if (!empty($image)): ?>
                        <a href="<?php echo $image->large; ?>" type="image" title="<?php echo $title; ?>"
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
                        <a href="#" type="image"
                           uk-tooltip class="uk-cover-container"
                           title="<?php echo $title; ?>">
                            <img src="<?php echo ES_MEDIA_URL . '/images/no-image.png'; ?>"
                                 class="uk-preserve-width" width="32"/>
                        </a>
					<?php endif; ?>
                </div>
                <div class="uk-width-expand">
                    <a href="#"
                       data-product-id="<?php echo (int) $item->id; ?>"
                       class="uk-link-reset uk-text-meta"
						<?php echo !empty($item->summary) ? 'title="' . $this->escape($item->summary) . '" uk-tooltip' : ''; ?>>
						<?php echo $this->escape($item->name); ?>
                    </a>
                    <small>
						<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
						<?php if (!empty($item->sku)): ?>
                            <i class="fa fa-barcode"></i>
							<?php echo $item->sku; ?>
						<?php endif; ?>

						<?php if ($item->vendor_id && !$item->approved): ?>
                            <span class="uk-text-warning">
                                <?php echo Text::_('COM_EASYSHOP_PRODUCT_NOT_APPROVED'); ?>
                            </span>
						<?php endif; ?>
                    </small>
					<?php if ($item->expired): ?>
                        <span class="uk-label uk-label-warning">
                            <?php echo Text::_('COM_EASYSHOP_PRODUCT_EXPIRED'); ?>
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
