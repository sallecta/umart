<?php
/**
 * @version        1.1.4
 * @package        plg_system_ukui
 * @author         JoomTech Team - http://www.joomtech.net/
 * @copyright      Copyright (C) 2015 - 2020 www.joomtech.net All Rights Reserved
 * @license        http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

/**
 * @var array $displayData
 */
extract($displayData);
$switcherId = isset($params['switcherId']) ? $params['switcherId'] : 'switcher-' . uniqid('switcher-');
$tabsTitle  = $tabsContent = '';

foreach ($items as $i => $item)
{
	$active      = $i ? '' : ' class="uk-active"';
	$tAnimation  = empty($item['params']['titleAnimation']) ? '' : ' uk-scrollspy="cls:uk-animation-' . $item['params']['titleAnimation'] . '"';
	$dAnimation  = empty($item['params']['descriptionAnimation']) ? '' : ' uk-scrollspy="cls:uk-animation-' . $item['params']['descriptionAnimation'] . '"';
	$tabsTitle   .= '<li' . $active . '><a href="#"' . $tAnimation . '>' . (isset($item['params']['icon']) ? HTMLHelper::_('ukui.icon', $item['params']['icon']) : '') . ' ' . $item['title'] . '</a></li>';
	$tabsContent .= '<li' . $active . '><div' . $dAnimation . '>' . $item['content'] . '</div></li>';
}

?>
<div class="ukui-tab tab-default">
    <ul uk-tab="connect: #<?php echo $switcherId; ?>">
		<?php echo $tabsTitle; ?>
    </ul>
    <ul id="<?php echo $switcherId; ?>" class="uk-switcher">
		<?php echo $tabsContent; ?>
    </ul>
</div>
