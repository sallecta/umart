<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\System;
use ES\Classes\User;
use ES\Controller\BaseController;
use ES\Helper\Navbar;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Image\Image;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Schema\ChangeSet;
use Joomla\CMS\Session\Session;

class EasyshopControllerSystem extends BaseController
{
	public function postUpdate()
	{
		try
		{
			if (!Session::checkToken('post'))
			{
				throw new RuntimeException(Text::_('JINVALID_TOKEN'));
			}

			CMSFactory::getLanguage()->load('com_installer');
			$url  = $this->app->input->getString('downloadUrl');
			$file = InstallerHelper::downloadPackage($url);

			if (!$file)
			{
				throw new RuntimeException(Text::sprintf('COM_INSTALLER_PACKAGE_DOWNLOAD_FAILED', $url));
			}

			$config    = CMSFactory::getConfig();
			$tmp       = $config->get('tmp_path');
			$package   = InstallerHelper::unpack($tmp . '/' . $file);
			$installer = Installer::getInstance();

			if (!$installer->update($package['dir']))
			{
				throw new RuntimeException(Text::sprintf('COM_INSTALLER_MSG_UPDATE_ERROR', Text::_('COM_INSTALLER_TYPE_TYPE_COMPONENT')));
			}

			InstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

			// Invalidates the cached system file
			if (function_exists('opcache_invalidate'))
			{
				opcache_invalidate(JPATH_PLUGINS . '/system/easyshop/easyshop.php');
			}

			$this->redirectBackPage(Text::sprintf('COM_INSTALLER_MSG_UPDATE_SUCCESS', Text::_('COM_INSTALLER_TYPE_TYPE_COMPONENT')));
		}
		catch (RuntimeException $e)
		{
			$this->redirectBackPage($e->getMessage(), 'error');
		}
	}

	public function fixSchemas()
	{
		$this->checkToken('get');
		$user = easyshop(User::class);

		if (!$user->core('admin', 'com_easyshop', false))
		{
			$user->stop();
		}

		$db = easyshop('db');

		try
		{
			$changeSet = ChangeSet::getInstance($db, ES_COMPONENT_ADMINISTRATOR . '/sql/updates/');
			$changeSet->fix();
		}
		catch (RuntimeException $e)
		{
			easyshop('app')->enqueueMessage($e->getMessage(), 'warning');
		}

		if (easyshop(System::class)->fixSchemas()
			&& is_dir(ES_COMPONENT_ADMINISTRATOR . '/sql/updates/mysql')
		)
		{
			$files = Folder::files(ES_COMPONENT_ADMINISTRATOR . '/sql/updates/mysql', '[0-9\.]+\.sql$');

			if ($files)
			{
				$versionId = '1.0.0';

				foreach ($files as $file)
				{
					$file = basename($file, '.sql');

					if (version_compare($file, $versionId, '>'))
					{
						$versionId = $file;
					}
				}

				$query = $db->getQuery(true)
					->update($db->quoteName('#__schemas'))
					->set($db->quoteName('version_id') . ' = ' . $db->quote($versionId))
					->where($db->quoteName('extension_id') . ' = ' . $db->quote(ComponentHelper::getComponent('com_easyshop')->id));
				$db->setQuery($query)
					->execute();
			}
		}

		$this->setRedirect(Route::_('index.php?option=com_easyshop&view=system', false));
	}

	public function regenerateThumbnails()
	{
		$this->checkToken('post');

		if (function_exists('ini_get'))
		{
			if (function_exists('ini_set') && ini_get('zlib.output_compression'))
			{
				ini_set('zlib.output_compression', 'Off');
			}

			if (function_exists('set_time_limit') && !ini_get('safe_mode'))
			{
				@set_time_limit(0);
			}
		}

		ob_implicit_flush(true);
		ob_end_flush();
		$mediaPath = ES_MEDIA . '/assets/images';

		if (Folder::exists($mediaPath))
		{
			@Path::setPermissions($mediaPath, '0644', '0755');
			$config = easyshop('config');

			if ($config->get('image_lazy_resize', '0'))
			{
				$thumbDirs = Folder::folders($mediaPath, '^thumbs$', true, true);
				$total     = count($thumbDirs);

				foreach ($thumbDirs as $i => $thumbDir)
				{
					if (@Folder::delete($thumbDir))
					{
						$this->flush([
							'type'     => 'Response',
							'message'  => ($i + 1) . '/' . $total . ' thumbs dirs are removed',
							'progress' => round(($i / $total) * 100, 2),
						]);
					}
					else
					{
						$this->flush([
							'type'     => 'Error',
							'message'  => 'Cannot remove thumb folder: ' . $thumbDir,
							'progress' => round(($i / $total) * 100, 2),
						]);
					}
				}
			}
			else
			{
				if (is_dir(JPATH_SITE . '/cache/shopImageThumbs'))
				{
					@Folder::delete(JPATH_SITE . '/cache/shopImageThumbs');
				}

				$images = Folder::files($mediaPath, 'jpe?g|png|gif|JPE?G|PNG|GIF', true, true, ['thumbs', '.svn', 'CVS', '.DS_Store', '__MACOSX']);
				$total  = count($images);

				if ($total > 0)
				{
					$count         = 0;
					$tinySize      = $config->get('image_tiny_size', '150x0');
					$smallSize     = $config->get('image_small_size', '250x0');
					$mediumSize    = $config->get('image_medium_size', '450x0');
					$largeSize     = $config->get('image_large_size', '850x0');
					$xlargeSize    = $config->get('image_xlarge_size', '1200x0');
					$thumbSizeMaps = [
						$tinySize   => 'tiny',
						$smallSize  => 'small',
						$mediumSize => 'medium',
						$largeSize  => 'large',
						$xlargeSize => 'xlarge',
					];

					foreach ($images as $i => $image)
					{
						$jImage       = new Image($image);
						$thumbsFolder = dirname($image) . '/thumbs';

						if (!Folder::exists($thumbsFolder) && !Folder::create($thumbsFolder, 0755))
						{
							continue;
						}

						$count++;

						foreach ($thumbSizeMaps as $size => $suffixName)
						{
							list($thumbWidth, $thumbHeight) = explode('x', $size);
							$scaleMethod = (float) $thumbWidth > 0.00 && (float) $thumbHeight > 0.00 ? 1 : 2;

							if ($thumbs = $jImage->generateThumbs([$size], $scaleMethod))
							{
								$imgProperties = $jImage->getImageFileProperties($image);
								$thumb         = $thumbs[0];
								$filename      = pathinfo($image, PATHINFO_FILENAME);
								$fileExtension = pathinfo($image, PATHINFO_EXTENSION);
								$thumbFileName = $filename . '_' . $suffixName . '.' . $fileExtension;

								if ($thumb->toFile($thumbsFolder . '/' . $thumbFileName, $imgProperties->type))
								{
									$thumb->destroy();
								}
							}
						}

						$jImage->destroy();
						$this->flush([
							'type'     => 'response',
							'message'  => $count . '/' . $total . ' image thumbnails were regenerated',
							'progress' => round(($i / $total) * 100, 2),
						]);
					}
				}
			}
		}

		$this->app->close();
	}

	protected function flush($response)
	{
		echo '[' . json_encode($response) . str_pad('', 1024, ' ') . ']';

		ob_flush();
		flush();
		usleep(1);
	}

	public function loadNavigation()
	{
		JLoader::import('helpers.navbar', ES_COMPONENT_ADMINISTRATOR);
		echo new JsonResponse(Navbar::render());

		$this->app->close();
	}
}
