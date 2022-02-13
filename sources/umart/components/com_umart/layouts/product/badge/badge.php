<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

/**
 * @var array $displayData
 */
$classes = 'uk-position-' . $displayData['badge_position'] . ' uk-padding-small uk-position-z-index';

if (!empty($displayData['badge_overlay']))
{
	$classes .= ' uk-overlay uk-overlay-' . $displayData['badge_overlay'];
}

if (!empty($displayData['badge_visibility']))
{
	$classes .= ' uk-' . $displayData['badge_visibility'];
}
?>
<div class="<?php echo $classes; ?>">
	<?php if ($displayData['badge'] == 'image'): ?>
        <!-- B/C for version 1.1.2 -->
		<?php if (is_file(UMART_MEDIA . '/' . $displayData['value'])): ?>
            <img src="<?php echo UMART_MEDIA_URL . '/' . $displayData['value']; ?>" alt=""/>
		<?php else: ?>
            <img src="<?php echo Uri::root(true) . '/' . $displayData['value']; ?>" alt=""/>
		<?php endif; ?>
	<?php else: ?>
        <span class="uk-badge">
            <?php echo $displayData['value']; ?>
        </span>
	<?php endif; ?>
</div>