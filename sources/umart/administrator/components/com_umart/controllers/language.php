<?php

/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\User;
use Umart\Controller\BaseController;
use Joomla\Archive\Zip;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;

class UmartControllerLanguage extends BaseController
{
	public function download()
	{
		$this->checkToken('get');
		$zip     = $this->getZip();
		$app     = plg_sytem_umart_main('app');
		$tag     = $app->input->get('tag', 'en-GB', 'string');
		$zipData = [];
		$this->parseZipData(UmartHelper::getAllLanguagesFiles($tag), $zipData);

		if (empty($zipData))
		{
			throw new RuntimeException('Language ' . $tag . ' not exists.');
		}

		if (!is_dir(JPATH_ROOT . '/tmp'))
		{
			Folder::create(JPATH_ROOT . '/tmp', 0755);
		}

		$output = JPATH_ROOT . '/tmp/' . $tag . '.umart-v' . UMART_VERSION . '.zip';

		if (!$zip->create($output, $zipData))
		{
			throw new RuntimeException('Cannot compress zip file.');
		}

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

		$headers = array(
			'Expires'                   => '0',
			'Pragma'                    => 'no-cache',
			'Cache-Control'             => 'must-revalidate, post-check=0, pre-check=0',
			'Content-Length'            => filesize($output),
			'Content-Disposition'       => 'attachment; filename="' . basename($output) . '"',
			'Content-Type'              => 'application/zip',
			'Content-Transfer-Encoding' => 'binary',
			'Accept-Ranges'             => 'bytes',
			'Connection'                => 'close',
		);

		foreach ($headers as $name => $value)
		{
			$app->setHeader($name, $value);
		}

		$app->sendHeaders();
		flush();
		readfile($output);
		@unlink($output);

		$app->close();
	}

	public function checkToken($method = 'post', $redirect = true)
	{
		parent::checkToken($method, $redirect = true);
		$user = plg_sytem_umart_main(User::class);

		if (!$user->core('admin'))
		{
			$user->stop();
		}
	}

	/**
	 * @return Zip
	 * @since 1.1.3
	 */
	protected function getZip()
	{
		$zip = new Zip;

		if (empty($zip))
		{
			throw new RuntimeException('No support Archive Zip');
		}

		return $zip;
	}

	protected function parseZipData($files, &$zipData)
	{
		foreach ($files as $file)
		{
			$localName = str_replace(Path::clean(JPATH_ROOT, '/'), '', Path::clean($file, '/'));
			$file      = [
				'name' => ltrim($localName, '/'),
				'data' => file_get_contents($file),
			];
			$zipData[] = $file;
		}
	}

	public function upload()
	{
		$this->checkToken('post');
		$app  = plg_sytem_umart_main('app');
		$file = $app->input->files->get('package', null, 'raw');
		$tag  = $app->input->getString('tag', 'en-GB');
		$list = [];

		if ($file
			&& !$file['error']
			&& File::getExt($file['name']) == 'zip'
		)
		{
			$fileName = File::makeSafe($file['name']);
			$tmpPath  = JPATH_ROOT . '/tmp/' . $tag . '.umart-v' . UMART_VERSION;

			if (!is_dir($tmpPath))
			{
				Folder::create($tmpPath, 0755);
			}

			if (File::upload($file['tmp_name'], $tmpPath . '/' . $fileName, false, true))
			{
				$zip = $this->getZip();
				$zip->extract($tmpPath . '/' . $fileName, $tmpPath);
				$paths = Folder::folders($tmpPath, $tag, true, true);

				if (!empty($paths))
				{
					$allFiles = UmartHelper::getAllLanguagesFiles('en-GB');

					foreach ($allFiles as &$allFile)
					{
						$allFile = str_replace(Path::clean(JPATH_ROOT, '/'), '', Path::clean($allFile, '/'));
						$allFile = str_replace('/en-GB', '/' . $tag, $allFile);
					}

					foreach ($paths as $path)
					{
						if ($files = Folder::files($path, '\.ini$', false, true))
						{
							foreach ($files as $file)
							{
								$copy = str_replace(Path::clean($tmpPath, '/'), '', Path::clean($file, '/'));
								$dest = JPATH_ROOT . '/' . ltrim($copy, '/');
								$dir  = dirname($dest);

								if (!is_dir($dir))
								{
									Folder::create($dir, 0755);
								}

								if (in_array($copy, $allFiles)
									&& @File::copy($file, $dest)
								)
								{
									$list[] = $copy;
								}
							}
						}
					}
				}
			}

			Folder::delete($tmpPath);
		}

		$this->successRedirect($app, $list);
	}

	protected function successRedirect($app, $list, $type = 'success')
	{
		if ($list)
		{
			$app->enqueueMessage('<ol>' . '<li>' . implode('</li><li>', ArrayHelper::arrayUnique($list)) . '</li>', $type);
		}

		$app->redirect(Route::_('index.php?option=com_umart&view=languages', false));
	}

	public function cloneLanguage()
	{
		$this->checkToken('get');
		$app  = plg_sytem_umart_main('app');
		$tag  = $app->input->getString('tag', 'en-GB');
		$list = [];

		if ($tag !== 'en-GB')
		{
			$db    = plg_sytem_umart_main('db');
			$query = $db->getQuery(true)
				->select('a.*')
				->from($db->quoteName('#__languages', 'a'))
				->where('a.lang_code = ' . $db->quote($tag));
			$db->setQuery($query);

			if ($db->loadResult())
			{
				$allFiles = UmartHelper::getAllLanguagesFiles('en-GB');

				foreach ($allFiles as $file)
				{
					$copy = str_replace(Path::clean(JPATH_ROOT, '/'), '', Path::clean($file, '/'));
					$copy = str_replace('/en-GB', '/' . $tag, $copy);
					$path = JPATH_ROOT . dirname($copy);

					if (!is_dir($path))
					{
						Folder::create($path, 0755);
					}

					if (@File::copy($file, JPATH_ROOT . $copy))
					{
						$list[] = $copy;
					}
				}
			}
		}

		$this->successRedirect($app, $list);
	}

	public function removeLanguage()
	{
		$this->checkToken('get');
		$app  = plg_sytem_umart_main('app');
		$tag  = $app->input->getString('tag', 'en-GB');
		$list = [];

		if ($tag !== 'en-GB')
		{
			$allFiles = UmartHelper::getAllLanguagesFiles($tag);

			foreach ($allFiles as $file)
			{
				if (@File::delete($file))
				{
					$list[] = str_replace(Path::clean(JPATH_ROOT, '/'), '', Path::clean($file, '/'));
				}
			}
		}

		$this->successRedirect($app, $list);
	}

	public function editFile()
	{
		$this->checkToken('post');
		$file = $this->app->input->getString('file');
		$data = $this->app->input->get('jform', [], 'array');

		try
		{
			if (!is_file(JPATH_ROOT . $file))
			{
				throw new RuntimeException(JText::_('COM_UMART_FILE_NOT_FOUND'));
			}

			if (@parse_ini_string($data['file_contents']) === false)
			{
				throw new RuntimeException(JText::_('COM_UMART_ERR_CANNOT_PARSE_INI_FILE'));
			}

			if (File::write(JPATH_ROOT . $file, $data['file_contents']))
			{
				$this->app->enqueueMessage(JText::_('COM_UMART_INI_SAVED_SUCCESSFULLY'), 'success');
			}

		}
		catch (RuntimeException $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'warning');
		}

		$this->app->redirect(Route::_('index.php?option=com_umart&view=language&layout=edit&file=' . urlencode($file), false));
	}
}
