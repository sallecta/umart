<?php
/**
 * @package     com_easyshop
 * @version     1.0.5
 * @Author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2019 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\System;
use ES\Controller\AdminController;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Router\Route;

class EasyshopControllerProducts extends AdminController
{
	public function export()
	{
		$this->checkToken();
		$app  = easyshop('app');
		$file = easyshop(System::class)->export();

		if (false !== $file)
		{
			if (function_exists('ini_get') && function_exists('ini_set'))
			{
				if (ini_get('zlib.output_compression'))
				{
					ini_set('zlib.output_compression', 'Off');
				}
			}

			if (function_exists('ini_get') && function_exists('set_time_limit'))
			{
				if (!ini_get('safe_mode'))
				{
					@set_time_limit(0);
				}
			}

			@ob_end_clean();
			@clearstatcache();
			$headers = [
				'Content-Type'              => 'application/zip',
				'Expires'                   => '0',
				'Pragma'                    => 'no-cache',
				'Cache-Control'             => 'must-revalidate, post-check=0, pre-check=0',
				'Content-Length'            => filesize($file),
				'Content-Disposition'       => 'attachment; filename="' . File::makeSafe(basename($file)) . '"',
				'Content-Transfer-Encoding' => 'binary',
				'Accept-Ranges'             => 'bytes',
				'Connection'                => 'close',
			];

			foreach ($headers as $name => $value)
			{
				$app->setHeader($name, $value);
			}

			$app->sendHeaders();
			flush();

			$blockSize = 1048576; //1M chunks
			$handle    = @fopen($file, 'r');

			if ($handle !== false)
			{
				while (!@feof($handle))
				{
					echo @fread($handle, $blockSize);
					@ob_flush();
					flush();
				}
			}

			if ($handle !== false)
			{
				@fclose($handle);
			}

			File::delete($file);
			$app->close();
		}

		$app->redirect(Route::_('index.php?option=com_easyshop&view=products', false));
	}
}
