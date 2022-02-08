<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use ES\Classes\Tags;

defined('_JEXEC') or die;

class ModEasyshopTagsHelper
{
	public static function getTags()
	{
		return easyshop(Tags::class)->getTags();
	}
}
