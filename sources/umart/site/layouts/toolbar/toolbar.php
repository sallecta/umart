<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\Toolbar\Toolbar;

defined('_JEXEC') or die;
$toolbar = Toolbar::getInstance();
$items   = $toolbar->getItems();
?>
<div id="es-j-toolbar" class="uk-margin uk-padding-small uk-background-muted">
	<?php

	if (!empty($items))
	{
		foreach ($items as $item)
		{
			echo $toolbar->renderButton($item);
		}
	}

	?>
</div>