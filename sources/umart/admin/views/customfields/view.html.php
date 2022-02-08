<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use ES\Classes\CustomField;
use ES\View\ListView;

class EasyshopViewCustomfields extends ListView
{
	public function display($tpl = null)
	{
		$reflector = easyshop('app')->input->getCmd('reflector');
		easyshop(CustomField::class)->check(false, $reflector);

		parent::display($tpl);
	}
}
