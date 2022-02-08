<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

?>
<div id="mod-easyshop-tracking<?php echo $module->id; ?>"
     class="es-scope uk-scope mod-easyshop-tracking<?php echo $params->get('moduleclass_sfx'); ?>">
	<?php echo easyshop('renderer')->render('customer.tracking', [
		'return' => base64_encode(JRoute::_(EasyshopHelperRoute::getCustomerRoute(), false)),
	]); ?>
</div>
