<?php

/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
use Umart\Controller\AdminController;

class UmartControllerTags extends AdminController
{
	public function redirect()
	{
		if ($this->redirect)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage($this->message, $this->messageType);

			if ($reflector = $this->input->getCmd('context'))
			{
				$uri = new JUri($this->redirect);
				$uri->setVar('context', $reflector);

				$this->redirect = $uri->toString();
			}

			$app->redirect($this->redirect);
		}

		return false;
	}
}
