<?php
/**
 
 
 
 
 
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
