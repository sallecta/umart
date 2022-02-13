<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Controller\BaseController;

class UmartController extends BaseController
{
	protected $default_view = 'dashboard';

	public function display($cachable = false, $urlparams = array())
	{
		parent::display($cachable, $urlparams = array());

		return $this;
	}
}
