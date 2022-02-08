<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\Media;
use ES\Classes\User;
use ES\Helper\Navbar;
use ES\View\BaseView;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

JLoader::import('helpers.media', ES_COMPONENT_ADMINISTRATOR);

class EasyshopViewMedia extends BaseView
{
	protected $uri;
	protected $directory;
	protected $viewType;
	protected $breadcrumbs = [];
	protected $files = [];
	protected $isFile = false;
	protected $isButtonEditor = false;
	protected $allowedFiles;

	public function display($tpl = null)
	{
		$rootUrl   = Uri::root(true);
		$app       = easyshop('app');
		$type      = $app->input->getWord('media_type', 'image');
		$directory = $app->getUserStateFromRequest('com_easyshop.media_' . $type . '_path', 'media_path', '', 'path');
		$directory = preg_replace('/^\/+/', '', $directory);
		$mediaUrl  = EasyshopHelperMedia::getLink($directory, $type);
		$mediaAjax = EasyshopHelperMedia::getLink($directory, $type, 'media.upload');
		$user      = easyshop(User::class);
		$extraPath = (!empty($directory) ? $directory : '');
		$isFile    = $type === 'file' && PluginHelper::isEnabled('easyshop', 'file');
		$isSite    = easyshop('site');

		if ($isSite)
		{
			$extraPath = rtrim('user_customers/' . $user->get()->id . '/' . $extraPath, '/');
		}

		if ($isFile)
		{
			$basePath     = ES_MEDIA . '/assets/files/' . $extraPath;
			$plugin       = easyshop('plugin', 'File');
			$allowedFiles = $plugin->params->get('allowed_files', 'zip|doc|docx|pdf|xls|txt|gz|gzip|rar|jpg|gif|tar.gz|xlsx|pps|csv|bmp|epg|ico|odg|odp|ods|odt|png|ppt|swf|xcf|wmv|avi|mkv|mp3|ogg|flac|wma|fla|flv|mp4|wav|aac|mov|epub');
		}
		else
		{
			$basePath     = ES_MEDIA . '/assets/images/' . $extraPath;
			$allowedFiles = 'svg|jpg|jpeg|gif|png|webp|SVG|JPG|JPEG|GIF|PNG|mp4|MP4';
			$editor       = $app->input->get('e_id', '', 'string');

			if ($editor && PluginHelper::isEnabled('editors-xtd', 'easyshopimage'))
			{
				$this->isButtonEditor = true;
				easyshop('doc')->addScriptDeclaration(<<<JAVASCRIPT
					_es.$(document).ready(function($) {						
					    $('#es-media-bars .file-selected-insert').on('click', function (e) {
					        e.preventDefault();
					        e.stopPropagation();
					        var mediaContainer = $(this).parents('#es-media-bars').next('#es-media');
					        var selectedImage = mediaContainer.find('.es-file-selected:eq(0)');					       
					            
					        if (selectedImage.length 
					            && window.parent
					            && window.parent.Joomla
					            && window.parent.Joomla.editors
					            && window.parent.Joomla.editors.instances
					            && window.parent.Joomla.editors.instances.hasOwnProperty('{$editor}')
					            && (typeof window.parent.jModalClose === 'function' || typeof window.parent.Joomla.Modal === 'object')
					        ) {					            			            
					            var img = document.createElement('img');
					            img.setAttribute('src', selectedImage.attr('href'));
					            img.setAttribute('alt', mediaContainer.find('.image-alt').val());
					            img.setAttribute('width', mediaContainer.find('.image-width').val());
					            img.setAttribute('height', mediaContainer.find('.image-height').val());				            
					            window.parent.Joomla.editors.instances['{$editor}'].replaceSelection(img.outerHTML);            
					            selectedImage.removeClass('es-file-selected');
					            mediaContainer.find('.image-alt, .image-width, .image-height').val('');
					            
					            if (typeof window.parent.jModalClose === 'function') {					                
					                window.parent.jModalClose();
					            } else {
					               var md = window.parent.Joomla.Modal.getCurrent();
					               md && md.close();
					            }					            
					        }			        
					    });
					});
JAVASCRIPT
				);
			}
		}

		$basePath = rtrim($basePath, '/');
		$paths    = [];

		if (Folder::exists($basePath))
		{
			$exclude = [
				'.svn',
				'CVS',
				'.DS_Store',
				'__MACOSX',
				'.idea',
				'.tmp',
				'.git',
				'thumbs',
			];

			if ($isSite)
			{
				$excludeFilter = [
					'^\..*',
				];
			}
			else
			{
				$excludeFilter = [
					'user_customers',
				];
			}

			$paths = (array) Folder::folders($basePath, '.', false, false, $exclude, $excludeFilter);
			$files = Folder::files($basePath, '.*(' . $allowedFiles . ')$', false, true);

			if ($isFile)
			{
				foreach ($files as &$file)
				{
					$temp       = new stdClass;
					$file       = Path::clean($file, '/');
					$temp->file = str_replace(Path::clean(ES_MEDIA, '/') . '/', '', $file);
					$file       = $temp;
				}

				$this->files = $files;
			}
			else
			{
				// @since 1.1.6
				/** @var Media $mediaClass */
				$mediaClass = easyshop(Media::class);

				foreach ($files as &$file)
				{
					$mime    = $mediaClass->getMimeByFile($file);
					$isVideo = strpos($mime, 'video') === 0;
					$file    = str_replace(Path::clean(ES_MEDIA, '/') . '/', '', $file);

					if ($isVideo)
					{
						$fileObject       = new stdClass;
						$fileObject->type = 'video';
						$fileObject->file = $file;
						$file             = $fileObject;
					}
					else
					{
						$file       = $mediaClass->getFullImages($file);
						$file->type = 'image';
					}
				}

				$this->files = $files;
			}
		}
		elseif (!empty($directory))
		{
			$directory = basename($directory);
		}

		$parentPath = empty($directory) ? '' : $directory . '/';

		foreach ($paths as &$path)
		{
			$p    = $path;
			$path = new CMSObject;
			$path->set('url', EasyshopHelperMedia::getLink($parentPath . $p, $type));
			$path->set('title', $p);
			$path->set('path', $p);
		}

		$breadcrumbs = [];

		if (!empty($directory))
		{
			$breadcrumbs = (array) explode('/', $directory);
			$linkPath    = '';

			foreach ($breadcrumbs as &$breadcrumb)
			{
				$linkPath   .= '/' . $breadcrumb;
				$linkPath   = ltrim($linkPath, '/');
				$breadcrumb = [
					'link'  => EasyshopHelperMedia::getLink($linkPath, $type),
					'title' => $breadcrumb,
				];
			}
		}

		$this->breadcrumbs = $breadcrumbs;
		$this->uri         = new CMSObject;
		$this->uri->set('media.url', $mediaUrl);
		$this->uri->set('media.ajax', $mediaAjax . '&returnAjax=' . base64_encode($mediaAjax));
		$this->directory = new CMSObject;
		$this->directory->set('base', $directory);
		$this->directory->set('path.url', $rootUrl . '/media/com_easyshop/assets/' . ($isFile ? 'files' : 'images'));
		$this->directory->set('list.path', $paths);

		if ($this->getLayout() != 'modal' && easyshop('app')->input->getString('tmpl') != 'component')
		{
			echo Navbar::render();
			$this->addToolbar();
		}

		$this->isFile       = $isFile;
		$this->allowedFiles = $allowedFiles;

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		$user = CMSFactory::getUser();
		ToolbarHelper::title(Text::_('COM_EASYSHOP_MEDIA_MANAGE'));

		if ($user->authorise('core.admin', 'com_easyshop') || $user->authorise('core.options', 'com_easyshop'))
		{
			ToolbarHelper::preferences('com_easyshop');
		}
	}
}
