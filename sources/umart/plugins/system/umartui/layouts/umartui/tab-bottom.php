<?php
/**
 * @version        1.1.4
 * @package        plg_system_umartukui
 * @author         JoomTech Team - http://github.com/sallecta/umart/
 * @copyright      Copyright (C) 2015 - 2020 github.com/sallecta/umart All Rights Reserved
 * @license        http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

/**
 * @var array $displayData
 */
extract($displayData);
$switcherId = isset($params['switcherId']) ? $params['switcherId'] : uniqid('switcher-');
$tabsTitle  = $tabsContent = '';

foreach ($items as $i => $item)
{
	$active      = $i ? '' : ' class="umartui_active"';
	$tAnimation  = empty($item['params']['titleAnimation']) ? '' : ' uk-scrollspy="cls:uk-animation-' . $item['params']['titleAnimation'] . '"';
	$dAnimation  = empty($item['params']['descriptionAnimation']) ? '' : ' uk-scrollspy="cls:uk-animation-' . $item['params']['descriptionAnimation'] . '"';
	$tabsTitle   .= '<li' . $active . '><a href="#"' . $tAnimation . '>' . (isset($item['params']['icon']) ? HTMLHelper::_('umartui.icon', $item['params']['icon']) : '') . ' ' . $item['title'] . '</a></li>';
	$tabsContent .= '<li' . $active . '><div' . $dAnimation . '>' . $item['content'] . '</div></li>';
}

?>
<div class="umartui-tab tab-bottom">
    <ul id="<?php echo $switcherId; ?>" class="uk-switcher">
		<?php echo $tabsContent; ?>
    </ul>
    <ul class="uk-tab-bottom" uk-tab="connect: #<?php echo $switcherId; ?>">
		<?php echo $tabsTitle; ?>
    </ul>
</div>
