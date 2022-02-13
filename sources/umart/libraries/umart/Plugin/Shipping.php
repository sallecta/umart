<?php
/**
 
 
 
 
 
 */

namespace Umart\Plugin;

defined('_JEXEC') or die;

abstract class Shipping extends PluginLegacy
{
	abstract public function onUmartShippingRegister();
}
