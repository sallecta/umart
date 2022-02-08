<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/**
 * @var array $displayData
 */
echo $displayData['navbar'];

?>

<div class="uk-alert uk-alert-danger">
	<?php echo $displayData['e']->getMessage(); ?>
    <a href="<?php echo Route::_('index.php?option=com_easyshop&task=customer.page&page=account', false); ?>"
       class="uk-button uk-button-primary">
        <span uk-icon="icon: reply"></span>
		<?php echo Text::_('COM_EASYSHOP_BACK'); ?>
    </a>
</div>
