<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ES\View;

defined('_JEXEC') or die;

use ES\Classes\StringHelper;
use ES\Classes\User;
use ES\Helper\Navbar;
use Exception;
use JLoader;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

class ListView extends BaseView
{
	public $filterForm;
	public $activeFilters;
	protected $items;
	protected $state;
	protected $pagination;
	protected $showNavbar = true;
	protected $useFilter = true;
	protected $addToolbar = true;
	protected $navbar;

	public function display($tpl = null)
	{
		$this->items      = $this->get('Items');
		$this->state      = $this->get('State');
		$this->pagination = $this->get('Pagination');

		if ($this->useFilter)
		{
			$this->filterForm    = $this->get('FilterForm');
			$this->activeFilters = $this->get('ActiveFilters');
		}

		if ($errors = $this->get('Errors'))
		{
			throw new Exception(implode(PHP_EOL, $errors), 500);
		}

		if ($this->addToolbar && $this->getLayout() != 'modal')
		{
			$this->addToolbar();
		}

		$this->navbar = $this->showNavbar ? Navbar::render() : null;

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		$user = easyshop(User::class);
		$name = $this->getName();

		ToolbarHelper::title(Text::_('COM_EASYSHOP_' . strtoupper($name) . '_MANAGE'));

		if ($user->core('create'))
		{
			$stringHelper = easyshop(StringHelper::class);
			ToolbarHelper::addNew($stringHelper->toSingular($name) . '.add');
		}

		if ($user->core('edit.state'))
		{
			ToolbarHelper::publish($name . '.publish');
			ToolbarHelper::unpublish($name . '.unpublish');

			if ($this->state->get('filter.published') == -2 && $user->core('delete'))
			{
				ToolbarHelper::deleteList('', $name . '.delete');
			}
			else
			{
				ToolbarHelper::trash($name . '.trash');
			}

			if ($user->core('edit.state'))
			{
				ToolbarHelper::checkin($name . '.checkin');
			}
		}

		if ($user->core('admin'))
		{
			ToolbarHelper::preferences('com_easyshop');
		}

	}

	public function getFormLayout($layoutId)
	{
		return $this->getRenderer()->render('view.list.' . $layoutId, $this);
	}

	protected function getItemLink($id = 0, array $append = [], $key = 'id')
	{
		$name         = $this->getName();
		$stringHelper = easyshop(StringHelper::class);
		$link         = 'index.php?option=com_easyshop&task=' . $stringHelper->toSingular($name) . '.edit';

		if (count($append))
		{
			$link .= '&' . http_build_query($append);
		}

		$link .= '&' . $key . '=' . $id;

		return Route::_($link, false);
	}

}
