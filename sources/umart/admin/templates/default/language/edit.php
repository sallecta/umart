<?php

/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\User;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

if (!easyshop(User::class)->core('admin'))
{
	easyshop('app')->redirect(Route::_('index.php?option=com_easyshop&view=languages', false));
}

HTMLHelper::_('script', 'system/core.js', false, true);
$action = Route::_('index.php?option=com_easyshop&view=language&layout=edit&file=' . urlencode($this->file), false);
?>
<form action="<?php echo $action; ?>" name="adminForm" id="adminForm" method="post">
    <div id="es-language-edit">
		<?php echo $this->form->getInput('file_contents'); ?>
    </div>
    <input name="task" type="hidden" value=""/>
    <input name="return" type="hidden" value="<?php echo base64_encode($action); ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
