<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\User;
use ES\View\BaseView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class EasyshopViewDashboard extends BaseView
{
	protected $tiles;
	protected $currencyClass;
	protected $latestOrders = [];
	protected $ordersThisMonth = [];
	protected $averageDay = [
		'count'     => 0,
		'totalPaid' => 0.0000,
	];
	protected $ordersThisDay = [
		'items'      => [],
		'saleItems'  => [],
		'totalPrice' => 0.000,
		'totalPaid'  => 0.0000,
	];

	public function display($tpl = null)
	{
		easyshop('app')->input->set('view', 'dashboard');
		$this->tiles = $this->getRenderer()->render('dashboard.tile.tile', $this->getDisplayData());
		$user        = easyshop(User::class);

		ToolbarHelper::title(Text::_('COM_EASYSHOP_DASHBOARD'));

		if ($user->core('admin') || $user->core('options'))
		{
			ToolbarHelper::preferences('com_easyshop');
		}

		parent::display($tpl);
	}

	public function getDisplayData()
	{
		$this->latestOrders    = $this->get('LatestOrders');
		$this->ordersThisMonth = $this->get('OrdersThisMonth');
		$this->currencyClass   = $this->get('CurrencyClass');

		if ($errors = $this->get('Errors'))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		$date  = $this->utility->getDate();
		$toDay = $date->format('Y-m-d', true);
		$t     = (int) $date->format('t');

		$this->ordersThisDay['fromDate'] = $toDay;
		$this->ordersThisDay['toDate']   = $toDay;
		$this->averageDay['count']       = round(count($this->ordersThisMonth['saleItems']) / $t, 2);
		$this->averageDay['totalPaid']   = $this->ordersThisMonth['totalPaid'] / $t;

		foreach ($this->ordersThisMonth['items'] as $item)
		{
			if ($this->utility->getDate($item->created_date)->format('Y-m-d', true) == $toDay)
			{
				$this->ordersThisDay['items'][]    = $item;
				$this->ordersThisDay['totalPrice'] += (float) $item->total_price;
				$this->ordersThisDay['totalPaid']  += (float) $item->total_paid;

				if ($item->total_paid > 0.0000)
				{
					$this->ordersThisDay['saleItems'][] = $item;
				}
			}
		}

		return [
			'currencyClass'   => $this->currencyClass,
			'latestOrders'    => $this->latestOrders,
			'ordersThisMonth' => $this->ordersThisMonth,
			'ordersThisDay'   => $this->ordersThisDay,
			'averageDay'      => $this->averageDay,
		];
	}
}
