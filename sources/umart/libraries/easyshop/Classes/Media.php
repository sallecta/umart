<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ES\Classes;
defined('_JEXEC') or die;

use InvalidArgumentException;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Image\Image;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Path;
use stdClass;

class Media
{
	protected $imageSizes = [];
	protected $imageLazyResize = false;

	public function __construct()
	{
		$config = ComponentHelper::getParams('com_easyshop');

		if ($config->get('image_lazy_resize', '0'))
		{
			$this->imageLazyResize = true;
		}

		$this->imageSizes = [
			'tinySize'   => $config->get('image_tiny_size', '150x0'),
			'smallSize'  => $config->get('image_small_size', '250x0'),
			'mediumSize' => $config->get('image_medium_size', '450x0'),
			'largeSize'  => $config->get('image_large_size', '850x0'),
			'xlargeSize' => $config->get('image_xlarge_size', '1200x0'),
		];
	}

	public function delete($filePath, $force = false)
	{
		$user         = easyshop(User::class);
		$products     = $this->getProductsByFile($filePath);
		$responseData = [
			'success' => false,
			'message' => null,
		];

		if (!$force && !$user->core('delete'))
		{
			$responseData['message'] = Text::_('JERROR_ALERTNOAUTHOR', true);
		}
		elseif (!empty($products))
		{
			$responseData['message'] = Text::_('COM_EASYSHOP_FILE_WAS_APPLIED_ON_PRODUCTS', true) . ': ';
			$productName             = [];

			foreach ($products as $product)
			{
				$productName[] = '[' . $product->product_name . ']';
			}

			$responseData['message'] .= implode(', ', $productName);
		}
		elseif (!is_file(ES_MEDIA . '/' . $filePath) || !File::delete(ES_MEDIA . '/' . $filePath))
		{
			$responseData['message'] = Text::sprintf('COM_EASYSHOP_REMOVE_FILE_FAIL', basename($filePath));
		}
		else
		{
			$responseData['success'] = true;
			$responseData['message'] = Text::sprintf('COM_EASYSHOP_REMOVE_FILE_SUCCESS', basename($filePath));
		}

		return $responseData;
	}

	public function getProductsByFile($filePath)
	{
		$db    = easyshop('db');
		$query = $db->getQuery(true)
			->select('a.product_id, a2.name AS product_name')
			->from($db->quoteName('#__easyshop_medias', 'a'))
			->innerJoin($db->quoteName('#__easyshop_products', 'a2') . ' ON a2.id = a.product_id')
			->where('a.file_path = ' . $db->quote($filePath));
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	public function getImageSize($originImage, $size)
	{
		$images = $this->getFullImages($originImage);

		return isset($images->{$size}) ? $images->{$size} : null;
	}

	public function getFullImages($originImageBasePath)
	{
		$image                 = new stdClass;
		$image->originBasePath = Path::clean($originImageBasePath, '/');
		$image->image          = ES_MEDIA_URL . '/' . $originImageBasePath;

		if ($this->imageLazyResize)
		{
			$rootUrl = Uri::root(true);
			$sizes   = [
				'tiny',
				'small',
				'medium',
				'large',
				'xlarge',
			];

			foreach ($sizes as $size)
			{
				$imageBasePath = $this->getResizeImageBasePath($originImageBasePath, $size);
				$file          = base64_encode($originImageBasePath);

				if (is_file(JPATH_ROOT . '/' . $imageBasePath))
				{
					$image->{$size} = $rootUrl . '/' . $imageBasePath;
				}
				else
				{
					$image->{$size} = $rootUrl . '/index.php?option=com_easyshop&task=ajax.loadImage&file=' . $file . '&size=' . $size;
				}
			}
		}
		else
		{
			$ext       = '.' . File::getExt($originImageBasePath);
			$fileName  = basename($originImageBasePath, $ext);
			$thumbPath = '/' . dirname($originImageBasePath) . '/thumbs/';

			// @since 1.1.5. B/C
			if (is_file(ES_MEDIA . $thumbPath . $fileName . '_tiny' . $ext))
			{
				$image->tiny = ES_MEDIA_URL . $thumbPath . $fileName . '_tiny' . $ext;
			}
			else
			{
				$image->tiny = ES_MEDIA_URL . $thumbPath . $fileName . '_' . $this->imageSizes['tinySize'] . $ext;
			}

			if (is_file(ES_MEDIA . $thumbPath . $fileName . '_small' . $ext))
			{
				$image->small = ES_MEDIA_URL . $thumbPath . $fileName . '_small' . $ext;
			}
			else
			{
				$image->small = ES_MEDIA_URL . $thumbPath . $fileName . '_' . $this->imageSizes['smallSize'] . $ext;
			}

			if (is_file(ES_MEDIA . $thumbPath . $fileName . '_medium' . $ext))
			{
				$image->medium = ES_MEDIA_URL . $thumbPath . $fileName . '_medium' . $ext;
			}
			else
			{
				$image->medium = ES_MEDIA_URL . $thumbPath . $fileName . '_' . $this->imageSizes['mediumSize'] . $ext;
			}

			if (is_file(ES_MEDIA . $thumbPath . $fileName . '_large' . $ext))
			{
				$image->large = ES_MEDIA_URL . $thumbPath . $fileName . '_large' . $ext;
			}
			else
			{
				$image->large = ES_MEDIA_URL . $thumbPath . $fileName . '_' . $this->imageSizes['largeSize'] . $ext;
			}

			if (is_file(ES_MEDIA . $thumbPath . $fileName . '_xlarge' . $ext))
			{
				$image->xlarge = ES_MEDIA_URL . $thumbPath . $fileName . '_xlarge' . $ext;
			}
			else
			{
				$image->xlarge = ES_MEDIA_URL . $thumbPath . $fileName . '_' . $this->imageSizes['xlargeSize'] . $ext;
			}

		}

		return $image;
	}

	public function getResizeImageBasePath($originImageBasePath, $size, $autoResize = false)
	{
		if (isset($this->imageSizes[$size . 'Size']))
		{
			$sizes = strtolower($this->imageSizes[$size . 'Size']);
		}
		else
		{
			$sizes = $size;
		}

		$cacheBasePath       = str_replace('assets/images', $sizes, Path::clean($originImageBasePath, '/'));
		$cacheFilePath       = JPATH_SITE . '/cache/shopImageThumbs/' . $cacheBasePath;
		$resultImageBasePath = 'cache/shopImageThumbs/' . $cacheBasePath;

		if ($autoResize && !is_file($cacheFilePath))
		{
			$extension    = File::getExt($originImageBasePath);
			$cacheDirPath = dirname($cacheFilePath);

			if (!preg_match('/jpe?g|gif|png|svg|webp/i', $extension) || !is_file(ES_MEDIA . '/' . $originImageBasePath))
			{
				throw new InvalidArgumentException('Denied image type: ' . $extension . ' or file not found.');
			}

			if (!is_dir($cacheDirPath))
			{
				@Folder::create($cacheDirPath);
			}

			$jImage = new Image(ES_MEDIA . '/' . $originImageBasePath);

			list($width, $height) = explode('x', $sizes, 2);

			if ($width && $height)
			{
				// Resize + crop + center
				$jImage = $jImage->resize($width, $height, true, 1);
			}
			else
			{
				// Resize keep ratio
				$jImage = $jImage->resize($width, $height, true, 2);
			}

			$imgProperties = $jImage->getImageFileProperties(ES_MEDIA . '/' . $originImageBasePath);
			$jImage->toFile($cacheFilePath, $imgProperties->type);
			$jImage->destroy();
		}

		return $resultImageBasePath;
	}

	public function getMimeByFile($file)
	{
		if (function_exists('mime_content_type'))
		{
			return mime_content_type($file);
		}

		if (function_exists('finfo_open') && defined('FILEINFO_MIME_TYPE'))
		{
			$fInfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime  = finfo_file($fInfo, $file);
			finfo_close($fInfo);

			return $mime;
		}

		// Fallback. It maybe danger for some fake files, if your host doesn't support mime_content_type and finfo_open Opps... you should be careful with the T-REX cause you are living in Jurassic period.
		$ext      = strtolower(File::getExt($file));
		$mimeMaps = [
			'txt'  => 'text/plain',
			'htm'  => 'text/html',
			'html' => 'text/html',
			'php'  => 'text/html',
			'css'  => 'text/css',
			'js'   => 'application/javascript',
			'json' => 'application/json',
			'xml'  => 'application/xml',
			'swf'  => 'application/x-shockwave-flash',
			'flv'  => 'video/x-flv',

			// images
			'png'  => 'image/png',
			'jpe'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg'  => 'image/jpeg',
			'gif'  => 'image/gif',
			'bmp'  => 'image/bmp',
			'ico'  => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif'  => 'image/tiff',
			'svg'  => 'image/svg+xml',
			'svgz' => 'image/svg+xml',

			// archives
			'zip'  => 'application/zip',
			'rar'  => 'application/x-rar-compressed',
			'exe'  => 'application/x-msdownload',
			'msi'  => 'application/x-msdownload',
			'cab'  => 'application/vnd.ms-cab-compressed',

			// audio/video
			'mp3'  => 'audio/mpeg',
			'mp4'  => 'video/mp4',
			'qt'   => 'video/quicktime',
			'mov'  => 'video/quicktime',

			// adobe
			'pdf'  => 'application/pdf',
			'psd'  => 'image/vnd.adobe.photoshop',
			'ai'   => 'application/postscript',
			'eps'  => 'application/postscript',
			'ps'   => 'application/postscript',

			// ms office
			'doc'  => 'application/msword',
			'rtf'  => 'application/rtf',
			'xls'  => 'application/vnd.ms-excel',
			'ppt'  => 'application/vnd.ms-powerpoint',
			'docx' => 'application/msword',
			'xlsx' => 'application/vnd.ms-excel',
			'pptx' => 'application/vnd.ms-powerpoint',

			// open office
			'odt'  => 'application/vnd.oasis.opendocument.text',
			'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
		];

		if (isset($mimeMaps[$ext]))
		{
			return $mimeMaps[$ext];
		}

		return 'application/octet-stream';
	}
}
