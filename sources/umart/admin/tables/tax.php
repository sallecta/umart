<?php
/**
 *  @package     com_easyshop
 *  @version     1.0.5
 *  @Author      JoomTech Team
* @copyright   Copyright (C) 2015 - 2019 www.joomtech.net All Rights Reserved.
 *  @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;
use ES\Table\AbstractTable;

class EasyshopTableTax extends AbstractTable
{
	protected function getTableDBName()
	{
		return '#__easyshop_taxes';
	}
}
