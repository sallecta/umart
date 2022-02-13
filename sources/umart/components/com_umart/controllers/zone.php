<?php
/**
 
 
 
 
 
 */

use Umart\Classes\Zone;
use Umart\Controller\BaseController;
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;

class UmartControllerZone extends BaseController
{
	public function loadByParents()
	{
		$parents = $this->input->get('parents', [], 'array');
		$data    = plg_sytem_umart_main(Zone::class)->loadByParents($parents);
		ob_clean();

		echo HTMLHelper::_('select.options', $data);

		plg_sytem_umart_main('app')->close();
	}
}
