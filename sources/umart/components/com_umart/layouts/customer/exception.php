<?php
/**
 
 
 
 
 
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
    <a href="<?php echo Route::_('index.php?option=com_umart&task=customer.page&page=account', false); ?>"
       class="uk-button uk-button-primary">
        <span uk-icon="icon: reply"></span>
		<?php echo Text::_('COM_UMART_BACK'); ?>
    </a>
</div>
