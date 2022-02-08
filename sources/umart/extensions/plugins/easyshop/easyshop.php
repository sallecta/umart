<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use ES\Classes\Currency;
use ES\Classes\Html;
use ES\Classes\Params;
use ES\Classes\Privacy;
use ES\Classes\Renderer;
use ES\Classes\Router;
use ES\Classes\Translator;
use ES\Factory;
use ES\Helper\Navbar;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

class PlgSystemEasyshop extends CMSPlugin
{
	/**
	 * @var  CMSApplication $app
	 * @since 1.0.0
	 */
	protected $app;

	public function onAfterInitialise()
	{
		self::defines();
		JLoader::import('easyshop.Loader');
		HTMLHelper::addIncludePath(ES_COMPONENT_ADMINISTRATOR . '/helpers/html');

		// For Joomla! 3.9 privacy
		if (version_compare(JVERSION, '3.9', 'ge')
			&& ComponentHelper::isEnabled('com_privacy')
			&& class_exists(Privacy::class)
		)
		{
			$dispatcher = ES_DETECT_JVERSION === 4 ? $this->getDispatcher() : $this->_subject;
			new Privacy($dispatcher);
		}
	}

	public static function defines()
	{
		if (!defined('ES_VERSION'))
		{
			define('ES_VERSION', '1.4.1');
		}

		if (!defined('ES_VERSION_HASH'))
		{
			define('ES_VERSION_HASH', md5(ES_VERSION));
		}

		if (!defined('ES_DETECT_JVERSION'))
		{
			define('ES_DETECT_JVERSION', version_compare(JVERSION, '4.0', 'ge') ? 4 : 3);
		}

		if (!defined('IS_JOOMLA_V4'))
		{
			define('IS_JOOMLA_V4', ES_DETECT_JVERSION === 4);
		}

		if (!defined('ES_COMPONENT'))
		{
			define('ES_COMPONENT', JPATH_BASE . '/components/com_easyshop');
		}

		if (!defined('ES_COMPONENT_SITE'))
		{
			define('ES_COMPONENT_SITE', JPATH_SITE . '/components/com_easyshop');
		}

		if (!defined('ES_COMPONENT_ADMINISTRATOR'))
		{
			define('ES_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_easyshop');
		}

		if (!defined('ES_MEDIA'))
		{
			define('ES_MEDIA', JPATH_ROOT . '/media/com_easyshop');
		}

		if (!defined('ES_MEDIA_URL'))
		{
			define('ES_MEDIA_URL', Uri::root(true) . '/media/com_easyshop');
		}

		if (!defined('ES_LIBRARIES'))
		{
			define('ES_LIBRARIES', JPATH_LIBRARIES . '/easyshop');
		}

		if (!defined('ES_ORDER_CREATED'))
		{
			define('ES_ORDER_CREATED', 0);
		}

		if (!defined('ES_ORDER_CONFIRMED'))
		{
			define('ES_ORDER_CONFIRMED', 1);
		}

		if (!defined('ES_ORDER_PROCESSED'))
		{
			define('ES_ORDER_PROCESSED', 2);
		}

		if (!defined('ES_ORDER_SHIPPED'))
		{
			define('ES_ORDER_SHIPPED', 3);
		}

		if (!defined('ES_ORDER_SUCCEED'))
		{
			define('ES_ORDER_SUCCEED', 4);
		}

		if (!defined('ES_ORDER_CANCELLED'))
		{
			define('ES_ORDER_CANCELLED', 5);
		}

		if (!defined('ES_ORDER_ARCHIVED'))
		{
			define('ES_ORDER_ARCHIVED', 6);
		}

		if (!defined('ES_ORDER_TRASHED'))
		{
			define('ES_ORDER_TRASHED', -2);
		}

		if (!defined('ES_PAYMENT_PAID'))
		{
			define('ES_PAYMENT_PAID', 1);
		}

		if (!defined('ES_PAYMENT_UNPAID'))
		{
			define('ES_PAYMENT_UNPAID', 0);
		}

		if (!defined('ES_PAYMENT_REFUND'))
		{
			define('ES_PAYMENT_REFUND', 2);
		}
	}

	public function onAfterRoute()
	{
		try
		{
			if (!ComponentHelper::isEnabled('com_easyshop'))
			{
				if (IS_JOOMLA_V4)
				{
					$this->getDispatcher()->clearListeners();
				}
				else
				{
					$this->_subject->detach($this);
				}

				$db    = CMSFactory::getDbo();
				$query = $db->getQuery(true)
					->update($db->quoteName('#__extensions'))
					->set($db->quoteName('enabled') . ' = 0')
					->set($db->quoteName('protected') . ' = 0')
					->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
					->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
					->where($db->quoteName('element') . ' = ' . $db->quote('easyshop'));

				return $db->setQuery($query)->execute();
			}

			if (easyshop('config', 'user_redirect_form', 0))
			{
				$task = strtolower($this->app->input->get('task', 'display', 'cmd'));
				$view = strtolower($this->app->input->get('view', '', 'cmd'));

				if ($task === 'display'
					&& in_array($view, ['login', 'registration'])
					&& !CMSFactory::getUser()->id
				)
				{
					require_once ES_COMPONENT_SITE . '/helpers/route.php';
					$this->app->redirect(Route::_(EasyshopHelperRoute::getCustomerRoute(), false));
				}
			}

			$this->framework();
			easyshop(Router::class)->execute();
		}
		catch (RuntimeException $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'error');
		}
	}

	protected function framework()
	{
		if (IS_JOOMLA_V4)
		{
			$this->app->loadDocument();
		}

		$document = CMSFactory::getDocument();
		$language = CMSFactory::getLanguage();
		$language->load('com_easyshop', ES_COMPONENT_ADMINISTRATOR, null, false, true)
		|| $language->load('com_easyshop', JPATH_ADMINISTRATOR, null, false, true);
		HTMLHelper::_('easyshop.framework');
		Factory::getInstance()->addLangText(
			[
				'COM_EASYSHOP_INPUT_INVALID_REQUIRED',
				'COM_EASYSHOP_INPUT_INVALID_MIN',
				'COM_EASYSHOP_INPUT_INVALID_MAX',
				'COM_EASYSHOP_INPUT_INVALID_REGEX',
				'COM_EASYSHOP_INPUT_INVALID_EMAIL',
				'COM_EASYSHOP_INPUT_INVALID_NUMBER',
			]
		);
		$uri           = Uri::getInstance();
		$root          = $uri->root();
		$pathRoot      = $uri->root(true);
		$base          = $uri->base();
		$pathBase      = $uri->base(true);
		$current       = $uri->toString();
		$currentBase   = base64_encode($current);
		$currencyClass = easyshop(Currency::class)->getActive();
		$config        = easyshop('config');
		$mediaSets     = [];
		$currency      = [
			'format'    => $currencyClass->get('format'),
			'symbol'    => $currencyClass->get('symbol'),
			'decimals'  => $currencyClass->get('decimals'),
			'separator' => $currencyClass->get('separator'),
			'point'     => $currencyClass->get('point'),
			'code'      => $currencyClass->get('code'),
		];

		if ($config->get('image_lazy_load', '0'))
		{
			$sizeOnScreens = [
				'imagesize_xsmall_screen' => [0, 539, ''],
				'imagesize_small_screen'  => [540, 959, ''],
				'imagesize_medium_screen' => [960, 1199, ''],
				'imagesize_large_screen'  => [1200, 1599, ''],
				'imagesize_xlarge_screen' => [1600, 0, ''],
			];

			foreach ($sizeOnScreens as $name => $mediaCondition)
			{
				if ($size = $config->get($name, ''))
				{
					$mediaCondition[2] = $size;
					$mediaSets[]       = $mediaCondition;
				}
			}
		}

		$jsData = [
			'uri'       => [
				'current'     => $current,
				'currentBase' => $currentBase,
				'base'        => $base,
				'pathBase'    => $pathBase,
				'root'        => $root,
				'pathRoot'    => $pathRoot,
				'input'       => $this->app->input->getArray(),
			],
			'currency'  => $currency,
			'jVersion'  => ES_DETECT_JVERSION,
			'esVersion' => ES_VERSION,
			'mediaSets' => $mediaSets,
			'token'     => Session::getFormToken(),
		];

		$document->addScriptDeclaration('_es.setData(' . json_encode($jsData) . ');');

		if ($document->getType() == 'html' && !is_writable(ES_MEDIA))
		{
			$this->app->enqueueMessage(Text::sprintf('COM_EASYSHOP_DIRECTORY_IS_NOT_WRITABLE_FORMAT', str_replace(JPATH_ROOT, '', ES_MEDIA)), 'warning');
		}
	}

	public function onAfterDispatch()
	{
		$document = CMSFactory::getDocument();

		if (easyshop('administrator')
			&& $this->isCategories()
			&& $document->getType() === 'html'
		)
		{
			PluginHelper::importPlugin('easyshop');

			if ($this->app->input->get('layout', 'default') == 'default')
			{
				JLoader::import('helpers.navbar', ES_COMPONENT_ADMINISTRATOR);
				$buffer = $document->getBuffer('component');
				$navbar = Navbar::render();
				$dom    = new DOMDocument('1.0', 'UTF-8');
				$string = '<?xml encoding="utf-8" ?><div id="es-component" class="es-category es-scope uk-scope">' . $navbar . '<div id="es-body" class="uk-width-3-4@m uk-width-4-5@xl uk-width-2-3@s">' . $buffer . '</div></div>';

				if (@$dom->loadHTML($string))
				{
					if ($componentArea = $dom->getElementById('es-component'))
					{
						if ($child = $dom->getElementById('j-sidebar-container'))
						{
							$child->parentNode->removeChild($child);
						}

						if ($child = $dom->getElementById('j-main-container'))
						{
							$child->setAttribute('id', 'es-main-container');
							$child->setAttribute('class', 'es-main-container');
						}

						$componentBuffer = $dom->saveHTML($componentArea);

						if (false !== $componentBuffer)
						{
							$document->setBuffer($componentBuffer, 'component');
						}
					}
				}

				easyshop(Html::class)->addJs('category-fallback.js', ES_VERSION_HASH);
			}

			$this->app->triggerEvent('onEasyshopCategoryAfterDispatch');
		}
	}

	protected function isCategories()
	{
		$option    = $this->app->input->get('option');
		$extension = $this->app->input->get('extension');
		easyshop(Html::class)->initChosen();

		return in_array($option, ['com_categories', 'com_easyshop'])
			&& strpos($extension, 'com_easyshop') !== false;
	}

	public function onEasyshopBeforeDispatch()
	{
		$view = $this->app->input->get('view', 'dashboard');

		if ($view == 'dashboard' || $view == 'methods')
		{
			easyshop('doc')->addScriptDeclaration('
				_es.$(document).ready(function($){
					$(\'#es-navbar-content .uk-tab li\').removeClass(\'uk-active\');
					$(\'#es-navbar-content .uk-tab > li:eq(0)\').addClass(\'uk-active\');
				});
			');
		}
	}

	public function onContentPrepareForm($form, $data)
	{
		JLoader::register('EasyshopHelper', ES_COMPONENT_ADMINISTRATOR . '/helpers/easyshop.php');
		/** @var Params $params */
		$params     = easyshop(Params::class);
		$extension  = $this->app->input->get('extension');
		$formName   = $form->getName();
		$isCategory = $this->isCategories();
		$registry   = new Registry($data);

		if ($formName == 'com_plugins.plugin')
		{
			$folder = $registry->get('folder');

			if (strpos($folder, 'easyshop') === 0)
			{
				PluginHelper::importPlugin($folder);
			}
		}
		elseif ($isCategory)
		{
			if ($extension == 'com_easyshop.product')
			{
				$form->loadFile(ES_COMPONENT_ADMINISTRATOR . '/models/forms/category/icon.xml');
				$form->load($params->getData('product_listing'));
			}

			if (Multilanguage::isEnabled())
			{
				$options = [
					'ajaxUrl' => Uri::root(true) . '/index.php?option=com_easyshop&task=ajax.loadCategoryMultiLanguageTabs',
					'refKey'  => $registry->get('id', $form->getValue('id')),
				];

				CMSFactory::getDocument()->addScriptOptions('com_easyshop.multiLanguage', $options);
				HTMLHelper::_('behavior.core');
				HTMLHelper::_('stylesheet', 'com_easyshop/multi-language.css', ['relative' => true, 'version' => 'auto']);
				HTMLHelper::_('script', 'com_easyshop/multi-language.js', ['relative' => true, 'version' => 'auto']);
			}

			PluginHelper::importPlugin('easyshop');
			$this->app->triggerEvent('onEasyshopCategoryPrepareForm', [$form, $data]);
		}

		if ($isCategory && in_array($extension, ['com_easyshop.product.customfield', 'com_easyshop.product.option']))
		{
			$form->loadFile(ES_COMPONENT_ADMINISTRATOR . '/models/forms/category/customfield.xml');
			$form->loadFile(ES_COMPONENT_ADMINISTRATOR . '/models/forms/category/icon.xml');
		}
		elseif ($formName == 'com_menus.item')
		{
			$option = $registry->get('request.option');
			$view   = $registry->get('request.view');

			if ($option == 'com_easyshop')
			{
				PluginHelper::importPlugin('easyshop');
				$this->app->triggerEvent('onEasyshopMenuPrepareForm', [$form, $data]);

				if ($view == 'productlist')
				{
					$form->load($params->getData('product_listing'));
				}
				elseif ($view == 'productdetail')
				{
					$form->load($params->getData('product_detail'));
				}
			}
		}
		elseif ($formName == 'com_modules.module')
		{
			$jform  = $this->app->input->get('jform', [], 'array');
			$module = isset($jform['module']) ? $jform['module'] : $registry->get('module');

			if (strpos($module, 'mod_easyshop') === 0)
			{
				PluginHelper::importPlugin('easyshop');
			}

			if ($module === 'mod_easyshop_products')
			{
				if ($form->load($params->getData('product_listing', 'basic')))
				{
					foreach ([
						         'show_category',
						         'category_description',
						         'show_sub_categories',
						         'product_in_sub_categories',
						         'title_sub_categories',
					         ] as $name)
					{
						$form->setFieldAttribute($name, 'showon', 'product_mode:categories', 'params');
					}

					$form->removeField('show_filter_bar', 'params');
					$form->removeField('show_toggle_button', 'params');
					$form->removeField('product_list_default_ordering', 'params');
					$form->removeField('product_list_default_limit', 'params');
				}
			}
		}
	}

	public function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success)
		{
			return false;
		}

		$userId = ArrayHelper::getValue($user, 'id', 0, 'int');
		Table::addIncludePath(ES_COMPONENT_ADMINISTRATOR . '/tables');

		$userTable = Table::getInstance('User', 'EasyshopTable');

		if ($userId && $userTable->load(['user_id' => $userId]))
		{
			$userTable->delete($userTable->id);
		}
	}

	public function onUserAfterSave($userData, $isNew, $result, $errorMsg)
	{
		if ($result && $isNew)
		{
			Table::addIncludePath(ES_COMPONENT_ADMINISTRATOR . '/tables');
			$userTable = Table::getInstance('User', 'EasyshopTable');
			$userTable->set('user_id', (int) $userData['id']);
			$userTable->set('state', 1);

			if (!$userTable->check() || !$userTable->store())
			{
				$this->app->enqueueMessage(implode(PHP_EOL, $userTable->getErrors()));

				return false;
			}

			easyshop('state')->set('customer.juser_register_id', $userData['id']);
		}
	}

	public function onContentAfterSave($context, $table, $isNew)
	{
		if (0 === strpos($context, 'com_categories.category') && $this->isCategories())
		{
			$form           = Translator::getCategoryForm();
			$validTransData = Translator::validateTranslationsData($form);

			if (false !== $validTransData)
			{
				$requestData = [
					'title'          => $table->title,
					'alias'          => $table->alias,
					'path'           => $table->path,
					'ESTranslations' => $validTransData,
				];

				if ('com_easyshop.product' === $table->extension)
				{
					$categories = Categories::getInstance('easyshop.product');
					$ids        = $categories->get($table->id)->getPath();
					array_pop($ids);

					if (count($ids) > 0)
					{
						foreach ($validTransData as $langCode => $transData)
						{
							if (empty($transData['alias']))
							{
								continue;
							}

							$paths = [];

							foreach ($ids as $id)
							{
								list ($cid, $alias) = explode(':', $id, 2);
								$transDatum = Translator::getTranslationsData('categories', $cid, $langCode);

								if (empty($transDatum['alias']))
								{
									$paths[] = $alias;
								}
								else
								{
									$paths[] = $transDatum['alias'];
								}
							}

							$requestData['ESTranslations'][$langCode]['path'] = implode('/', $paths) . '/' . $transData['alias'];
						}
					}
				}

				Translator::saveTranslations('categories', $table->id, $requestData);
			}

			PluginHelper::importPlugin('easyshop');
			$this->app->triggerEvent('onEasyshopCategoryAfterSave', [$context, $table, $isNew]);
		}
	}

	public function onExtensionAfterSave($context, $table, $isNew)
	{
		if ($context == 'com_plugins.plugin' && in_array($table->folder, ['easyshop', 'easyshoppayment', 'easyshopshipping']))
		{
			PluginHelper::importPlugin($table->folder, $table->element);
			$this->app->triggerEvent('on' . ucfirst($table->folder) . ucfirst($table->element) . 'AfterSave', [$context, $table, $isNew]);
		}
	}

	public function onAfterGetMenuTypeOptions(&$list, $model)
	{
		PluginHelper::importPlugin('easyshop');
		$options = $list['com_easyshop'];
		$this->app->triggerEvent('onEasyshopGetMenuTypeOptions', [&$options, $model]);
		$list['com_easyshop'] = $options;
	}

	public function onEasyshopAfterValidateCheckoutData(&$data)
	{
		if (PluginHelper::isEnabled('easyshop', 'recaptcha'))
		{
			$db    = CMSFactory::getDbo();
			$query = $db->getQuery(true)
				->select('a.manifest_cache')
				->from($db->quoteName('#__extensions', 'a'))
				->where('a.type = ' . $db->quote('plugin'))
				->where('a.folder = ' . $db->quote('easyshop'))
				->where('a.element = ' . $db->quote('recaptcha'));

			if ($manifest = $db->setQuery($query)->loadResult())
			{
				$manifest = new Registry($manifest);
				$version  = $manifest->get('version');

				if (!empty($version) && version_compare($version, '1.0.5', 'lt'))
				{
					throw new RuntimeException('EasyShop version ' . ES_VERSION . ' required ReCaptcha v-1.0.5 or greater.');
				}
			}
		}
	}
}

if (!function_exists('easyshop'))
{
	function easyshop($key = null)
	{
		$factory = Factory::getInstance();
		$args    = func_get_args();

		switch ($key)
		{
			case null:
				return $factory;

			case 'app':
			case 'db':
			case 'doc':
			case 'state':
				return $factory->__get($key);

			case 'site':
			case 'administrator':
				return $factory->__get('app')->getName() === $key;

			case 'renderer':
				$config   = isset($args[1]) && is_array($args[1]) ? $args[1] : [];
				$renderer = new Renderer($config);
				$renderer->refreshDefaultPaths();

				return $renderer;

			case 'dispatch':

				return $factory->dispatch();
		}

		if (false !== strpos($key, '\\'))
		{
			return call_user_func_array([$factory, 'getClass'], $args);
		}

		array_shift($args);

		if (strpos($key, '.') !== false)
		{
			$parts = explode('.', $key, 2);

			if ($parts[0] === 'class')
			{
				array_unshift($args, $parts[1]);

				return call_user_func_array([$factory, 'getClass'], $args);
			}

			if ($parts[0] === 'prepare')
			{
				$data    = $args[0];
				$context = 'com_easyshop' . str_replace([$parts[0], 'com_easyshop.'], '', $key);
				$app     = $factory->__get('app');

				switch (strtolower(gettype($data)))
				{
					case 'array':
						$app->triggerEvent('onEasyshopPrepareItems', [$context, &$data]);
						break;

					case 'object':
						$app->triggerEvent('onEasyshopPrepareItem', [$context, $data]);
						break;

					case 'string':
						$o       = new stdClass;
						$o->text = $data;
						$app->triggerEvent('onEasyshopPrepareContent', [$context, $o]);
						$data = $o->text;
						break;
				}

				return $data;
			}
		}

		$callBack = 'get' . ucfirst($key);

		if (!is_callable([$factory, $callBack]))
		{
			$callBack = $key;
		}

		if (is_callable([$factory, $callBack]))
		{
			return call_user_func_array([$factory, $callBack], $args);
		}
	}
}

if (!function_exists('dd'))
{
	function dd($var)
	{
		ob_end_clean();
		ob_clean();
		echo '<pre>' . print_r($var, true) . '</pre>';

		exit(0);
	}
}