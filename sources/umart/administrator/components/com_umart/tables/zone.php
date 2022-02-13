<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
use Umart\Table\AbstractTable;

class UmartTableZone extends AbstractTable
{
	protected function getTableDBName()
	{
		return '#__umart_zones';
	}
}
