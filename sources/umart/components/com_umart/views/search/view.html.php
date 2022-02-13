<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\Utility;
use Umart\View\BaseView;
use Joomla\CMS\Response\JsonResponse;

class UmartViewSearch extends BaseView
{
	protected $products;
	protected $pagination;
	protected $task;
	protected $filters = [];
	protected $type = 'html';

	public function display($tpl = null)
	{
		$app        = plg_sytem_umart_main('app');
		$input      = $app->input;
		$q          = $input->getString('q');
		$prices     = $input->getString('range');
		$tags       = $input->getString('tag');
		$categoryId = $input->get('category');
		$brandId    = $input->get('brand');
		$menu       = $app->getMenu('site')->getActive();

		if ($this->task !== 'display')
		{
			$this->task = $input->get('task');
		}

		if ($menu
			&& @$menu->query['option'] == 'com_umart'
			&& @$menu->query['view'] == 'search'
		)
		{
			foreach ($menu->getParams()->toArray() as $name => $value)
			{
				if ($this->config->exists($name) && trim($value) !== '')
				{
					$this->config->set($name, $value);
				}
			}
		}

		if ($this->task === 'search')
		{
			$productsModel = plg_sytem_umart_main('model', 'Products', UMART_COMPONENT_ADMINISTRATOR);
			$productsModel->setState('filter.search', $q);
			$productsModel->setState('filter.published', 1);
			$productsModel->setState('filter.category_id', $categoryId);
			$productsModel->setState('filter.brand_id', $brandId);
			$productsModel->setState('filter.prices', $prices);
			$productsModel->setState('filter.tags', $tags);
			$app->triggerEvent('onUmartPrepareModelSearch', [&$productsModel]);

			$this->filters = array_merge(
				[
					'sort'    => $this->config->get('search_default_ordering'),
					'display' => $this->config->get('search_default_limit'),
				],
				(array) $app->getUserState('com_umart.product_filter_search', [])
			);

			$utility = new Utility;
			$utility->parseOrderingData($this->filters['sort'], $ordering, $direction);
			$productsModel->setState('list.ordering', $ordering);
			$productsModel->setState('list.direction', $direction);
			$productsModel->setState('list.start', plg_sytem_umart_main('app')->input->get('limitstart', 0, 'uint'));
			$productsModel->setState('list.limit', $display = abs((int) $this->filters['display']));
			$this->products   = $productsModel->getItems();
			$this->pagination = $productsModel->getPagination();

			if ($errors = $productsModel->getErrors())
			{
				throw new RuntimeException(implode(PHP_EOL, $errors), 500);
			}
		}

		$this->type = strtolower($input->getWord('type', 'html'));

		if ($this->task === 'search' && $this->type !== 'html')
		{
			ob_clean();

			switch ($this->type)
			{
				case 'json':
					$app->setHeader('Content-Type', 'application/json');
					$app->sendHeaders();
					echo new JsonResponse($this->products);
					break;

				case 'raw':
					ob_start();
					parent::display($tpl);
					$buffer = ob_get_clean();
					$app->triggerEvent('onUmartAfterDispatch', [&$buffer]);
					echo $buffer;
					break;
			}

			$app->close();
		}
		else
		{
			parent::display($tpl);
		}
	}
}
