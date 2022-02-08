<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\CustomField;
use ES\Classes\Media;
use ES\Controller\FormController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Registry\Registry;

class EasyshopControllerProduct extends FormController
{
	public function loadOptions()
	{
		try
		{
			$optionId = $this->input->post->getInt('optionId');
			$value    = $this->input->post->getString('value');

			/** @var CustomField $customField */
			$customField = easyshop(CustomField::class, [
					'reflector' => 'com_easyshop.product.option',
				]
			);
			$field       = $customField->findField($optionId);

			if (!$field)
			{
				throw new RuntimeException('Option ID: ' . $optionId . ' not found.');
			}

			$params   = new Registry($field->params);
			$response = easyshop('renderer')->render('modal.option', [
				'name'            => $field->name,
				'type'            => $field->type,
				'options'         => (array) $params->get('options', []),
				'id'              => $optionId,
				'value'           => !empty($value) ? (array) json_decode($value, true) : [],
				'media'           => easyshop(Media::class),
				'multiCurrencies' => easyshop('config', 'multi_currencies_mode', '0'),
			]);
		}
		catch (RuntimeException $e)
		{
			$response = $e;
		}

		echo new JsonResponse($response);
		easyshop('app')->close();
	}

	public function loadFields()
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$app      = easyshop('app');
		$oldData  = (array) $app->getUserState('com_easyshop.edit.product.data', []);
		$data     = array_merge($oldData, $app->input->get('jform', [], 'array'));
		$redirect = 'index.php?option=com_easyshop&view=product&layout=edit' . (isset($data['id']) ? '&id=' . $data['id'] : '');

		$app->setUserState('com_easyshop.edit.product.data', $data);
		$app->redirect(Route::_($redirect, false));
	}
}
