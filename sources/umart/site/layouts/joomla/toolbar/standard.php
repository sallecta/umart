<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;
HTMLHelper::_('behavior.core');

/**
 * @var array $displayData
 */

$doTask    = $displayData['doTask'];
$text      = $displayData['text'];
$search    = ['btn', 'btn-'];
$replace   = ['uk-button', 'uk-button-'];
$btnClass  = str_replace($search, $replace, $displayData['btnClass']);
$iconClass = str_replace('icon-white', '', $displayData['class']);

if (strpos($btnClass, 'uk-button-primary') === false
	&& strpos($btnClass, 'uk-button-success') === false
	&& strpos($btnClass, 'uk-button-info') === false
	&& strpos($btnClass, 'uk-button-danger') === false
)
{
	$btnClass .= ' uk-button-default';
}

?>

<button onclick="<?php echo $doTask; ?>" class="<?php echo $btnClass; ?>">
    <span class="<?php echo trim($iconClass); ?>" aria-hidden="true"></span>
	<?php echo $text; ?>
</button>
