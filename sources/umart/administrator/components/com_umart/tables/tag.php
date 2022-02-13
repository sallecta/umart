<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
use Umart\Table\AbstractTable;

class UmartTableTag extends AbstractTable
{
	protected function getTableDBName()
	{
		return '#__umart_tags';
	}
}
