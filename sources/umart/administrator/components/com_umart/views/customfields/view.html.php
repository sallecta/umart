<?php
/**
 
 
 
 
 
 */

defined('_JEXEC') or die;

use Umart\Classes\CustomField;
use Umart\View\ListView;

class UmartViewCustomfields extends ListView
{
	public function display($tpl = null)
	{
		$reflector = plg_sytem_umart_main('app')->input->getCmd('reflector');
		plg_sytem_umart_main(CustomField::class)->check(false, $reflector);

		parent::display($tpl);
	}
}
