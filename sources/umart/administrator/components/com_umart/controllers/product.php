<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\CustomField;
use Umart\Classes\Media;
use Umart\Controller\FormController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Registry\Registry;

class UmartControllerProduct extends FormController
{
	public function loadOptions()
	{
		try
		{
			$optionId = $this->input->post->getInt('optionId');
			$value    = $this->input->post->getString('value');

			/** @var CustomField $customField */
			$customField = plg_sytem_umart_main(CustomField::class, [
					'reflector' => 'com_umart.product.option',
				]
			);
			$field       = $customField->findField($optionId);

			if (!$field)
			{
				throw new RuntimeException('Option ID: ' . $optionId . ' not found.');
			}

			$params   = new Registry($field->params);
			$response = plg_sytem_umart_main('renderer')->render('modal.option', [
				'name'            => $field->name,
				'type'            => $field->type,
				'options'         => (array) $params->get('options', []),
				'id'              => $optionId,
				'value'           => !empty($value) ? (array) json_decode($value, true) : [],
				'media'           => plg_sytem_umart_main(Media::class),
				'multiCurrencies' => plg_sytem_umart_main('config', 'multi_currencies_mode', '0'),
			]);
		}
		catch (RuntimeException $e)
		{
			$response = $e;
		}

		echo new JsonResponse($response);
		plg_sytem_umart_main('app')->close();
	}

	public function loadFields()
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$app      = plg_sytem_umart_main('app');
		$oldData  = (array) $app->getUserState('com_umart.edit.product.data', []);
		$data     = array_merge($oldData, $app->input->get('jform', [], 'array'));
		$redirect = 'index.php?option=com_umart&view=product&layout=edit' . (isset($data['id']) ? '&id=' . $data['id'] : '');

		$app->setUserState('com_umart.edit.product.data', $data);
		$app->redirect(Route::_($redirect, false));
	}
}
