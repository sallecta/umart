<?php
/**
 * @version        1.1.4
 * @package        plg_system_umartukui
 * @author         JoomTech Team
 * @copyright      Copyright (C) 2015 - 2020 github.com/sallecta/umart All Rights Reserved.
 * @license        GNU General Public License, version 3
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

abstract class JHtmlUmartUi
{
	protected static $tabBuffers = null;
	protected static $tabActive = ['__main'];

	/**
	 * @deprecated 1.0.5 Use iconsFramework instead
	 * @since      1.0.2
	 */

	public static function iconsAwesome()
	{
		self::iconsFramework();
	}

	public static function iconsFramework()
	{
		if (UMARTUI_ICON_URL !== null)
		{
			HTMLHelper::_('stylesheet', UMARTUI_ICON_URL);
		}
	}

	public static function icon($icon)
	{
		if (defined('UMART_VERSION'))
		{
			HTMLHelper::addIncludePath(UMART_COMPONENT_ADMINISTRATOR . '/helpers/html');

			return HTMLHelper::_('umart.icon', $icon);
		}

		if (strpos($icon, 'fa') === 0)
		{
			return '<i class="' . $icon . '"></i>';
		}

		if (strpos($icon, 'icon-') === 0)
		{
			return '<i class="' . $icon . '"></i>';
		}

		return $icon;
	}

	public static function openTab($tabName)
	{
		if (!in_array($tabName, self::$tabActive))
		{
			self::$tabActive[] = $tabName;
		}
	}

	public static function addTab($title, $params = null)
	{
		if (empty(self::$tabActive))
		{
			self::$tabActive = ['__main'];
		}

		$tab = end(self::$tabActive);
		reset(self::$tabActive);

		if (empty(self::$tabBuffers))
		{
			self::$tabBuffers = [];
		}

		if (empty(self::$tabBuffers[$tab]))
		{
			self::$tabBuffers[$tab] = [
				'data'    => [],
				'current' => [],
			];
		}

		$itemParams = is_array($params) ? $params : [];

		if (is_string($params))
		{
			// For B/c
			$itemParams['icon'] = $params;
		}

		self::$tabBuffers[$tab]['current'] = [
			'title'   => $title,
			'params'  => $itemParams,
			'content' => null,
		];

		ob_start();

		return null;
	}

	public static function endTab()
	{
		$tab = end(self::$tabActive);
		reset(self::$tabActive);

		$active             = &self::$tabBuffers[$tab];
		$current            = $active['current'];
		$current['content'] = ob_get_clean();
		$active['data'][]   = $current;
		$active['current']  = [];

		return null;
	}

	public static function renderTab($layout = 'tab-default', $params = [])
	{
		$tab = end(self::$tabActive);
		reset(self::$tabActive);
		array_pop(self::$tabActive);

		if (empty(self::$tabBuffers[$tab]['data']))
		{
			return null;
		}

		$tabs = self::$tabBuffers[$tab]['data'];
		unset(self::$tabBuffers[$tab]);

		return self::tabs($tabs, $layout, $params);
	}

	public static function tabs($tabs, $tabLayout = 'tab-default', $params = [], $responsive = false, $tabState = false)
	{
		static::framework();
		$tabLayout = str_replace('uk-', '', strtolower($tabLayout));

		if ($tabLayout == 'slide')
		{
			$tabLayout = 'accordion';
		}

		$layoutId    = 'umartui.' . (in_array($tabLayout, ['tab-left', 'tab-center', 'tab-right', 'tab-bottom', 'accordion']) ? $tabLayout : 'tab');
		$displayData = [
			'items'  => $tabs,
			'params' => $params,
		];

		static $loadDocument = false;

		if (!$loadDocument)
		{
			$responsive && HTMLHelper::_('stylesheet', 'umartui/tab_responsive.css', [], true);
			$tabState && HTMLHelper::_('script', 'umartui/tabs-state.js', [], true);
			$loadDocument = true;
		}

		return self::render($layoutId, $displayData);
	}

	public static function framework()
	{
		static $framework = false;

		if (!$framework)
		{
			HTMLHelper::_('jquery.framework');
			$plugin     = PluginHelper::getPlugin('system', 'umart');
			$params     = new Registry($plugin->params);
			$app        = Factory::getApplication();
			$isAdmin    = $app->isClient('administrator');
			$umartui      = $params->get('load_umartui', 'local');
			$document   = Factory::getDocument();
			$template   = $app->getTemplate(true);
			$framework  = true;
			$isYooTheme = $template->template == 'yootheme' || $app->input->get('template') === 'yootheme';

			if ($isYooTheme)
			{
				$umartui = null;
			}
			elseif ($isAdmin)
			{
				$umartui = 'local';
			}
			else
			{
				$tplHelperFile  = JPATH_THEMES . '/' . $template->template . '/helper.php';
				$tplHelperClass = 'Tpl' . ucfirst($template->template) . 'Helper';

				if (is_file($tplHelperFile))
				{
					\JLoader::register($tplHelperClass, $tplHelperFile);
				}

				if (class_exists($tplHelperClass))
				{
					$class = new $tplHelperClass;

					if (is_callable([$class, 'disableUkuiFramework'])
						&& true === call_user_func([$class, 'disableUkuiFramework'])
					)
					{
						$umartui = null;
					}
				}
			}

			$uikitCss = 'umartui' . ($document->direction == 'rtl' ? '-rtl' : '') . '.min.css';

			if (!empty($umartui))
			{
				$uikitVersion = '3.6.16';

				if ($umartui === 'cdn')
				{
					$document->addStylesheet('https://cdnjs.cloudflare.com/ajax/libs/umartui/' . $uikitVersion . '/css/' . $uikitCss);
					$document->addScript('https://cdnjs.cloudflare.com/ajax/libs/umartui/' . $uikitVersion . '/js/umartui.min.js');
					$document->addScript('https://cdnjs.cloudflare.com/ajax/libs/umartui/' . $uikitVersion . '/js/umartui_icons.min.js');
				}
				elseif ($umartui === 'local' || (int) $umartui === 1)
				{
					$themeBaseUrl = Uri::base(true) . '/templates/' . $template->template;

					if (is_file(JPATH_THEMES . '/' . $template->template . '/css/' . $uikitCss))
					{
						$document->addStylesheet($themeBaseUrl . '/css/' . $uikitCss, ['version' => 'auto']);
					}
					else
					{
						$document->addStylesheet(UMARTUI_MEDIA_URL . '/css/' . $uikitCss, ['version' => 'v=' . $uikitVersion . '']);
					}

					if (is_file(JPATH_THEMES . '/' . $template->template . '/js/umartui.min.js'))
					{
						$document->addStylesheet($themeBaseUrl . '/js/umartui.min.js', ['version' => 'auto']);
					}
					else
					{
						$document->addScript(UMARTUI_MEDIA_URL . '/js/umartui.min.js', ['version' => 'v=' . $uikitVersion . '']);
					}

					if (is_file(JPATH_THEMES . '/' . $template->template . '/js/umartui_icons.min.js'))
					{
						$document->addStylesheet($themeBaseUrl . '/js/umartui_icons.min.js', ['version' => 'auto']);
					}
					else
					{
						$document->addScript(UMARTUI_MEDIA_URL . '/js/umartui_icons.min.js', ['version' => 'v=' . $uikitVersion . '']);
					}
				}
			}

			if (defined('UMART_VERSION') || $umartui == 'local')
			{
				$appendJS = <<<JAVASCRIPT
				jQuery(document).ready(function ($) {
					if (!$('body > #jt-ui-container').length && typeof UIkit !== 'undefined') {
						var containerUI = document.createElement('div');
						containerUI.setAttribute('id', 'jt-ui-container');
						containerUI.setAttribute('class', 'umartui_scope umart_scope');
						$('body').append(containerUI);						
						UIkit.container = containerUI;
					}
					
					$(document).trigger('initUIContainer');
				});
JAVASCRIPT;
				$document->addScriptDeclaration($appendJS);
			}
		}
	}

	protected static function render($layoutId, $displayData = [])
	{
		$template   = Factory::getApplication()->getTemplate();
		$layoutFile = new FileLayout($layoutId, UMARTUI_PATH . '/layouts', ['component' => 'none']);
		$layoutFile->addIncludePath(JPATH_THEMES . '/' . $template . '/html/layouts');

		return $layoutFile->render($displayData);
	}

	public static function tabState()
	{
		static $loaded = false;

		if (!$loaded)
		{
			$loaded = true;
			Factory::getDocument()
				->addScript(UMARTUI_MEDIA_URL . '/js/tab_state.js');
		}
	}
}
