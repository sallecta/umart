<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;
JLoader::import('easyshop.controller.legacy');

use ES\Controller\ControllerLegacy;

class EasyshopControllerMarket extends ControllerLegacy
{
	public function install()
	{
		$this->checkToken('get');
		$message = '';

		try
		{
			$package = $this->getPackageFromUrl();

			if (!$package)
			{
				throw new RuntimeException(JText::_('PLG_EASYSHOP_MARKET_ERR_PACKAGE_NOT_FOUND'));
			}

			$installer = new JInstaller;

			if ($installer->install($package['extractdir']))
			{
				$message = JText::sprintf('PLG_EASYSHOP_MARKET_INSTALL_SUCCESS', $package['type']);
				JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
			}
		}
		catch (RuntimeException $e)
		{
			$this->redirectBackPage($e->getMessage(), 'error');
		}

		$this->redirectBackPage($message);
	}

	protected function getPackageFromUrl()
	{
		/** @var $app \JApplicationCms */
		$app   = easyshop('app');
		$input = $app->input;
		$url   = $input->getBase64('download');

		if (!$url)
		{
			throw new RuntimeException(JText::_('PLG_EASYSHOP_MARKET_ERR_INVALID_URL'));
		}

		$file = JInstallerHelper::downloadPackage(base64_decode($url));

		if (!$file)
		{
			throw new RuntimeException(JText::_('PLG_EASYSHOP_MARKET_ERR_INVALID_URL'));
		}

		$tmpDest = $app->get('tmp_path');
		$package = JInstallerHelper::unpack($tmpDest . '/' . $file, true);

		return $package;
	}

	public function refresh()
	{
		easyshop('app')->setUserState('com_easyshop.channelData', null);
		$this->redirectBackPage(JText::_('PLG_EASYSHOP_MARKET_DATA_REFRESHED'));
	}
}
