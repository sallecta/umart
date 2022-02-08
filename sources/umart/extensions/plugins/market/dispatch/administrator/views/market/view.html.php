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
use Joomla\CMS\Toolbar\ToolbarHelper;

class EasyshopViewMarket extends BaseView
{
	protected $data = [];
	protected $secret = null;

	protected function beforeDisplay()
	{
		$this->loadDataChannel();
		$this->addToolbar();
	}

	public function loadDataChannel()
	{
		$app          = easyshop('app');
		$params       = easyshop('plugin', 'market')->params;
		$channelUrl   = $params->get('channel_url', 'https://www.joomtech.net/index.php?option=com_easyshop&amp;task=file.channel');
		$this->secret = $params->get('secret_key');
		$data         = $app->getUserState('com_easyshop.channelData', null);

		if (null === $data)
		{
			$data = [];

			if ($channelUrl
				&& ($contents = file_get_contents(str_replace('&amp;', '&', $channelUrl)))
				&& ($contents = json_decode($contents, true))
				&& json_last_error() == JSON_ERROR_NONE
				&& !empty($contents['packages'])
			)
			{
				$data = $contents;
			}

			$app->setUserState('com_easyshop.channelData', $data);
		}

		$this->data = $data;
	}

	protected function addToolbar()
	{
		ToolbarHelper::title(JText::_('PLG_EASYSHOP_MARKET_LABEL'));
		ToolbarHelper::custom('market.refresh', 'refresh', 'refresh', 'PLG_EASYSHOP_MARKET_REFRESH', false);

		if (easyshop(User::class)->core('admin'))
		{
			ToolbarHelper::preferences('com_easyshop');
		}
	}
}
