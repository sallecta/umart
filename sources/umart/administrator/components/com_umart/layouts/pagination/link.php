<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
$link     = $displayData->link;
$text     = $displayData->text;
$active   = $displayData->active ? ' class="umartui_active"' : '';
$disabled = empty($link) ? ' class="uk-disabled"' : '';
$link     = str_replace(['&type=raw', '&amp;type=raw'], '', $link);
$link     = str_replace('limitstart=', 'start=', $link);

if (!preg_match('/start=[0-9]+$/', $link))
{
	$link .= (strpos($link, '?') === false ? '?' : '&') . 'start=0';
}
?>
<li<?php echo $active; ?><?php echo $disabled; ?>>
	<?php if ($displayData->active || $disabled): ?>
        <span><?php echo $text; ?></span>
	<?php else: ?>
        <a href="<?php echo $link; ?>">
            <span><?php echo $text; ?></span>
        </a>
	<?php endif; ?>
</li>
