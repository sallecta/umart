<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\Order;
use Umart\Classes\User;
use Umart\Method;
use Umart\Plugin\Payment;
use Umart\View\BaseView;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

class UmartViewCustomer extends BaseView
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
		$app            = plg_sytem_umart_main('app');
		$this->state    = plg_sytem_umart_main('state');
		$this->form     = $this->get('Form');
		$this->customer = $this->get('User');
		$this->page     = $app->getUserState('com_umart.customer.page', 'orders');
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
							$start = $app->getUserStateFromRequest('com_umart.customer.list_order_start', 'start', 0, 'uint');
							$this->state->set('model.orders.state.list.start', $start);
							$this->state->set('model.orders.state.filter.user_id', $this->customer->id);
							$this->state->set('model.orders.state.filter.parent_id', 0);
							$this->ordersModel      = plg_sytem_umart_main('model', 'orders', UMART_COMPONENT_ADMINISTRATOR, []);
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
							$trackedOrderId = $app->getUserState('com_umart.customer.guest_order_id');

							if ((int) $trackedOrderId > 0)
							{
								$this->loadOrder($trackedOrderId, false);
							}
							elseif ((int) ($orderId = $app->getUserState('com_umart.customer.order_id', $app->input->get('orderId', 0, 'uint'))) > 0)
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

				$return       = base64_encode(Route::_(UmartHelperRoute::getCustomerRoute(), false));
				$this->navbar = [
					'account' => [
						'icon'  => 'user',
						'title' => 'COM_UMART_MY_ACCOUNT',
						'url'   => 'index.php?option=com_umart&task=customer.page&page=account&return=' . $return,
					],
					'orders'  => [
						'icon'  => 'file-text',
						'title' => 'COM_UMART_MY_ORDERS',
						'url'   => 'index.php?option=com_umart&task=customer.page&page=orders&return=' . $return,
					],
				];

				$app->triggerEvent('onUmartCustomerNavbarPrepare', [&$this->navbar]);
				$app->triggerEvent('onUmartCustomerRegisterPageLayout', [&$this->pageLayouts, $this->page]);
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
				$orderId = $app->getUserState('com_umart.customer.guest_order_id', 0);

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
		$this->orderModel = plg_sytem_umart_main('getModel', 'Order', UMART_COMPONENT_ADMINISTRATOR, []);
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
		$app         = plg_sytem_umart_main('app');
		$this->order = plg_sytem_umart_main(Order::class);
		$this->order->load($orderId);
		$paymentId   = (int) $this->orderForm->getValue('payment_id', 0);
		$methodClass = plg_sytem_umart_main(Method::class);
		PluginHelper::importPlugin('umart_payment');

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
					$app->triggerEvent('onUmartPaymentOrderArea', [$this->order, $this->payment]);
				}
			}
		}
	}
}
