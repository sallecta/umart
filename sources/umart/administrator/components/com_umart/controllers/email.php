<?php

/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Controller\FormController;
use Umart\LayoutHelper;

class UmartControllerEmail extends FormController
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
				/** @var $renderer \Umart\LayoutHelper */
				$parts = explode('.', $layoutId);

				if (!empty($parts[1]) && in_array($parts[0], ['umart', 'umart_payment', 'umartshipping']))
				{
					$group    = $parts[0];
					$element  = $parts[1];
					$layoutId = str_replace($group . '.', '', $layoutId);
				}
				else
				{
					$group   = 'umart';
					$element = $parts[0];
				}

				if ($plugin = plg_sytem_umart_main('plugin', $element, $group))
				{
					JPluginHelper::importPlugin($group, $element);
					$layoutPath = JPATH_PLUGINS . '/' . $plugin->folder . '/' . $plugin->element . '/layouts';

					if (is_file($layoutPath . '/' . str_replace('.', '/', $layoutId) . '.php'))
					{
						$renderer = plg_sytem_umart_main('state')->get('plugin.' . $plugin->folder . '.' . $plugin->element . '.renderer');

						if ($renderer instanceof LayoutHelper)
						{
							$data['send_body'] = $renderer->render($layoutId);
						}
						else
						{
							$data['send_body'] = plg_sytem_umart_main('renderer')->render($layoutId);
						}
					}
					else
					{
						$data['send_body'] = '';
					}
				}
				elseif ($default)
				{
					$data['send_body'] = plg_sytem_umart_main('renderer')->render($layoutId);
				}
				else
				{
					$data['send_body'] = '';
				}

			}

			plg_sytem_umart_main('app')->setUserState('com_umart.edit.email.data', $data);
		}

		$this->setRedirect(JRoute::_('index.php?option=com_umart&view=email&layout=edit', false));
		$this->redirect();
	}
}
