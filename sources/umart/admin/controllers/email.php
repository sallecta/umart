<?php

/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Controller\FormController;
use ES\LayoutHelper;

class EasyshopControllerEmail extends FormController
{
	public function loadDefaultTemplate()
	{
		if (!$this->input->getInt('id'))
		{
			$data    = $this->input->post->get('jform', [], 'array');
			$default = true;

			switch ($data['send_on'])
			{
				case '[ON_NEW_ORDER]':
					$layoutId = 'email.order-new-html';

					break;

				case '[ON_ORDER_CHANGE_STATE]':
					$layoutId = 'email.order-change-status-html';
					break;

				case '[ON_ORDER_CHANGE_PAYMENT]':
					$layoutId = 'email.order-change-payment-status-html';
					break;

				default:
					$default  = false;
					$layoutId = str_replace('_', '.', trim(strtolower($data['send_on']), '[]'));
					break;
			}

			if (!empty($layoutId))
			{
				/** @var $renderer \ES\LayoutHelper */
				$parts = explode('.', $layoutId);

				if (!empty($parts[1]) && in_array($parts[0], ['easyshop', 'easyshoppayment', 'easyshopshipping']))
				{
					$group    = $parts[0];
					$element  = $parts[1];
					$layoutId = str_replace($group . '.', '', $layoutId);
				}
				else
				{
					$group   = 'easyshop';
					$element = $parts[0];
				}

				if ($plugin = easyshop('plugin', $element, $group))
				{
					JPluginHelper::importPlugin($group, $element);
					$layoutPath = JPATH_PLUGINS . '/' . $plugin->folder . '/' . $plugin->element . '/layouts';

					if (is_file($layoutPath . '/' . str_replace('.', '/', $layoutId) . '.php'))
					{
						$renderer = easyshop('state')->get('plugin.' . $plugin->folder . '.' . $plugin->element . '.renderer');

						if ($renderer instanceof LayoutHelper)
						{
							$data['send_body'] = $renderer->render($layoutId);
						}
						else
						{
							$data['send_body'] = easyshop('renderer')->render($layoutId);
						}
					}
					else
					{
						$data['send_body'] = '';
					}
				}
				elseif ($default)
				{
					$data['send_body'] = easyshop('renderer')->render($layoutId);
				}
				else
				{
					$data['send_body'] = '';
				}

			}

			easyshop('app')->setUserState('com_easyshop.edit.email.data', $data);
		}

		$this->setRedirect(JRoute::_('index.php?option=com_easyshop&view=email&layout=edit', false));
		$this->redirect();
	}
}
