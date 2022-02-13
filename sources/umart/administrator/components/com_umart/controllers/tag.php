<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
use Umart\Controller\FormController;

class UmartControllerTag extends FormController
{
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId);
		$append .= '&context=' . $this->input->getCmd('context');

		return $append;
	}

	protected function getRedirectToListAppend()
	{
		$append = parent::getRedirectToListAppend();
		$append .= '&context=' . $this->input->getCmd('context');

		return $append;
	}
}
