<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

class com_easyshopInstallerScript
{
	public function preflight($type, $adapter)
	{
		$db = CMSFactory::getDBo();

		if ($type == 'uninstall')
		{
			$query = $db->getQuery(true)
				->update($db->quoteName('#__extensions'))
				->set($db->quoteName('enabled') . ' = 0')
				->set($db->quoteName('protected') . ' = 0')
				->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
				->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
				->where($db->quoteName('element') . ' = ' . $db->quote('easyshop'));
			$db->setQuery($query)->execute();
		}
		elseif (version_compare(JVERSION, '3.8.0', 'lt'))
		{
			throw new RuntimeException('<p>You need Joomla! 3.8.0 or later to install/update EasyShop component.</p>');
		}

		return true;
	}

	public function install($adapter)
	{
		$path = $adapter->getParent()->getPath('source');
		$db   = CMSFactory::getDbo();
		$SQLs = Folder::files($path . '/admin/sql/mysql/dummy', '\.sql$', false, true);

		foreach ($SQLs as $SQL)
		{
			$contents = file_get_contents($SQL);

			if (!$contents)
			{
				CMSFactory::getApplication()->enqueueMessage('Cannot install dummy SQL data', 'error');

				return false;
			}

			foreach ($db->splitSql($contents) as $string)
			{
				if (!$db->setQuery($string)->execute())
				{
					return false;
				}
			}
		}

		$userId = (int) CMSFactory::getUser()->id;
		$date   = $db->quote(CMSFactory::getDate()->toSql());
		$query  = $db->getQuery(true);
		$query->update($db->quoteName('#__easyshop_currencies'))
			->set($db->quoteName('checked_out') . ' = 0')
			->set($db->quoteName('checked_out_time') . ' = ' . $db->quote($db->getNullDate()))
			->set($db->quoteName('created_date') . ' = ' . $date)
			->set($db->quoteName('created_by') . ' = ' . $userId);
		$db->setQuery($query)
			->execute();
		$query->clear('update')
			->update($db->quoteName('#__easyshop_zones'));
		$db->setQuery($query)
			->execute();
		$query->clear('update')
			->update($db->quoteName('#__easyshop_customfields'));
		$db->setQuery($query)
			->execute();
		$this->update($adapter);

		$data = [
			'title'     => 'Shop',
			'extension' => 'com_easyshop.product',
			'language'  => '*',
			'parent_id' => 1,
			'published' => 1,
		];

		if (version_compare(JVERSION, '4.0', 'ge'))
		{
			JLoader::register('CategoriesHelper', JPATH_ADMINISTRATOR . '/components/com_categories/Helper/CategoriesHelper.php');
			\Joomla\Component\Categories\Administrator\Helper\CategoriesHelper::createCategory($data);
		}
		else
		{
			JLoader::register('CategoriesHelper', JPATH_ADMINISTRATOR . '/components/com_categories/helpers/categories.php');
			CategoriesHelper::createCategory($data);
		}

		$this->installMenus();

		return true;
	}

	public function update($adapter)
	{
		$path = $adapter->getParent()->getPath('source');
		Folder::copy($path . '/libraries/easyshop', JPATH_ROOT . '/libraries/easyshop', '', true);
		File::copy($path . '/cli/easyshop.php', JPATH_ROOT . '/cli/easyshop.php');

		if (Folder::exists($path . '/extensions/plugins'))
		{
			$plugins  = Folder::folders($path . '/extensions/plugins');
			$ukuiPath = $path . '/extensions/plugins/ukui';

			foreach ($plugins as $plugin)
			{
				if ($plugin == 'ukui'
					&& is_file($ukuiPath . '/ukui.xml')
					&& function_exists('simplexml_load_file')
					&& ($ukuiManifest = @simplexml_load_file($ukuiPath . '/ukui.xml'))
				)
				{
					$curUIVersion = $this->getUIVersion();

					if (null !== $curUIVersion
						&& version_compare($curUIVersion, (string) $ukuiManifest->version, 'ge')
					)
					{
						continue;
					}
				}

				$installer = new Installer;

				if (!$installer->install($path . '/extensions/plugins/' . $plugin))
				{
					return false;
				}

				$manifest = $installer->getManifest();
				$this->enablePlugin($plugin, (string) $manifest->attributes()->group);
			}
		}

		if (Folder::exists($path . '/extensions/modules'))
		{
			$modules = Folder::folders($path . '/extensions/modules');

			foreach ($modules as $module)
			{
				$installer = new Installer;
				$installer->install($path . '/extensions/modules/' . $module);
			}
		}

		$this->deleteFiles();

		return true;
	}

	protected function getUIVersion()
	{
		$table = Table::getInstance('Extension', 'JTable');

		if ($table->load([
			'type'    => 'plugin',
			'folder'  => 'system',
			'element' => 'ukui',
		])
		)
		{
			$manifest = json_decode($table->manifest_cache);

			return isset($manifest->version) ? (string) $manifest->version : null;
		}

		return null;
	}

	protected function enablePlugin($plugin, $group)
	{
		$db    = CMSFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update($db->quoteName('#__extensions'))
			->set($db->quoteName('enabled') . ' = 1')
			->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
			->where($db->quoteName('element') . ' = ' . $db->quote($plugin))
			->where($db->quoteName('folder') . ' = ' . $db->quote($group));

		if ($group == 'system')
		{
			$query->set($db->quoteName('protected') . ' = 1');
		}

		$db->setQuery($query);

		return $db->execute();
	}

	protected function deleteFiles()
	{
		$files = [
			JPATH_ADMINISTRATOR . '/components/com_easyshop/layouts/order/notification.php',
			JPATH_ADMINISTRATOR . '/components/com_easyshop/layouts/system/notification.php',
			JPATH_ADMINISTRATOR . '/components/com_easyshop/layouts/order/address.php',
			JPATH_ADMINISTRATOR . '/components/com_easyshop/layouts/modal/svgicon.php',
			JPATH_ADMINISTRATOR . '/components/com_easyshop/models/fields/modal/svgicon.php',
			JPATH_ADMINISTRATOR . '/components/com_easyshop/tables/price.php',
			JPATH_ROOT . '/administrator/components/com_easyshop/helpers/layout.php',
			JPATH_ROOT . '/components/com_easyshop/layouts/product/options-addtocart.php',
			JPATH_ROOT . '/libraries/easyshop/classes/exception.php',
			JPATH_ROOT . '/libraries/easyshop/classes/form.php',
			JPATH_ROOT . '/administrator/components/com_easyshop/templates/default/template.php',
			JPATH_ROOT . '/components/com_easyshop/controllers/comment.php',
			JPATH_ROOT . '/components/com_easyshop/layouts/product/carousel.php',
			JPATH_ROOT . '/media/com_easyshop/js/owl.carousel.min.js',
			JPATH_ROOT . '/media/com_easyshop/css/owl.carousel.css',

		];

		foreach ($files as $file)
		{
			if (is_file($file))
			{
				@File::delete($file);
			}
		}

		$folders = [
			JPATH_ROOT . '/media/com_easyshop/js/ui/i18n',
			JPATH_ROOT . '/components/com_easyshop/layouts/joomla/searchtools/default',
			JPATH_LIBRARIES . '/easyshop/abstract',
			JPATH_LIBRARIES . '/easyshop/classes',
			JPATH_LIBRARIES . '/easyshop/controller',
			JPATH_LIBRARIES . '/easyshop/model',
			JPATH_LIBRARIES . '/easyshop/table',
			JPATH_LIBRARIES . '/easyshop/plugin',
			JPATH_LIBRARIES . '/easyshop/view',
			JPATH_ADMINISTRATOR . '/components/com_easyshop/layouts/joomla/searchtools',
			JPATH_ADMINISTRATOR . '/components/com_easyshop/views/reviews',
			JPATH_ADMINISTRATOR . '/components/com_easyshop/views/review',
			// JPATH_ROOT . '/media/com_easyshop/icons', => to keep B/C
		];

		foreach ($folders as $folder)
		{
			if (is_dir($folder))
			{
				@Folder::delete($folder);
			}
		}
	}

	protected function installMenus()
	{
		try
		{
			if (version_compare(JVERSION, '4.0', 'ge'))
			{
				$tableType   = 'MenuTable';
				$modelName   = 'ItemModel';
				$tablePrefix = 'Joomla\\Component\\Menus\\Administrator\\Table\\';
				$modelPrefix = 'Joomla\\Component\\Menus\\Administrator\\Model\\';
				Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_menus/Table');
				Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_menus/forms');
				Form::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_menus/models');
				Form::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_menus/Field');
				BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_menus/Model', $modelPrefix);
			}
			else
			{
				$tableType   = 'Menu';
				$modelName   = 'Item';
				$tablePrefix = 'MenusTable';
				$modelPrefix = 'MenusModel';
				Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_menus/tables');
				Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_menus/models/forms');
				Form::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_menus/models/fields');
				BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_menus/models', $modelPrefix);
			}

			$menuModel = BaseDatabaseModel::getInstance($modelName, $modelPrefix, ['ignore_request' => true]);

			if (!$menuModel)
			{
				return true;
			}

			$menuModel->setState('item.client_id', 0);
			$db    = CMSFactory::getDbo();
			$query = $db->getQuery(true);
			$query->clear()
				->select('a.id')
				->from($db->quoteName('#__categories', 'a'))
				->where('a.extension = ' . $db->quote('com_easyshop.product'))
				->order('a.id ASC');
			$db->setQuery($query);

			$installMenu = false;
			$categoryId  = (int) $db->loadResult();
			$componentId = (int) ComponentHelper::getComponent('com_easyshop')->id;
			$menuData    = [
				[
					'menutype'     => 'easyshopmenu',
					'title'        => 'Shop',
					'alias'        => 'shop',
					'link'         => 'index.php?option=com_easyshop&view=productlist&id=' . $categoryId,
					'type'         => 'component',
					'language'     => '*',
					'published'    => 1,
					'parent_id'    => 1,
					'access'       => 1,
					'home'         => 0,
					'client_id'    => 0,
					'component_id' => $componentId,
				],
				[
					'menutype'     => 'easyshopmenu',
					'title'        => 'Search product',
					'alias'        => 'search-product',
					'link'         => 'index.php?option=com_easyshop&view=search',
					'type'         => 'component',
					'language'     => '*',
					'published'    => 1,
					'parent_id'    => 1,
					'access'       => 1,
					'home'         => 0,
					'client_id'    => 0,
					'component_id' => $componentId,
				],
				[
					'menutype'     => 'easyshopmenu',
					'title'        => 'Your cart',
					'alias'        => 'your-cart',
					'link'         => 'index.php?option=com_easyshop&view=cart',
					'type'         => 'component',
					'language'     => '*',
					'published'    => 1,
					'parent_id'    => 1,
					'access'       => 1,
					'home'         => 0,
					'client_id'    => 0,
					'component_id' => $componentId,
				],
				[
					'menutype'     => 'easyshopmenu',
					'title'        => 'Product tags',
					'alias'        => 'product-tags',
					'link'         => 'index.php?option=com_easyshop&view=search&layout=tag',
					'type'         => 'component',
					'language'     => '*',
					'published'    => 1,
					'parent_id'    => 1,
					'access'       => 1,
					'home'         => 0,
					'client_id'    => 0,
					'component_id' => $componentId,
				],
				[
					'menutype'     => 'easyshopmenu',
					'title'        => 'My page',
					'alias'        => 'my-page',
					'link'         => 'index.php?option=com_easyshop&view=customer',
					'type'         => 'component',
					'language'     => '*',
					'published'    => 1,
					'parent_id'    => 1,
					'access'       => 1,
					'home'         => 0,
					'client_id'    => 0,
					'component_id' => $componentId,
				],
			];

			foreach ($menuData as $mData)
			{
				$menuTable = Table::getInstance($tableType, $tablePrefix);

				if (!$menuTable->load([
					'component_id' => $mData['component_id'],
					'client_id'    => $mData['client_id'],
					'link'         => $mData['link'],
				])
				)
				{
					$menuTable->setLocation(1, 'last-child');

					if (!$menuTable->bind($mData)
						|| !$menuTable->check()
						|| !$menuTable->store()
						|| !$menuTable->rebuildPath($menuTable->id)
					)
					{
						throw new RuntimeException($menuTable->getError());
					}

					$menuModel->setState('item.id', $menuTable->id);

					if ($menuForm = $menuModel->getForm([], true))
					{
						$params = new Registry;

						foreach ($menuForm->getGroup('params') as $field)
						{
							$name  = $field->getAttribute('name');
							$value = $field->__get('value');
							$params->set($name, $value);
						}

						$menuTable->set('params', (string) $params->toString());
						$menuTable->store();
					}

					$installMenu = true;
				}
			}

			if ($installMenu)
			{
				$query->clear()
					->select('COUNT(m.id)')
					->from($db->quoteName('#__menu_types', 'm'))
					->where('m.menutype = ' . $db->quote('easyshopmenu'));
				$db->setQuery($query);

				if (!$db->loadResult())
				{
					$query->clear()
						->insert($db->quoteName('#__menu_types'))
						->columns($db->quoteName(['menutype', 'title', 'description', 'client_id']))
						->values($db->quote('easyshopmenu') . ',' . $db->quote('EasyShop Menu') . ', ' . $db->quote('This is the default EasyShop menu.') . ', 0');
					$db->setQuery($query)
						->execute();
				}
			}
		}
		catch (RuntimeException $e)
		{
			CMSFactory::getApplication()->enqueueMessage($e->getMessage(), 'notice');
		}
	}

	public function uninstall($adapter)
	{
		$db    = CMSFactory::getDBo();
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__menu'))
			->where($db->quoteName('link') . ' LIKE ' . $db->quote('index.php?option=com_easyshop%'));
		$db->setQuery($query)->execute();

		$query->clear()
			->delete($db->quoteName('#__categories'))
			->where($db->quoteName('extension') . ' LIKE ' . $db->quote('com_easyshop.%'));
		$db->setQuery($query)->execute();

		$query->clear()
			->update($db->quoteName('#__modules'))
			->set($db->quoteName('published') . ' = 0')
			->where($db->quoteName('module') . ' LIKE ' . $db->quote('mod_easyshop_%'));
		$db->setQuery($query)->execute();

		$query->clear()
			->update($db->quoteName('#__extensions'))
			->set($db->quoteName('enabled') . ' = 0')
			->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
			->where($db->quoteName('folder') . ' LIKE ' . $db->quote('easyshop%'));
		$db->setQuery($query)->execute();

		$query->clear()
			->update($db->quoteName('#__extensions'))
			->set($db->quoteName('enabled') . ' = 0')
			->where($db->quoteName('type') . ' = ' . $db->quote('module'))
			->where($db->quoteName('element') . ' LIKE ' . $db->quote('mod_easyshop_%'));
		$db->setQuery($query)->execute();

		return true;
	}

	public function postflight($type, $adapter)
	{
		if ($type == 'uninstall')
		{
			return true;
		}

		echo
			'<style>
					#es-install .es-a-target {
					        display: inline-block;
					        text-decoration: none;
					        text-transform: uppercase;
					        color: #fff;
					        padding: 8px 35px;
					        border-radius: 25px;
					        margin-right: 5px;
					}
			</style>
			<div id="es-install" style="margin-bottom: 15px; padding: 15px; border: 1px solid #eee; font-size: 14px; color: #444; font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 24px;">
				<table border="0">
					<tbody>
						<tr>
					    	<td>
					        	<img src="https://www.joomtech.net/media/com_easyshop/assets/images/thumbs/easyshop_450x0.png" alt="EasyShop Component"/>
					        </td>
					        <td>
						        <p style="margin-top: 0;"><strong style="color: #009688; font-size: 18px; line-height: 34px;">Thank you for using EasyShop v-' . (string) $adapter->getManifest()->version . '</strong>
						            <br/><i class="icon-ok"></i> The EasyShop Component has been successfully installed on your website.
						            <br/><i class="icon-ok"></i> It also automatically installed some Plugins and Modules.
						            <br/><i class="icon-ok"></i> If you are satisfied with EasyShop, please post a rating and a review at the <a href="https://extensions.joomla.org/extensions/extension/e-commerce/shopping-cart/easy-shop/" target="_blank">Joomla! Extensions Directory</a>
						        </p>
					            <div>
					            	<a href="https://www.joomtech.net/community/index" target="_blank" class="es-a-target" style="background-color: #3F51B5;">
					                        <i class="icon-comments"></i> Forum
					                </a>
					               	<a href="https://www.joomtech.net/easyshop-docs/getting-started/easyshop-getting-started" target="_blank" class="es-a-target" style="background-color: #009688;">
					                	<i class="icon-help"></i> Documentation
					                </a>
					            	<a href="https://demo.joomtech.net/" target="_blank" class="es-a-target" style="background-color: #F44336;">
					                	<i class="icon-search"></i> Demo
					            	</a>
					        	</div>
					        </td>
					    </tr>
					</tbody>
				</table>
			</div>';
	}
}
