<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;
$link     = $displayData->link;
$text     = $displayData->text;
$active   = $displayData->active ? ' class="uk-active"' : '';
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
