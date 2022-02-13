<?php

use Umart\Classes\Addon;
use Joomla\CMS\Component\ComponentHelper;

defined('_JEXEC') or die;

if (ComponentHelper::isEnabled('com_umart'))
{
	$addOn = umart(Addon::class);
	echo '<div class="es-scope uk-scope">' . $addOn->getBuffer($params->get('addon', ''), '') . '</div>';
}
