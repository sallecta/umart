<?php

/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\User;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

if (!plg_sytem_umart_main(User::class)->core('admin'))
{
	plg_sytem_umart_main('app')->redirect(Route::_('index.php?option=com_umart&view=languages', false));
}

HTMLHelper::_('script', 'system/core.js', false, true);
$action = Route::_('index.php?option=com_umart&view=language&layout=edit&file=' . urlencode($this->file), false);
?>
<form action="<?php echo $action; ?>" name="adminForm" id="adminForm" method="post">
    <div id="es-language-edit">
		<?php echo $this->form->getInput('file_contents'); ?>
    </div>
    <input name="task" type="hidden" value=""/>
    <input name="return" type="hidden" value="<?php echo base64_encode($action); ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
