<?php
/**
 
 
 
 
 
 */

use Umart\Controller\BaseController;
use Joomla\CMS\Factory as CMSFactory;

defined('_JEXEC') or die;

class UmartController extends BaseController
{
	public function display($cachable = false, $urlparams = false)
	{
		$cachable = true;
		$user     = CMSFactory::getUser();
		$task     = $this->input->get('task');
		$view     = $this->input->get('view');

		if ($user->get('id')
			|| $this->input->getMethod() === 'POST'
			|| (!empty($task) && $task != 'display')
			|| in_array($view, ['cart', 'customer'])
		)
		{
			$cachable = false;
		}

		$safeUrlParams = [
			'category_id'      => 'UINT',
			'id'               => 'UINT',
			'cid'              => 'ARRAY',
			'limit'            => 'UINT',
			'limitstart'       => 'UINT',
			'showall'          => 'UINT',
			'return'           => 'BASE64',
			'filter'           => 'STRING',
			'filter_order'     => 'CMD',
			'filter_order_Dir' => 'CMD',
			'filter-search'    => 'STRING',
			'print'            => 'BOOLEAN',
			'lang'             => 'CMD',
			'Itemid'           => 'UINT',
		];

		parent::display($cachable, $safeUrlParams);

		return $this;
	}

	public function filters()
	{
		$app = CMSFactory::getApplication('site');
		$key = $this->input->getString('filterKey');

		if (in_array($key, ['list', 'search']))
		{
			$filters = $this->input->get('filters', [], 'array');
			$app->setUserState('com_umart.product_filter_' . $key, $filters);
		}

		$app->redirect($this->input->getString('return'));
	}
}
