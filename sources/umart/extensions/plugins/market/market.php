<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

JLoader::import('easyshop.plugin.dispatch');

use ES\Plugin\PluginDispatch;

class PlgEasyshopMarket extends PluginDispatch
{
	protected $taskMaps = ['market'];

	public function onEasyshopNavbarPrepare(&$items)
	{
		$items['system']['children'][] = [
			'icon'  => 'es-icon-store',
			'url'   => 'index.php?option=com_easyshop&view=market',
			'title' => 'PLG_EASYSHOP_MARKET_LABEL',
		];
	}

	public function onEasyshopMarketAfterSave($context, $table, $isNew)
	{
		$secretKey = trim($this->params->get('secret_key', ''));
		$query     = $this->db->getQuery(true)
			->update($this->db->qn('#__update_sites'))
			->set($this->db->qn('extra_query') . ' = ' . $this->db->q('secretKey=' . $secretKey))
			->where('(' . $this->db->qn('name') . ' LIKE ' . $this->db->q('EasyShop % Update Server') . ' OR ' . $this->db->qn('name') . ' = ' . $this->db->q('EasyTab Update Server') . ')');
		$this->db->setQuery($query)
			->execute();
	}
}
