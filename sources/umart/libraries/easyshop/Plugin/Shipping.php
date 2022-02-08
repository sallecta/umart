<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ES\Plugin;

defined('_JEXEC') or die;

abstract class Shipping extends PluginLegacy
{
	abstract public function onEasyshopShippingRegister();
}
