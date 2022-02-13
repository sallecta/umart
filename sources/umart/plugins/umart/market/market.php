<?php
/**
 
 
 
 
 
 */

defined('_JEXEC') or die;

JLoader::import('umart.plugin.dispatch');

use Umart\Plugin\PluginDispatch;

class PlgUmartMarket extends PluginDispatch
{
	protected $taskMaps = ['market'];

	public function onUmartNavbarPrepare(&$items)
	{
		$items['system']['children'][] = [
			'icon'  => 'es-icon-store',
			'url'   => 'index.php?option=com_umart&view=market',
			'title' => 'PLG_UMART_MARKET_LABEL',
		];
	}

	public function onUmartMarketAfterSave($context, $table, $isNew)
	{
		$secretKey = trim($this->params->get('secret_key', ''));
		$query     = $this->db->getQuery(true)
			->update($this->db->qn('#__update_sites'))
			->set($this->db->qn('extra_query') . ' = ' . $this->db->q('secretKey=' . $secretKey))
			->where('(' . $this->db->qn('name') . ' LIKE ' . $this->db->q('Umart % Update Server') . ' OR ' . $this->db->qn('name') . ' = ' . $this->db->q('EasyTab Update Server') . ')');
		$this->db->setQuery($query)
			->execute();
	}
}
