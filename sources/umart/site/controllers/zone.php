<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use ES\Classes\Zone;
use ES\Controller\BaseController;
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;

class EasyshopControllerZone extends BaseController
{
	public function loadByParents()
	{
		$parents = $this->input->get('parents', [], 'array');
		$data    = easyshop(Zone::class)->loadByParents($parents);
		ob_clean();

		echo HTMLHelper::_('select.options', $data);

		easyshop('app')->close();
	}
}
