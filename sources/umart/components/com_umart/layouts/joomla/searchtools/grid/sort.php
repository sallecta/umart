<?php
/**
 
 
 
 
 
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;

/**
 * @var array $displayData
 */

$data     = $displayData;
$title    = htmlspecialchars(Text::_($data->tip ?: $data->title));
$iconMaps = [
	'icon-arrow-up-3'   => '<span uk-icon="icon: chevron-up"></span>&nbsp;',
	'icon-arrow-down-3' => '<span uk-icon="icon: chevron-down"></span>&nbsp;',
];

?>
<a href="#" onclick="return false;" class="js-stools-column-order"
   data-order="<?php echo $data->order; ?>" data-direction="<?php echo strtoupper($data->direction); ?>"
   data-name="<?php echo Text::_($data->title); ?>"
   title="<?php echo $title; ?>"
   uk-tooltip="<?php echo htmlspecialchars(Text::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN')); ?>">

	<?php if (!empty($data->icon)) : ?>

		<?php if (isset($iconMaps[$data->icon])): ?>
			<?php echo $iconMaps[$data->icon]; ?>
		<?php else: ?>
            <span class="<?php echo $data->icon; ?>"></span>
		<?php endif; ?>

	<?php endif; ?>

	<?php if (!empty($data->title)) : ?>
		<?php echo Text::_($data->title); ?>
	<?php endif; ?>

	<?php if ($data->order == $data->selected) : ?>
		<?php if (isset($iconMaps[$data->orderIcon])): ?>
			<?php echo $iconMaps[$data->orderIcon]; ?>
		<?php else: ?>
            <span class="<?php echo $data->orderIcon; ?>"></span>
		<?php endif; ?>
	<?php endif; ?>
</a>
