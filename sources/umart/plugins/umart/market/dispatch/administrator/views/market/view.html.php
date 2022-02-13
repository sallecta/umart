<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\User;
use Umart\View\BaseView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class UmartViewMarket extends BaseView
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
		$app          = umart('app');
		$params       = umart('plugin', 'market')->params;
		$channelUrl   = $params->get('channel_url', 'https://github.com/sallecta/umart/index.php?option=com_umart&amp;task=file.channel');
		$this->secret = $params->get('secret_key');
		$data         = $app->getUserState('com_umart.channelData', null);

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

			$app->setUserState('com_umart.channelData', $data);
		}

		$this->data = $data;
	}

	protected function addToolbar()
	{
		ToolbarHelper::title(JText::_('PLG_UMART_MARKET_LABEL'));
		ToolbarHelper::custom('market.refresh', 'refresh', 'refresh', 'PLG_UMART_MARKET_REFRESH', false);

		if (umart(User::class)->core('admin'))
		{
			ToolbarHelper::preferences('com_umart');
		}
	}
}
