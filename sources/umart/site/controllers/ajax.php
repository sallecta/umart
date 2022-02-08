<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\Media;
use ES\Classes\Method;
use ES\Classes\Order;
use ES\Classes\Translator;
use ES\Classes\Utility;
use ES\Controller\BaseController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Response\JsonResponse;

class EasyshopControllerAjax extends BaseController
{
	public function validateVat()
	{
		JLoader::register('EasyshopRuleVat', ES_COMPONENT_ADMINISTRATOR . '/models/rules/vat.php');
		$vat      = $this->input->get('vatNumber');
		$required = $this->input->get('required');
		$response = EasyshopRuleVat::isValid($vat, $required);
		echo new JsonResponse($response);

		$this->app->close();
	}

	public function loadPrintPage()
	{
		try
		{
			/** @var Order $order */
			$dataOrder = $this->app->input->get('dataOrder', [], 'array');
			$order     = easyshop(Order::class);
			$dataLoad  = [
				'id'         => isset($dataOrder['id']) ? (int) $dataOrder['id'] : 0,
				'order_code' => isset($dataOrder['code']) ? $dataOrder['code'] : '',
				'user_email' => isset($dataOrder['email']) ? $dataOrder['email'] : '',
			];

			if (empty($dataLoad['id'])
				|| empty($dataLoad['order_code'])
				|| empty($dataLoad['user_email'])
				|| !$order->load($dataLoad)
			)
			{
				throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
			}

			$vendorId                 = (int) $order->get('vendor_id', 0);
			$displayData              = easyshop(Utility::class)->getShopInformation($vendorId);
			$displayData['order']     = $order;
			$displayData['pageTitle'] = $this->app->input->getString('pageTitle', Text::_('COM_EASYSHOP_ORDER_PRINT_TITLE'));
			$displayData['shipping']  = null;
			$displayData['payment']   = null;
			$shippingId               = $order->get('shipping_id', 0);
			$paymentId                = $order->get('payment_id', 0);

			if ($shippingId || $paymentId)
			{
				$methodClass = easyshop(Method::class);

				if ($shippingId && ($shipping = $methodClass->get($shippingId)))
				{
					$displayData['shipping'] = $shipping;
				}

				if ($paymentId && ($payment = $methodClass->get($paymentId)))
				{
					$displayData['payment'] = $payment;
				}
			}

			$response = easyshop('renderer')->render('print.print', $displayData);
		}
		catch (RuntimeException $e)
		{
			$response = $e;
		}

		echo new JsonResponse($response);

		$this->app->close();
	}

	public function loadImage()
	{
		/** @var Media $mediaClass */
		$mediaClass          = easyshop(Media::class);
		$size                = $this->app->input->getString('size');
		$imageBaseFilePath   = trim(base64_decode($this->app->input->getBase64('file')), './');
		$resultImageBasePath = JPATH_ROOT . '/' . $mediaClass->getResizeImageBasePath($imageBaseFilePath, $size, true);
		$mime                = $mediaClass->getMimeByFile($resultImageBasePath);

		if (!is_file($resultImageBasePath)
			|| !preg_match('/\.(gif|png|jpg|jpeg|svg|webp)$/i', $imageBaseFilePath)
			|| stripos($mime, 'image/') !== 0
			|| false === getimagesize($resultImageBasePath)
		)
		{
			throw new RuntimeException('Invalid Image.');
		}

		$this->app->setHeader('Content-type', $mime);
		$this->app->sendHeaders();
		readfile($resultImageBasePath);
		$this->app->close();
	}

	public function loadCategoryMultiLanguageTabs()
	{
		try
		{
			$form = Translator::getCategoryForm();

			if ($refKey = $this->app->input->post->get('refKey', 0, 'uint'))
			{
				$db        = easyshop('db');
				$query     = $db->getQuery(true)
					->select('a.translationId, a.translatedValue')
					->from($db->quoteName('#__easyshop_translations', 'a'))
					->where('a.translationId LIKE ' . $db->quote('%.categories.' . $refKey . '.%'));
				$transData = [];

				if ($rows = $db->setQuery($query)->loadObjectList())
				{
					foreach ($rows as $row)
					{
						list($langCode, $refTable, $refId, $refField) = explode('.', $row->translationId, 4);
						$transData[$langCode][$refField] = $row->translatedValue;
					}
				}

				$form->setTranslationsData($transData);
				$query->clear()
					->select('a.title, a.alias')
					->from($db->quoteName('#__categories', 'a'))
					->where('a.id = ' . (int) $refKey);

				if ($row = $db->setQuery($query)->loadObject())
				{
					$form->setValue('title', null, $row->title);
					$form->setValue('alias', null, $row->alias);
				}
			}

			echo new JsonResponse(
				[
					'title' => $form->getInput('title'),
					'alias' => $form->getInput('alias'),
				]
			);
		}
		catch (RuntimeException $e)
		{
			echo new JsonResponse($e);
		}

		$this->app->close();
	}
}
