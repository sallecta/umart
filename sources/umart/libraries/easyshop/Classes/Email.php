<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ES\Classes;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

class Email
{
	protected static $triggers = [];
	/**
	 * @var Log $log
	 * @since 1.0.0
	 */
	protected $log;
	/**
	 * @var CMSApplication $app
	 * @since 1.0.1
	 */
	protected $app;

	public function __construct($config = [])
	{
		$this->log = easyshop(Log::class);
		$this->app = easyshop('app');
	}

	public function sendOn($triggerName, $replaceData = [], Order $order = null)
	{
		$emails = $this->getEmails();

		if (!in_array($triggerName, array_keys($this->register()), true) || empty($emails[$triggerName]))
		{
			return;
		}

		foreach ($emails[$triggerName] as $email)
		{
			if ($order instanceof Order)
			{
				$orderVendorId = (int) $order->get('vendor_id', 0);

				if ($orderVendorId > 0 && $orderVendorId !== (int) $email->vendor_id)
				{
					continue;
				}

				if ($triggerName === '[ON_ORDER_CHANGE_PAYMENT]'
					&& !empty($email->order_payment)
				)
				{
					$orderPayment = @json_decode($email->order_payment);

					if (is_array($orderPayment)
						&& count($orderPayment)
						&& !in_array((int) $order->get('payment_status', 0), ArrayHelper::toInteger($orderPayment))
					)
					{
						continue;
					}
				}

				if ($triggerName === '[ON_ORDER_CHANGE_STATE]'
					&& !empty($email->order_status)
				)
				{
					$orderStatus = @json_decode($email->order_status);

					if (is_array($orderStatus)
						&& count($orderStatus)
						&& !in_array((int) $order->get('state', 0), ArrayHelper::toInteger($orderStatus))
					)
					{
						continue;
					}
				}
			}

			$this->sendEmail($email, $replaceData, $order);
		}
	}

	public function getEmails()
	{
		static $emails = null;

		if (null === $emails)
		{
			$emailsModel = easyshop('model', 'Emails', ES_COMPONENT_ADMINISTRATOR);
			$emailsModel->setState('filter.published', 1);

			if (Multilanguage::isEnabled())
			{
				$emailsModel->setState('filter.language', ['*', CMSFactory::getLanguage()->getTag()]);
			}

			$emails = [];

			foreach ($emailsModel->getItems() as $email)
			{
				$emails[$email->send_on][] = $email;
			}
		}

		return $emails;
	}

	public function register($trigger = null, $title = null)
	{
		$defaults = [
			'[ON_NEW_ORDER]'            => Text::_('COM_EASYSHOP_ON_NEW_ORDER'),
			'[ON_ORDER_CHANGE_STATE]'   => Text::_('COM_EASYSHOP_ON_ORDER_CHANGE_STATE'),
			'[ON_ORDER_CHANGE_PAYMENT]' => Text::_('COM_EASYSHOP_ON_PAYMENT_CHANGE_STATE'),
		];

		if (null !== $trigger && null !== $title)
		{
			$trigger = '[' . preg_replace('/[^0-9A-Z_]/i', '', strtoupper($trigger)) . ']';

			if (!isset(static::$triggers[$trigger]) && !in_array($trigger, $defaults))
			{
				static::$triggers[$trigger] = Text::_($title);
			}
		}

		return array_merge($defaults, static::$triggers);
	}

	public function sendEmail($email, $replaceData, Order $order = null)
	{
		$mailer       = CMSFactory::getMailer();
		$sendToEmails = trim($email->send_to_emails);
		$sendSubject  = trim($email->send_subject);
		$sendBody     = trim($email->send_body);

		if (isset($replaceData['{CUSTOMER_EMAIL}']) && filter_var($replaceData['{CUSTOMER_EMAIL}'], FILTER_VALIDATE_EMAIL))
		{
			$sendToEmails = str_ireplace('{CUSTOMER_EMAIL}', $replaceData['{CUSTOMER_EMAIL}'], $sendToEmails);
		}

		$sendToEmails = preg_split('/\r\n|\n|;/', $sendToEmails);
		$recipients   = [];

		foreach ($sendToEmails as $sendToEmail)
		{
			$sendToEmail = trim($sendToEmail);

			if (filter_var($sendToEmail, FILTER_VALIDATE_EMAIL))
			{
				$recipients[] = $sendToEmail;
			}
		}

		if (count($recipients))
		{
			foreach ($replaceData as $key => $value)
			{
				if (stripos($sendSubject, $key) !== false)
				{
					$sendSubject = str_ireplace($key, $value, $sendSubject);
				}

				if (stripos($sendBody, $key) !== false)
				{
					$sendBody = str_ireplace($key, $value, $sendBody);
				}
			}

			$utility = easyshop(Utility::class);
			$mailer->setFrom($email->send_from_email, $email->send_from_name);
			$mailer->setSubject($utility->convertRelativeToAbsoluteUrl($sendSubject));
			$mailer->isHtml(true);
			$mailer->addRecipient($recipients);
			$mailer->setBody($utility->convertRelativeToAbsoluteUrl($sendBody));
			$sprintfData  = [$sendSubject, implode(', ', $recipients),];
			$previousData = $sendBody;
			$this->app->triggerEvent('onEasyshopBeforeSendEmail', [$mailer, $email, $order]);
			$modifiedData = $mailer->Body;

			if ($mailer->Send())
			{
				$this->log->addEntry('com_easyshop.email', 'COM_EASYSHOP_EMAIL_SENT_SUCCESS', $sprintfData, $previousData, $modifiedData);
				$this->app->triggerEvent('onEasyshopAfterSendEmail', [$mailer, $email, $order]);
			}
			else
			{
				$this->log->addEntry('com_easyshop.email', 'COM_EASYSHOP_EMAIL_SENT_FAIL', $sprintfData, $previousData, $modifiedData);
			}
		}
	}
}
