<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
JLoader::import('umart.controller.legacy');

use Umart\Controller\ControllerLegacy;

class UmartControllerMarket extends ControllerLegacy
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
				throw new RuntimeException(JText::_('PLG_UMART_MARKET_ERR_PACKAGE_NOT_FOUND'));
			}

			$installer = new JInstaller;

			if ($installer->install($package['extractdir']))
			{
				$message = JText::sprintf('PLG_UMART_MARKET_INSTALL_SUCCESS', $package['type']);
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
		$app   = umart('app');
		$input = $app->input;
		$url   = $input->getBase64('download');

		if (!$url)
		{
			throw new RuntimeException(JText::_('PLG_UMART_MARKET_ERR_INVALID_URL'));
		}

		$file = JInstallerHelper::downloadPackage(base64_decode($url));

		if (!$file)
		{
			throw new RuntimeException(JText::_('PLG_UMART_MARKET_ERR_INVALID_URL'));
		}

		$tmpDest = $app->get('tmp_path');
		$package = JInstallerHelper::unpack($tmpDest . '/' . $file, true);

		return $package;
	}

	public function refresh()
	{
		umart('app')->setUserState('com_umart.channelData', null);
		$this->redirectBackPage(JText::_('PLG_UMART_MARKET_DATA_REFRESHED'));
	}
}
