<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;
use Joomla\Registry\Registry;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory as CMSFactory;
use ES\Model\AdminModel;

class EasyshopModelMethod extends AdminModel
{
	protected $translationRefTable = 'easyshop_methods';

	protected function getMethod($id)
	{
		$pluginTable = Table::getInstance('Extension', 'JTable');

		if ($pluginTable->load($id))
		{
			return $pluginTable;
		}

		throw new RuntimeException(Text::sprintf('COM_EASYSHOP_PLUGIN_ID_NOT_FOUND', $id), 404);
	}

	public function getForm($data = [], $loadData = true)
	{
		if ($form = parent::getForm($data, $loadData))
		{
			if (!$form->getValue('plugin_id'))
			{
				$form->setValue('plugin_id', null, easyshop('app')->input->getInt('method_id'));
			}

			$pluginId = $form->getValue('plugin_id');
			$plugin   = $this->getMethod($pluginId);
			$path     = JPATH_PLUGINS . '/' . $plugin->folder . '/' . $plugin->element;
			$registry = new Registry($this->loadFormData());
			$registry->set('plugin_id', $pluginId);
			CMSFactory::getLanguage()->load('plg_' . $plugin->folder . '_' . $plugin->element, $path);
			$form->loadFile($path . '/' . $plugin->element . '.xml', true, 'easyshop');

			if ($plugin->folder == 'easyshoppayment')
			{
				if ($form->getField('is_card', 'params'))
				{
					if (!$form->getField('accepted_cards', 'params'))
					{
						$form->loadFile(__DIR__ . '/forms/payment/accept_card.xml');
					}

					if (!$registry->get('params.is_card'))
					{
						$registry->set('params.is_card', $form->getField('is_card', 'params')->getAttribute('value'));
					}
				}

				if (!$form->getField('payment_success_message', 'params'))
				{
					$form->loadFile(__DIR__ . '/forms/payment/success_message.xml');
				}
			}

			if ($loadData)
			{
				// Reload data
				$form->bind($registry->toArray());
			}
		}

		return $form;
	}

	public function save($data)
	{
		$plugin = $this->getMethod($data['plugin_id']);
		$type   = ucfirst($plugin->folder);
		PluginHelper::importPlugin($plugin->folder, $plugin->element);
		$results = easyshop('app')->triggerEvent('on' . $type . 'BeforeSave', [$plugin, &$data]);

		if (in_array(false, $results, true))
		{
			return false;
		}

		if ($result = parent::save($data))
		{
			easyshop('app')->triggerEvent('on' . $type . 'AfterSave', [$plugin, $data]);
		}

		return $result;
	}
}
