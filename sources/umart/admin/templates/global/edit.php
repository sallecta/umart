<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

echo $this->getFormLayout('head');
?>
<div class="uk-form-horizontal" data-zone-group>
	<?php echo $this->getFormLayout('general'); ?>

	<?php echo $this->getFormLayout('params'); ?>
	<?php echo HTMLHelper::_('ukui.renderTab'); ?>
</div>
<?php echo $this->getFormLayout('foot'); ?>
