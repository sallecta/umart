<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
JLoader::import('helpers.media', UMART_COMPONENT_ADMINISTRATOR);

use Umart\Classes\Media;
use Umart\Classes\User;
use Umart\Controller\BaseController;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\Image\Image;

class UmartControllerMedia extends BaseController
{
	/**
	 * @var $user User
	 * @since 1.0.0
	 */
	protected $user;

	public function __construct(array $config = array())
	{
		parent::__construct($config);
		$this->user = plg_sytem_umart_main(User::class);
		$this->user->load();
	}

	public function upload()
	{
		Session::checkToken('get') or jexit(Text::_('JINVALID_TOKEN'));
		$type = $this->input->getWord('media_type', 'image');

		if ($type === 'file')
		{
			$userPath = $this->getUserPath($type);

			if (!$this->core('create', $userPath))
			{
				throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR', true));
			}

			plg_sytem_umart_main('app')->triggerEvent('onUmartFileUpload', [$userPath]);
		}
		else
		{
			$this->uploadMedia();
		}
	}

	protected function getUserPath($type = 'image')
	{
		$userPath = UMART_MEDIA . '/assets/' . $type . 's';
		$basePath = trim($this->input->get('media_path', '', 'path'), '/');

		if (plg_sytem_umart_main('site')
			&& !$this->user->core('admin')
		)
		{
			$userPath .= '/user_customers/' . $this->user->get()->id;
		}

		if (!empty($basePath))
		{
			$userPath .= '/' . $basePath;
		}

		return $userPath;
	}

	protected function core($core, $userPath = null)
	{
		$userId = (int) $this->user->get()->id;

		if ($userPath && $userId < 1)
		{
			return false;
		}

		if (plg_sytem_umart_main('site'))
		{
			if (!$this->user->core('admin')
				&& !$this->user->isCustomer()
			)
			{
				return false;
			}

			if (!$this->user->core('admin'))
			{
				$imagePath = UMART_MEDIA . '/assets/images';
				$filePath  = UMART_MEDIA . '/assets/files';

				if ((strpos($userPath, $imagePath) === 0 && strpos($userPath, $imagePath . '/user_customers/' . $userId) !== 0)
					|| (strpos($userPath, $filePath) === 0 && strpos($userPath, $filePath . '/user_customers/' . $userId) !== 0)
				)
				{
					// This path is not yours
					return false;
				}

				if ($core === 'create')
				{
					if (null === $userPath)
					{
						return false;
					}

					if (Folder::exists($userPath))
					{
						$files        = Folder::files($userPath, 'jpg|jpeg|png|gif|svg|ico|JPG|JPEG|PNG|GIF|SVG|ICO', true, false, ['.svn', 'CVS', '.DS_Store', '__MACOSX', 'thumbs']);
						$maxFiles     = (int) plg_sytem_umart_main('config', 'customer_max_upload', 5);
						$userMaxFiles = $this->user->getParam('maxUploadImages', false);

						if (is_numeric($userMaxFiles))
						{
							$maxFiles = (int) $userMaxFiles;
						}

						if (count($files) >= $maxFiles)
						{
							throw new RuntimeException(Text::sprintf('COM_UMART_CUSTOMER_MAX_FILE_UPLOAD_ERROR', $maxFiles));
						}
					}
				}
			}

			return true;
		}

		return $this->user->core($core);
	}

	protected function uploadMedia()
	{
		try
		{
			$isThumb  = (int) $this->input->getUint('thumb', 1);
			$userPath = $this->getUserPath();

			if (!$this->core('create', $userPath))
			{
				throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR', true));
			}

			if (!Folder::exists($userPath))
			{
				Folder::create($userPath, 0755);
			}

			$files        = $_FILES['files'];
			$config       = plg_sytem_umart_main('config');
			$tinySize     = strtolower($config->get('image_tiny_size', '150x0'));
			$smallSize    = strtolower($config->get('image_small_size', '250x0'));
			$mediumSize   = strtolower($config->get('image_medium_size', '450x0'));
			$largeSize    = strtolower($config->get('image_large_size', '850x0'));
			$xlargeSize   = strtolower($config->get('image_xlarge_size', '1200x0'));
			$maxSize      = (int) $config->get('upload_max_size', 2) * 1000000;
			$originSize   = trim($config->get('image_origin_size', ''));
			$lazyResize   = $config->get('image_lazy_resize', '0');
			$originWidth  = null;
			$originHeight = null;

			if (!empty($originSize) && preg_match('/^[0-9\.?]+x[0-9\.?]+$/', $originSize))
			{
				list($originWidth, $originHeight) = explode('x', $originSize, 2);
			}

			if ($isThumb && !$lazyResize)
			{
				$thumbSizes = [
					$tinySize,
					$smallSize,
					$mediumSize,
					$largeSize,
					$xlargeSize,
				];
			}
			else
			{
				$thumbSizes = [];
			}

			// @since 1.1.5
			$thumbSizeMaps = [
				$tinySize   => 'tiny',
				$smallSize  => 'small',
				$mediumSize => 'medium',
				$largeSize  => 'large',
				$xlargeSize => 'xlarge',
			];

			if (!Folder::exists($userPath))
			{
				throw new RuntimeException(Text::sprintf('COM_UMART_DIRECTORY_NOT_EXISTS', basename($userPath)));
			}

			/** @var Media $mediaClass */
			$mediaClass   = plg_sytem_umart_main(Media::class);
			$thumbsFolder = $userPath . '/thumbs';

			if (!$lazyResize && !Folder::exists($thumbsFolder))
			{
				Folder::create($thumbsFolder, 0755);
			}

			foreach ($files['name'] as $i => $file)
			{
				if ($files['size'][$i] > $maxSize)
				{
					throw new RuntimeException(Text::sprintf('COM_UMART_MEDIA_UPLOAD_ERROR_MAX_SIZE', $maxSize / 1000000));
				}

				if ($files['error'][$i] || !File::upload($files['tmp_name'][$i], $userPath . '/' . $file))
				{
					throw new RuntimeException(Text::sprintf('COM_UMART_MEDIA_UPLOAD_ERROR', $files['error'][$i]));
				}

				$mime    = $mediaClass->getMimeByFile($userPath . '/' . $file);
				$isImage = strpos($mime, 'image') === 0;
				$isVideo = strpos($mime, 'video') === 0;

				if (!$isImage && !$isVideo)
				{
					File::delete($userPath . '/' . $file);
					continue;
				}

				if ($isVideo)
				{
					continue;
				}

				// @since 1.1.6
				if ($lazyResize
					&& null === $originWidth
					&& null === $originHeight
				)
				{
					continue;
				}

				/** @var $jImage Image */
				$jImage = new JImage($userPath . '/' . $file);

				if ($originWidth || $originHeight)
				{
					if ($originWidth && $originHeight)
					{
						// Resize + crop + center
						$jImage->resize($originWidth, $originHeight, false, 1);
					}
					else
					{
						// Resize keep ratio
						$jImage->resize($originWidth, $originHeight, false, 2);
					}

					$jImage->toFile($userPath . '/' . $file);
				}

				$imagePath = $jImage->getPath();

				foreach ($thumbSizes as $thumbSize)
				{
					list($thumbWidth, $thumbHeight) = explode('x', $thumbSize);
					$scaleMethod = (float) $thumbWidth > 0.00 && (float) $thumbHeight > 0.00 ? 1 : 2;

					if ($thumbs = $jImage->generateThumbs([$thumbSize], $scaleMethod))
					{
						$imgProperties = $jImage->getImageFileProperties($imagePath);
						$thumb         = $thumbs[0];
						$filename      = pathinfo($imagePath, PATHINFO_FILENAME);
						$fileExtension = pathinfo($imagePath, PATHINFO_EXTENSION);
						$thumbFileName = $filename . '_' . $thumbSizeMaps[$thumbSize] . '.' . $fileExtension;

						if ($thumb->toFile($thumbsFolder . '/' . $thumbFileName, $imgProperties->type))
						{
							$thumb->destroy();
						}
					}
				}

				$jImage->destroy();
			}

			$view = $this->getView('Media', 'html', 'UmartView');

			ob_start();
			$view->display();
			$response = ob_get_clean();
		}
		catch (RuntimeException $e)
		{
			$response = $e;
		}

		echo new JResponseJson($response);

		$this->app->close();
	}

	public function createFolder()
	{
		try
		{
			$type     = $this->input->getWord('media_type', 'image');
			$userPath = $this->getUserPath($type);
			$dirName  = preg_replace('/\/|\\|\\\\|\s+/', '', trim($this->input->getString('dirName')));

			if (!$this->core('create', $userPath))
			{
				throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR', true));
			}

			if (empty($dirName))
			{
				throw new RuntimeException(Text::_('COM_UMART_ERROR_EMPTY_DIR_NAME'));
			}

			$dir = $userPath . '/' . $dirName;

			if (is_dir($dir))
			{
				throw new RuntimeException(Text::sprintf('COM_UMART_ERROR_FOLDER_EXISTS', $dirName));
			}

			if (!Folder::create($dir, 0755))
			{
				throw new RuntimeException(Text::_('COM_UMART_CREATED_FOLDER_FAIL'));
			}

			$view = $this->getView('Media', 'html', 'UmartView');

			ob_start();
			$view->display();

			echo new JResponseJson(ob_get_clean(), Text::sprintf('COM_UMART_CREATED_FOLDER_SUCCESS', $dirName));

		}
		catch (RuntimeException $e)
		{
			echo new JResponseJson($e);
		}

		$this->app->close();
	}

	public function removeFolder()
	{
		JSession::checkToken('get') or jexit(Text::_('JINVALID_TOKEN'));
		$responseData = [
			'error'   => true,
			'message' => '',
		];
		$type         = $this->input->getWord('media_type', 'image');
		$userPath     = $this->getUserPath($type);
		$dirName      = preg_replace('/\/|\\|\\\\|\s+/', '', trim($this->input->getString('dirName')));
		$folder       = $userPath . '/' . $dirName;

		if (!$this->core('delete', $folder))
		{
			$responseData['message'] = Text::_('JERROR_ALERTNOAUTHOR', true);
		}
		else
		{
			if (Folder::exists($folder))
			{
				if (count(Folder::files($folder, '.', true)))
				{
					$responseData['message'] = Text::sprintf('COM_UMART_REMOVE_FOLDER_NOT_EMPTY', $dirName);
				}
				elseif (!Folder::delete($folder))
				{
					$responseData['message'] = Text::sprintf('COM_UMART_ERR_REMOVE_FOLDER_FAIL', $dirName);
				}
				else
				{
					$responseData['error']   = false;
					$responseData['message'] = Text::sprintf('COM_UMART_REMOVE_FOLDER_SUCCESS', $dirName);
				}
			}
		}

		echo new JResponseJson($responseData);

		$this->app->close();
	}

	public function remove()
	{
		try
		{
			if (!JSession::checkToken('get'))
			{
				throw new RuntimeException(Text::_('JINVALID_TOKEN'));
			}

			$type = $this->input->getWord('media_type', 'image');

			if ($type === 'file' && !JPluginHelper::isEnabled('umart', 'file'))
			{
				throw new RuntimeException(Text::_('COM_UMART_PLUGIN_FILE_NOT_ENABLED'));
			}

			$filePath = $this->input->getString('file');

			if (!$this->core('delete', UMART_MEDIA . '/' . $filePath))
			{
				throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'));
			}

			$responseData = plg_sytem_umart_main(Media::class)->delete($filePath, true);
			$message      = $responseData['message'];

			if (!$responseData['success'])
			{
				throw new RuntimeException($message);
			}

			if ($type === 'image')
			{
				$imageExt  = File::getExt($filePath);
				$imageName = basename($filePath, '.' . $imageExt);
				$path      = UMART_MEDIA . '/' . dirname($filePath);
				$config    = plg_sytem_umart_main('config');
				$thumbs    = [
					$imageName . '_' . $config->get('image_tiny_size', '150x0') . '.' . $imageExt,
					$imageName . '_' . $config->get('image_small_size', '250x0') . '.' . $imageExt,
					$imageName . '_' . $config->get('image_medium_size', '450x0') . '.' . $imageExt,
					$imageName . '_' . $config->get('image_large_size', '850x0') . '.' . $imageExt,
					$imageName . '_' . $config->get('image_xlarge_size', '1200x0') . '.' . $imageExt,
				];

				foreach ($thumbs as $thumb)
				{
					if (is_file($path . '/thumbs/' . $thumb))
					{
						@File::delete($path . '/thumbs/' . $thumb);
					}
				}
			}
		}
		catch (RuntimeException $e)
		{
			$responseData = $e;
		}

		echo new JResponseJson($responseData, isset($message) ? $message : null);
		$this->app->close();
	}

	/**
	 *
	 * @deprecated 1.4.0 use Upload media instead
	 * @since      1.3.0
	 */
	protected function uploadImages()
	{
		$this->uploadMedia();
	}
}
