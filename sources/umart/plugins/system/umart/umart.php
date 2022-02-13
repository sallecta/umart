<?php

 
 
 
 
 


defined('_JEXEC') or die;

use Umart\Classes\Currency;
use Umart\Classes\Html;
use Umart\Classes\Params;
use Umart\Classes\Privacy;
use Umart\Classes\Renderer;
use Umart\Classes\Router;
use Umart\Classes\Translator;
use Umart\Factory;
use Umart\Helper\Navbar;
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

class plgSystemUmart extends CMSPlugin
{
	/**
	 * @var  CMSApplication $app
	 * @since 1.0.0
	 */
	protected $app;

	public function onAfterInitialise()
	{
		self::defines();
		JLoader::import('umart.Loader');
		HTMLHelper::addIncludePath(UMART_COMPONENT_ADMINISTRATOR . '/helpers/html');

		// For Joomla! 3.9 privacy
		if (version_compare(JVERSION, '3.9', 'ge')
			&& ComponentHelper::isEnabled('com_privacy')
			&& class_exists(Privacy::class)
		)
		{
			$dispatcher = UMART_DETECT_JVERSION === 4 ? $this->getDispatcher() : $this->_subject;
			new Privacy($dispatcher);
		}
	}

	public static function defines()
	{
		if (!defined('UMART_VERSION'))
		{
			define('UMART_VERSION', '1.0.0');
		}

		if (!defined('UMART_VERSION_HASH'))
		{
			define('UMART_VERSION_HASH', md5(UMART_VERSION));
		}

		if (!defined('UMART_DETECT_JVERSION'))
		{
			define('UMART_DETECT_JVERSION', version_compare(JVERSION, '4.0', 'ge') ? 4 : 3);
		}

		if (!defined('IS_JOOMLA_V4'))
		{
			define('IS_JOOMLA_V4', UMART_DETECT_JVERSION === 4);
		}

		if (!defined('UMART_COMPONENT'))
		{
			define('UMART_COMPONENT', JPATH_BASE . '/components/com_umart');
		}

		if (!defined('UMART_COMPONENT_SITE'))
		{
			define('UMART_COMPONENT_SITE', JPATH_SITE . '/components/com_umart');
		}

		if (!defined('UMART_COMPONENT_ADMINISTRATOR'))
		{
			define('UMART_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_umart');
		}

		if (!defined('UMART_MEDIA'))
		{
			define('UMART_MEDIA', JPATH_ROOT . '/media/com_umart');
		}

		if (!defined('UMART_MEDIA_URL'))
		{
			define('UMART_MEDIA_URL', Uri::root(true) . '/media/com_umart');
		}

		if (!defined('UMART_LIBRARIES'))
		{
			define('UMART_LIBRARIES', JPATH_LIBRARIES . '/umart');
		}

		if (!defined('UMART_ORDER_CREATED'))
		{
			define('UMART_ORDER_CREATED', 0);
		}

		if (!defined('UMART_ORDER_CONFIRMED'))
		{
			define('UMART_ORDER_CONFIRMED', 1);
		}

		if (!defined('UMART_ORDER_PROCESSED'))
		{
			define('UMART_ORDER_PROCESSED', 2);
		}

		if (!defined('UMART_ORDER_SHIPPED'))
		{
			define('UMART_ORDER_SHIPPED', 3);
		}

		if (!defined('UMART_ORDER_SUCCEED'))
		{
			define('UMART_ORDER_SUCCEED', 4);
		}

		if (!defined('UMART_ORDER_CANCELLED'))
		{
			define('UMART_ORDER_CANCELLED', 5);
		}

		if (!defined('UMART_ORDER_ARCHIVED'))
		{
			define('UMART_ORDER_ARCHIVED', 6);
		}

		if (!defined('UMART_ORDER_TRASHED'))
		{
			define('UMART_ORDER_TRASHED', -2);
		}

		if (!defined('UMART_PAYMENT_PAID'))
		{
			define('UMART_PAYMENT_PAID', 1);
		}

		if (!defined('UMART_PAYMENT_UNPAID'))
		{
			define('UMART_PAYMENT_UNPAID', 0);
		}

		if (!defined('UMART_PAYMENT_REFUND'))
		{
			define('UMART_PAYMENT_REFUND', 2);
		}
	}

	public function onAfterRoute()
	{
		try
		{
			if (!ComponentHelper::isEnabled('com_umart'))
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
					->where($db->quoteName('element') . ' = ' . $db->quote('umart'));

				return $db->setQuery($query)->execute();
			}

			if (plg_sytem_umart_main('config', 'user_redirect_form', 0))
			{
				$task = strtolower($this->app->input->get('task', 'display', 'cmd'));
				$view = strtolower($this->app->input->get('view', '', 'cmd'));

				if ($task === 'display'
					&& in_array($view, ['login', 'registration'])
					&& !CMSFactory::getUser()->id
				)
				{
					require_once UMART_COMPONENT_SITE . '/helpers/route.php';
					$this->app->redirect(Route::_(UmartHelperRoute::getCustomerRoute(), false));
				}
			}

			$this->framework();
			plg_sytem_umart_main(Router::class)->execute();
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
		$language->load('com_umart', UMART_COMPONENT_ADMINISTRATOR, null, false, true)
		|| $language->load('com_umart', JPATH_ADMINISTRATOR, null, false, true);
		HTMLHelper::_('umart.framework');
		Factory::getInstance()->addLangText(
			[
				'COM_UMART_INPUT_INVALID_REQUIRED',
				'COM_UMART_INPUT_INVALID_MIN',
				'COM_UMART_INPUT_INVALID_MAX',
				'COM_UMART_INPUT_INVALID_REGEX',
				'COM_UMART_INPUT_INVALID_EMAIL',
				'COM_UMART_INPUT_INVALID_NUMBER',
			]
		);
		$uri           = Uri::getInstance();
		$root          = $uri->root();
		$pathRoot      = $uri->root(true);
		$base          = $uri->base();
		$pathBase      = $uri->base(true);
		$current       = $uri->toString();
		$currentBase   = base64_encode($current);
		$currencyClass = plg_sytem_umart_main(Currency::class)->getActive();
		$config        = plg_sytem_umart_main('config');
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
			'jVersion'  => UMART_DETECT_JVERSION,
			'esVersion' => UMART_VERSION,
			'mediaSets' => $mediaSets,
			'token'     => Session::getFormToken(),
		];

		$document->addScriptDeclaration('_umart.setData(' . json_encode($jsData) . ');');

		if ($document->getType() == 'html' && !is_writable(UMART_MEDIA))
		{
			$this->app->enqueueMessage(Text::sprintf('COM_UMART_DIRECTORY_IS_NOT_WRITABLE_FORMAT', str_replace(JPATH_ROOT, '', UMART_MEDIA)), 'warning');
		}
	}

	public function onAfterDispatch()
	{
		$document = CMSFactory::getDocument();

		if (plg_sytem_umart_main('administrator')
			&& $this->isCategories()
			&& $document->getType() === 'html'
		)
		{
			PluginHelper::importPlugin('umart');

			if ($this->app->input->get('layout', 'default') == 'default')
			{
				JLoader::import('helpers.navbar', UMART_COMPONENT_ADMINISTRATOR);
				$buffer = $document->getBuffer('component');
				$navbar = Navbar::render();
				$dom    = new DOMDocument('1.0', 'UTF-8');
				$string = '<?xml encoding="utf-8" ?><div id="umart_component" class="umart_category umart_scope umartui_scope">' . $navbar . '<div id="umart_body" class="umartui_width-3-4@m umartui_width-4-5@xl umartui_width-2-3@s">' . $buffer . '</div></div>';

				if (@$dom->loadHTML($string))
				{
					if ($componentArea = $dom->getElementById('umart_component'))
					{
						if ($child = $dom->getElementById('j-sidebar-container'))
						{
							$child->parentNode->removeChild($child);
						}

						if ($child = $dom->getElementById('j-main-container'))
						{
							$child->setAttribute('id', 'umart_main_container');
							$child->setAttribute('class', 'umart_main_container');
						}

						$componentBuffer = $dom->saveHTML($componentArea);

						if (false !== $componentBuffer)
						{
							$document->setBuffer($componentBuffer, 'component');
						}
					}
				}

				plg_sytem_umart_main(Html::class)->addJs('category-fallback.js', UMART_VERSION_HASH);
			}

			$this->app->triggerEvent('onUmartCategoryAfterDispatch');
		}
	}

	protected function isCategories()
	{
		$option    = $this->app->input->get('option');
		$extension = $this->app->input->get('extension');
		plg_sytem_umart_main(Html::class)->initChosen();

		return in_array($option, ['com_categories', 'com_umart'])
			&& strpos($extension, 'com_umart') !== false;
	}

	public function onUmartBeforeDispatch()
	{
		$view = $this->app->input->get('view', 'dashboard');

		if ($view == 'dashboard' || $view == 'methods')
		{
			plg_sytem_umart_main('doc')->addScriptDeclaration('
				_umart.$(document).ready(function($){
					$(\'#umart_navbar-content .uk-tab li\').removeClass(\'umartui_active\');
					$(\'#umart_navbar-content .uk-tab > li:eq(0)\').addClass(\'umartui_active\');
				});
			');
		}
	}

	public function onContentPrepareForm($form, $data)
	{
		JLoader::register('UmartHelper', UMART_COMPONENT_ADMINISTRATOR . '/helpers/umart.php');
		/** @var Params $params */
		$params     = plg_sytem_umart_main(Params::class);
		$extension  = $this->app->input->get('extension');
		$formName   = $form->getName();
		$isCategory = $this->isCategories();
		$registry   = new Registry($data);

		if ($formName == 'com_plugins.plugin')
		{
			$folder = $registry->get('folder');

			if (strpos($folder, 'umart') === 0)
			{
				PluginHelper::importPlugin($folder);
			}
		}
		elseif ($isCategory)
		{
			if ($extension == 'com_umart.product')
			{
				$form->loadFile(UMART_COMPONENT_ADMINISTRATOR . '/models/forms/category/icon.xml');
				$form->load($params->getData('product_listing'));
			}

			if (Multilanguage::isEnabled())
			{
				$options = [
					'ajaxUrl' => Uri::root(true) . '/index.php?option=com_umart&task=ajax.loadCategoryMultiLanguageTabs',
					'refKey'  => $registry->get('id', $form->getValue('id')),
				];

				CMSFactory::getDocument()->addScriptOptions('com_umart.multiLanguage', $options);
				HTMLHelper::_('behavior.core');
				HTMLHelper::_('stylesheet', 'com_umart/multi-language.css', ['relative' => true, 'version' => 'auto']);
				HTMLHelper::_('script', 'com_umart/multi-language.js', ['relative' => true, 'version' => 'auto']);
			}

			PluginHelper::importPlugin('umart');
			$this->app->triggerEvent('onUmartCategoryPrepareForm', [$form, $data]);
		}

		if ($isCategory && in_array($extension, ['com_umart.product.customfield', 'com_umart.product.option']))
		{
			$form->loadFile(UMART_COMPONENT_ADMINISTRATOR . '/models/forms/category/customfield.xml');
			$form->loadFile(UMART_COMPONENT_ADMINISTRATOR . '/models/forms/category/icon.xml');
		}
		elseif ($formName == 'com_menus.item')
		{
			$option = $registry->get('request.option');
			$view   = $registry->get('request.view');

			if ($option == 'com_umart')
			{
				PluginHelper::importPlugin('umart');
				$this->app->triggerEvent('onUmartMenuPrepareForm', [$form, $data]);

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

			if (strpos($module, 'mod_umart') === 0)
			{
				PluginHelper::importPlugin('umart');
			}

			if ($module === 'mod_umart_products')
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
		Table::addIncludePath(UMART_COMPONENT_ADMINISTRATOR . '/tables');

		$userTable = Table::getInstance('User', 'UmartTable');

		if ($userId && $userTable->load(['user_id' => $userId]))
		{
			$userTable->delete($userTable->id);
		}
	}

	public function onUserAfterSave($userData, $isNew, $result, $errorMsg)
	{
		if ($result && $isNew)
		{
			Table::addIncludePath(UMART_COMPONENT_ADMINISTRATOR . '/tables');
			$userTable = Table::getInstance('User', 'UmartTable');
			$userTable->set('user_id', (int) $userData['id']);
			$userTable->set('state', 1);

			if (!$userTable->check() || !$userTable->store())
			{
				$this->app->enqueueMessage(implode(PHP_EOL, $userTable->getErrors()));

				return false;
			}

			plg_sytem_umart_main('state')->set('customer.juser_register_id', $userData['id']);
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
					'UmartTranslations' => $validTransData,
				];

				if ('com_umart.product' === $table->extension)
				{
					$categories = Categories::getInstance('umart.product');
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

							$requestData['UmartTranslations'][$langCode]['path'] = implode('/', $paths) . '/' . $transData['alias'];
						}
					}
				}

				Translator::saveTranslations('categories', $table->id, $requestData);
			}

			PluginHelper::importPlugin('umart');
			$this->app->triggerEvent('onUmartCategoryAfterSave', [$context, $table, $isNew]);
		}
	}

	public function onExtensionAfterSave($context, $table, $isNew)
	{
		if ($context == 'com_plugins.plugin' && in_array($table->folder, ['umart', 'umart_payment', 'umartshipping']))
		{
			PluginHelper::importPlugin($table->folder, $table->element);
			$this->app->triggerEvent('on' . ucfirst($table->folder) . ucfirst($table->element) . 'AfterSave', [$context, $table, $isNew]);
		}
	}

	public function onAfterGetMenuTypeOptions(&$list, $model)
	{
		PluginHelper::importPlugin('umart');
		$options = $list['com_umart'];
		$this->app->triggerEvent('onUmartGetMenuTypeOptions', [&$options, $model]);
		$list['com_umart'] = $options;
	}

	public function onUmartAfterValidateCheckoutData(&$data)
	{
		if (PluginHelper::isEnabled('umart', 'recaptcha'))
		{
			$db    = CMSFactory::getDbo();
			$query = $db->getQuery(true)
				->select('a.manifest_cache')
				->from($db->quoteName('#__extensions', 'a'))
				->where('a.type = ' . $db->quote('plugin'))
				->where('a.folder = ' . $db->quote('umart'))
				->where('a.element = ' . $db->quote('recaptcha'));

			if ($manifest = $db->setQuery($query)->loadResult())
			{
				$manifest = new Registry($manifest);
				$version  = $manifest->get('version');

				if (!empty($version) && version_compare($version, '1.0.5', 'lt'))
				{
					throw new RuntimeException('Umart version ' . UMART_VERSION . ' required ReCaptcha v-1.0.5 or greater.');
				}
			}
		}
	}
}

if (!function_exists('plg_sytem_umart_main'))
{
	function plg_sytem_umart_main($key = null)
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
				$context = 'com_umart' . str_replace([$parts[0], 'com_umart.'], '', $key);
				$app     = $factory->__get('app');

				switch (strtolower(gettype($data)))
				{
					case 'array':
						$app->triggerEvent('onUmartPrepareItems', [$context, &$data]);
						break;

					case 'object':
						$app->triggerEvent('onUmartPrepareItem', [$context, $data]);
						break;

					case 'string':
						$o       = new stdClass;
						$o->text = $data;
						$app->triggerEvent('onUmartPrepareContent', [$context, $o]);
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
