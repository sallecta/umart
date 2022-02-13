<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
use Umart\Table\AbstractTable;

class UmartTableEmail extends AbstractTable
{
	protected function getTableDBName()
	{
		return '#__umart_emails';
	}
}
