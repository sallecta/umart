<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\Order;
use ES\Classes\User;
use ES\Method;
use ES\Plugin\Payment;
use ES\View\BaseView;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

class EasyshopViewCustomer extends BaseView
{
	public $filterForm;
	public $activeFilters;
	protected $form;
	protected $customer;
	protected $page;
	protected $ordersModel;
	protected $orderModel;
	protected $orderForm;
	protected $ordersPagination;
	protected $ordersState;
	protected $orders;
	protected $order;
	protected $payment;
	protected $state;
	protected $navbar = [];
	protected $pageLayouts = [];
	protected $paymentOptionsList = [];

	public function display($tpl = null)
	{
		/**
		 * @var $user User
		 * @var $app  CMSApplication
		 */
		$app            = easyshop('app');
		$this->state    = easyshop('state');
		$this->form     = $this->get('Form');
		$this->customer = $this->get('User');
		$this->page     = $app->getUserState('com_easyshop.customer.page', 'orders');
		$layout         = $this->getLayout();

		try
		{
			if ($this->customer->id)
			{
				$isCustomerView = $app->input->get('view') === 'customer';

				if ($isCustomerView)
				{
					switch ($this->page)
					{
						case 'orders':
							$start = $app->getUserStateFromRequest('com_easyshop.customer.list_order_start', 'start', 0, 'uint');
							$this->state->set('model.orders.state.list.start', $start);
							$this->state->set('model.orders.state.filter.user_id', $this->customer->id);
							$this->state->set('model.orders.state.filter.parent_id', 0);
							$this->ordersModel      = easyshop('model', 'orders', ES_COMPONENT_ADMINISTRATOR, []);
							$this->orders           = $this->ordersModel->getItems();
							$this->ordersState      = $this->ordersModel->getState();
							$this->ordersPagination = $this->ordersModel->getPagination();
							$this->filterForm       = $this->ordersModel->getFilterForm();
							$this->activeFilters    = $this->ordersModel->getActiveFilters();
							$this->filterForm->removeField('user_id', 'filter');
							$this->filterForm->removeField('product_id', 'filter');
							unset($this->activeFilters['product_id']);
							unset($this->activeFilters['user_id']);
							break;

						case 'order':
							$trackedOrderId = $app->getUserState('com_easyshop.customer.guest_order_id');

							if ((int) $trackedOrderId > 0)
							{
								$this->loadOrder($trackedOrderId, false);
							}
							elseif ((int) ($orderId = $app->getUserState('com_easyshop.customer.order_id', $app->input->get('orderId', 0, 'uint'))) > 0)
							{
								$this->loadOrder($orderId);
							}
							else
							{
								$this->page = 'orders';
							}

							break;
					}
				}

				$return       = base64_encode(Route::_(EasyshopHelperRoute::getCustomerRoute(), false));
				$this->navbar = [
					'account' => [
						'icon'  => 'user',
						'title' => 'COM_EASYSHOP_MY_ACCOUNT',
						'url'   => 'index.php?option=com_easyshop&task=customer.page&page=account&return=' . $return,
					],
					'orders'  => [
						'icon'  => 'file-text',
						'title' => 'COM_EASYSHOP_MY_ORDERS',
						'url'   => 'index.php?option=com_easyshop&task=customer.page&page=orders&return=' . $return,
					],
				];

				$app->triggerEvent('onEasyshopCustomerNavbarPrepare', [&$this->navbar]);
				$app->triggerEvent('onEasyshopCustomerRegisterPageLayout', [&$this->pageLayouts, $this->page]);
				$this->state->set('customer.navbar', $this->loadTemplate('navbar'));

				if ($isCustomerView)
				{
					if (isset($this->pageLayouts[$this->page]))
					{
						if (is_dir($this->pageLayouts[$this->page]))
						{
							$this->_path['template'][] = $this->pageLayouts[$this->page];
						}
						else
						{
							$this->state->set('customer.page', $this->pageLayouts[$this->page]);
						}
					}
					else
					{
						$this->state->set('customer.page', $this->loadTemplate($this->page));
					}
				}
			}
			else
			{
				$orderId = $app->getUserState('com_easyshop.customer.guest_order_id', 0);

				if ((int) $orderId > 0)
				{
					$this->loadOrder($orderId, false);
				}

				$layout = 'guest';
			}

			if ($this->state->get('customer.display') !== false)
			{
				$app->input->set('layout', $layout);
				$this->setLayout($layout);
				parent::display($tpl);
			}
		}
		catch (Exception $e)
		{
			echo $this->getRenderer()->render('customer.exception', [
				'e'      => $e,
				'navbar' => $this->state->get('customer.navbar'),
			]);
		}
	}

	protected function loadOrder($orderId, $checkOwner = true)
	{
		$this->state->set('model.order.state.order.id', $orderId);
		$this->orderModel = easyshop('getModel', 'Order', ES_COMPONENT_ADMINISTRATOR, []);
		$this->orderForm  = $this->orderModel->getForm();

		// Check is it owner?
		if ($checkOwner)
		{
			$userId = (int) $this->orderForm->getValue('user_id', 0);

			if ($userId < 1 || $userId !== (int) $this->customer->id)
			{
				$this->customer->stop();
			}
		}

		/**
		 * @var Method  $methodClass
		 * @var Payment $handler
		 */
		$app         = easyshop('app');
		$this->order = easyshop(Order::class);
		$this->order->load($orderId);
		$paymentId   = (int) $this->orderForm->getValue('payment_id', 0);
		$methodClass = easyshop(Method::class);
		PluginHelper::importPlugin('easyshoppayment');

		if ((int) $this->order->get('payment_status', 0) !== 1 && ($paymentList = $methodClass->getPaymentMethods()))
		{
			foreach ($paymentList as $paymentItem)
			{
				if ($handler = Payment::getHandler($paymentItem->element))
				{
					$order = clone $this->order;
					$order->set('payment_id', $paymentItem->id);

					if ($paymentForm = $handler->registerCustomerPaymentForm($order, $paymentItem))
					{
						$this->paymentOptionsList[$paymentItem->element] = [
							$paymentItem->name,
							$paymentForm,
						];
					}
				}
			}
		}

		if ($paymentId > 0)
		{
			if ($payment = $methodClass->get($paymentId))
			{
				$this->payment = clone $payment;

				if ($handler = Payment::getHandler($this->payment->element))
				{
					$registry = new Registry;
					$registry->loadString((string) $this->order->get('payment_data', '{}'));
					$this->payment->data = $registry;

					$registry = new Registry;
					$registry->loadString((string) $this->payment->params);
					$this->payment->params = $registry;
					$app->triggerEvent('onEasyshopPaymentOrderArea', [$this->order, $this->payment]);
				}
			}
		}
	}
}
