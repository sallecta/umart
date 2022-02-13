<?php

 
 
 
 
 

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

class com_umartInstallerScript
{
	protected $propertyArrPluginsAndModules = array();
	
	public function preflight($type, $adapter)
	{
		
		if (version_compare(JVERSION, '3.8.0', 'lt'))
		{
			throw new RuntimeException('<p>You need Joomla! 3.8.0 or later to install/update Umart component.</p>');
		}
		
		$db = CMSFactory::getDBo();

		if ($type == 'uninstall')
		{
			$query = $db->getQuery(true)
				->update($db->quoteName('#__extensions'))
				->set($db->quoteName('enabled') . ' = 0')
				->set($db->quoteName('protected') . ' = 0')
				->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
				->where($db->quoteName('folder') . ' = ' . $db->quote('umart'))
				->where($db->quoteName('element') . ' = ' . $db->quote('system'));
			$db->setQuery($query)->execute();
		}

		return true;
	}

	public function install($adapter)
	{
		//echo 'fn install: started </br>';
		$pathRoot = $adapter->getParent()->getPath('source');
		$db   = CMSFactory::getDbo();
		$SQLs = Folder::files($pathRoot . '/administrator/components/com_umart/sql/mysql/dummy', '\.sql$', false, true);

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
		$query->update($db->quoteName('#__umart_currencies'))
			->set($db->quoteName('checked_out') . ' = 0')
			->set($db->quoteName('checked_out_time') . ' = ' . $db->quote($db->getNullDate()))
			->set($db->quoteName('created_date') . ' = ' . $date)
			->set($db->quoteName('created_by') . ' = ' . $userId);
		$db->setQuery($query)
			->execute();
		$query->clear('update')
			->update($db->quoteName('#__umart_zones'));
		$db->setQuery($query)
			->execute();
		$query->clear('update')
			->update($db->quoteName('#__umart_customfields'));
		$db->setQuery($query)
			->execute();
		$this->update($adapter);

		$data = [
			'title'     => 'Shop',
			'extension' => 'com_umart.product',
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
		
		//echo 'fn install: ended </br>';
		return true;
	}

	public function update($adapter)
	{
		//echo 'fn update: started </br>';
		$pathRoot = $adapter->getParent()->getPath('source');
		Folder::copy($pathRoot . '/libraries/umart', JPATH_ROOT . '/libraries/umart', '', true);
		File::copy($pathRoot . '/cli/umart.php', JPATH_ROOT . '/cli/umart.php');
		//echo 'fn update: root path ('. $pathRoot .') exists </br>';
		//$pathRootUmartUi = $pathRoot . '/plugins/umart/umartui';
		$pluginsDirs = $this-> pluginsDirs ($pathRoot . '/plugins', 3);
			
		foreach ($pluginsDirs as $pluginDir)
		 {
			//echo 'fn update: pluginDir: ' .  $pluginDir . '</br>';
			 
			// if ($pluginDir == 'umartui'
				 // && is_file($pathRootUmartUi . '/umartui.xml')
				 // && function_exists('simplexml_load_file')
				 // && ($umartUiManifest = @simplexml_load_file($pathRootUmartUi . '/umartui.xml'))
			 // )
			 // {
				 // $curUIVersion = $this->getUIVersion();

				 // if (null !== $curUIVersion
					 // && version_compare($curUIVersion, (string) $umartUiManifest->version, 'ge')
				 // )
				 // {
					 // continue;
				 // }
			 // }

			 // install plugin
			 $installer = new Installer;
			 if ( $installer->install($pluginDir) )
			 {
				 //echo 'fn update: success install of plugin ' .  $pluginDir . '</br>';
				 array_push($this->propertyArrPluginsAndModules, $pluginDir);
			 }
			 else
			 {
				 echo 'fn update: failed install of plugin ' .  $pluginDir . '</br>';
				 continue;//return false;
			 }
			 // end install plugin

			 // enable plugin
			 $manifest = $installer->getManifest();
			 $this->pluginEnable($pluginDir, (string) $manifest->attributes()->group);
			 // end enable plugin
		 } // end foreach

		if (Folder::exists($pathRoot . '/modules'))
		{
			$modules = Folder::folders($pathRoot . '/modules');

			foreach ($modules as $module)
			{
				$installer = new Installer;
				$moduleDir = $pathRoot . '/modules/' . $module;
				$installer->install($moduleDir);
				array_push($this->propertyArrPluginsAndModules, $moduleDir);
			}
		}

		//echo 'fn update ended </br>';
		return true;
	} // end update

	protected function pluginsDirs ($argPath, $argDeep)
	{
		$arrOut = array();
		function _recursive ($argPath, $argDeep, $argDeepCurrent, &$argArrOut)
		{
			if (  isset($argDeepCurrent) )
			{
				if ( $argDeepCurrent > $argDeep )
				{
					 return; // currentDeep ecseeds deep limit			 
				}
			}
			else
			{
				$argDeepCurrent=1; // first run (no recursion), set initial value to 1
			}
			// get subdirectories
			$subdirs = glob($argPath . "/*",  GLOB_ONLYDIR); 

			foreach ( $subdirs as $subDir )
			{
				// processing subdirectory
				$fname = $subDir . '/'. basename( $subDir ) . '.xml';
				if ( is_file($fname) )
				{
					//echo " $fname </br>\n";
					array_push($argArrOut, $subDir);
				}
				// end processing subdirectory
				
				// check if we need to recurse 
				if ( $argDeepCurrent <= $argDeep )
				{
					$subdirsNextTotal = count( glob($subDir . "/*",  GLOB_ONLYDIR + GLOB_NOSORT) );
					if ($subdirsNextTotal > 0)
					{
						$deepCurrentNew = $argDeepCurrent + 1;
						_recursive( $subDir, $argDeep , $deepCurrentNew, $argArrOut );					
					}
				}
			} // end foreach		
		} // end _recursive
		_recursive ($argPath, $argDeep, null, $arrOut);
		return $arrOut;
	} // end pluginsDirs
	
	protected function getUIVersion()
	{
		$table = Table::getInstance('Extension', 'JTable');

		if ($table->load([
			'type'    => 'plugin',
			'folder'  => 'umart',
			'element' => 'umartui',
		])
		)
		{
			$manifest = json_decode($table->manifest_cache);

			return isset($manifest->version) ? (string) $manifest->version : null;
		}

		return null;
	}

	protected function pluginEnable($argPluginFolder, $argPluginGroup)
	{
		$argPluginFolder = basename($argPluginFolder);
		$db    = CMSFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update($db->quoteName('#__extensions'))
			->set($db->quoteName('enabled') . ' = 1')
			->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
			->where($db->quoteName('element') . ' = ' . $db->quote($argPluginFolder))
			->where($db->quoteName('folder') . ' = ' . $db->quote($argPluginGroup));

		if ($argPluginFolder == 'umart' OR $argPluginFolder == 'umartui')
		{
			$query->set($db->quoteName('protected') . ' = 1');
		}

		$db->setQuery($query);

		return $db->execute();
	} // pluginEnable

	protected function deleteFiles()
	{
		$files = [
			JPATH_ROOT . '/cli/umart.php'
		];

		foreach ($files as $file)
		{
			if (is_file($file))
			{
				@File::delete($file);
			}
		}

		$folders = [
			JPATH_ADMINISTRATOR . '/components/com_umart',
			JPATH_ROOT . '/components/com_umart',
			JPATH_LIBRARIES . '/umart',
			JPATH_ROOT . '/media/com_umart'
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
				->where('a.extension = ' . $db->quote('com_umart.product'))
				->order('a.id ASC');
			$db->setQuery($query);

			$installMenu = false;
			$categoryId  = (int) $db->loadResult();
			$componentId = (int) ComponentHelper::getComponent('com_umart')->id;
			$menuData    = [
				[
					'menutype'     => 'umartmenu',
					'title'        => 'Shop',
					'alias'        => 'shop',
					'link'         => 'index.php?option=com_umart&view=productlist&id=' . $categoryId,
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
					'menutype'     => 'umartmenu',
					'title'        => 'Search product',
					'alias'        => 'search-product',
					'link'         => 'index.php?option=com_umart&view=search',
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
					'menutype'     => 'umartmenu',
					'title'        => 'Your cart',
					'alias'        => 'your-cart',
					'link'         => 'index.php?option=com_umart&view=cart',
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
					'menutype'     => 'umartmenu',
					'title'        => 'Product tags',
					'alias'        => 'product-tags',
					'link'         => 'index.php?option=com_umart&view=search&layout=tag',
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
					'menutype'     => 'umartmenu',
					'title'        => 'My page',
					'alias'        => 'my-page',
					'link'         => 'index.php?option=com_umart&view=customer',
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
					->where('m.menutype = ' . $db->quote('umartmenu'));
				$db->setQuery($query);

				if (!$db->loadResult())
				{
					$query->clear()
						->insert($db->quoteName('#__menu_types'))
						->columns($db->quoteName(['menutype', 'title', 'description', 'client_id']))
						->values($db->quote('umartmenu') . ',' . $db->quote('Umart Menu') . ', ' . $db->quote('This is the default Umart menu.') . ', 0');
					$db->setQuery($query)
						->execute();
				}
			}
		}
		catch (RuntimeException $e)
		{
			CMSFactory::getApplication()->enqueueMessage($e->getMessage(), 'notice');
		}/**/
	}

	public function uninstall($adapter)
	{
		$db    = CMSFactory::getDBo();
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__menu'))
			->where($db->quoteName('link') . ' LIKE ' . $db->quote('index.php?option=com_umart%'));
		$db->setQuery($query)->execute();

		$query->clear()
			->delete($db->quoteName('#__categories'))
			->where($db->quoteName('extension') . ' LIKE ' . $db->quote('com_umart.%'));
		$db->setQuery($query)->execute();

		$query->clear()
			->update($db->quoteName('#__modules'))
			->set($db->quoteName('published') . ' = 0')
			->where($db->quoteName('module') . ' LIKE ' . $db->quote('mod_umart_%'));
		$db->setQuery($query)->execute();

		$query->clear()
			->update($db->quoteName('#__extensions'))
			->set($db->quoteName('enabled') . ' = 0')
			->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
			->where($db->quoteName('folder') . ' LIKE ' . $db->quote('umart%'));
		$db->setQuery($query)->execute();

		$query->clear()
			->update($db->quoteName('#__extensions'))
			->set($db->quoteName('enabled') . ' = 0')
			->where($db->quoteName('type') . ' = ' . $db->quote('module'))
			->where($db->quoteName('element') . ' LIKE ' . $db->quote('mod_umart_%'));
		$db->setQuery($query)->execute();

		return true;
	}

	public function postflight($type, $adapter)
	{
		if ($type == 'uninstall')
		{
			$this->deleteFiles();
			return true;
		}

		?>
			<style>
					.umart-install {
						margin-bottom: 15px; padding: 15px; border: 1px solid #eee; 
						font-size: 14px; color: #444; font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 24px;					
					}
					a.umart-a-target {
					        display: inline-block;
					        text-decoration: none; text-transform: uppercase;
					        background-color: #3F51B5; color: #fff;
					        padding: 8px 35px;
					        border-radius: 25px;
					        margin-right: 5px;
					}
					a.umart-a-target:hover { background-color: #0077ff; }
					.umart-install h1 {
						margin-top: 0;
						color: #009688; font-weight: 300;
					}
					.umart-install h2 {
						margin-top: 0;
						color: #009688; font-weight: 300;
					}
					.umart-install ul { list-style: none; 
					}
					.umart-install li::before { content: '\2713'; /* unicode check symbol */
						padding-right: 8px;
					}
			</style>
			<div class="umart-install" >
				<table border="0">
					<tbody>
						<tr>
					    	<td>
					        	<!-- <img src="image.png" alt="Umart Component"/> -->
					        </td>
					        <td>
						        <h1>Thank you for using Umart <?php echo $adapter->getManifest()->version; ?></h1>
								<p>
						            <p>&check; The Umart Component has been successfully installed on your website.</p>
									<?php
										$totalPluginsAndModules = count($this->propertyArrPluginsAndModules);
										if ( $totalPluginsAndModules > 0 )
										{
											?>
											<h2>It also automatically installed <?php echo $totalPluginsAndModules;?> Plugins and Modules</h2>
											<ul>
											<?php
											foreach ($this->propertyArrPluginsAndModules as $item)
											{
												?>
												<li><?php echo $item; ?>
												<?php
											}
										}
									?>
						        </p>
					            <div>
					            	<a href="https://github.com/sallecta/umart" target="_blank" class="umart-a-target" >
					                        Visit Umart home page
					                </a>
					        	</div>
					        </td>
					    </tr>
					</tbody>
				</table>
			</div>
			<?php
	}
}
