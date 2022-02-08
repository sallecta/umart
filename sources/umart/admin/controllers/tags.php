<?php

/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;
use ES\Controller\AdminController;

class EasyshopControllerTags extends AdminController
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
