<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

echo $this->getFormLayout('head');
?>
<div class="uk-form-horizontal" data-zone-group>
	<?php echo $this->getFormLayout('general'); ?>

	<?php echo $this->getFormLayout('params'); ?>
	<?php echo HTMLHelper::_('umartui.renderTab'); ?>
</div>
<?php echo $this->getFormLayout('foot'); ?>
