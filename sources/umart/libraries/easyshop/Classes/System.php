<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ES\Classes;

use JDatabaseDriver;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use RuntimeException;
use ZipArchive;

defined('_JEXEC') or die;

class System
{
	/**
	 * Fix install database schema: Add missed columns, correct the type of columns and ADD TABLE IF NOT EXISTS
	 * @return boolean
	 * @since 1.1.5
	 */
	public function fixSchemas()
	{
		$app             = easyshop('app');
		$sqlInstallFiles = [
			ES_COMPONENT_ADMINISTRATOR . '/sql/mysql/install.sql',
		];

		$app->triggerEvent('onEasyshopBeforeFixSchemas', [&$sqlInstallFiles]);
		$fileContents = '';

		foreach (array_unique($sqlInstallFiles) as $sqlInstallFile)
		{
			if (is_file($sqlInstallFile) && ($contents = file_get_contents($sqlInstallFile)))
			{
				$fileContents .= $contents . PHP_EOL;
			}
		}

		try
		{
			$queries      = [];
			$fileContents = rtrim($fileContents, PHP_EOL);

			if (!empty($fileContents))
			{
				/** @var JDatabaseDriver $db */
				$db        = easyshop('db');
				$tableList = $db->getTableList();
				$prefix    = $db->getPrefix();
				$types     = implode('|', [
					'INT',
					'TINYINT',
					'SMALLINT',
					'MEDIUMINT',
					'BIGINT',
					'FLOAT',
					'DOUBLE',
					'DECIMAL',
					'DATETIME',
					'DATE',
					'TIMESTAMP',
					'TIME',
					'YEAR',
					'CHAR',
					'VARCHAR',
					'BLOB',
					'TEXT',
					'TINYBLOB',
					'TINYTEXT',
					'MEDIUMBLOB',
					'MEDIUMTEXT',
					'LONGBLOB',
					'LONGTEXT',
					'ENUM',
				]);

				foreach ($db->splitSql($fileContents) as $content)
				{
					preg_match('/CREATE TABLE IF NOT EXISTS \`(#[_a-zA-Z0-9]+)\`/i', $content, $matches);

					if (!empty($matches[1]))
					{
						$originTable = $db->quoteName($matches[1]);
						$table       = str_replace('#__', $prefix, $matches[1]);

						if (!in_array($table, $tableList))
						{
							// Create table
							$queries[] = $matches[0] . '...';
							$db->setQuery($content)
								->execute();
							continue;
						}

						$columns   = $db->getTableColumns($table, true);
						$fields    = array_keys($columns);
						$lastField = $db->quoteName($fields[count($fields) - 1]);
						$lines     = preg_split('/\n|\r\n/', $content);

						foreach ($lines as $line)
						{
							preg_match('/^\`([a-z0-9_]+)\`\s+(' . $types . ').+\,$/i', trim($line), $matches);

							if ($matches)
							{
								list($queryString, $field, $type) = array_map('trim', $matches);
								$queryString = preg_replace('/\,$/', '', $queryString);
								$fieldName   = $db->quoteName($field);

								if (isset($columns[$field]))
								{
									if (stripos($columns[$field], $type) !== 0)
									{
										// Change column
										$query     = str_replace($fieldName, 'ALTER TABLE ' . $originTable . ' CHANGE COLUMN ' . $fieldName . ' ' . $fieldName, $queryString);
										$queries[] = $query;
										$db->setQuery($query)
											->execute();
									}
								}
								else
								{
									// Add new column
									$query     = str_replace($fieldName, 'ALTER TABLE ' . $originTable . ' ADD COLUMN ' . $fieldName, $queryString) . ' AFTER ' . $lastField;
									$queries[] = $query;
									$db->setQuery($query)
										->execute();
								}
							}
						}
					}
				}

				if ($queries)
				{
					$app->enqueueMessage('<h5>' . sprintf('%02d', count($queries)) . ' queries executed</h5><ol><li>' . implode('</li><li>', $queries) . '</li></ol>');
				}
				else
				{
					$app->enqueueMessage('No queries executed.');
				}

				return true;
			}
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		return false;
	}

	public function export()
	{
		if (!easyshop(User::class)->core('admin'))
		{
			return false;
		}

		if (!class_exists('ZipArchive'))
		{
			easyshop('app')->enqueueMessage('Class not exists: ZipArchive');

			return false;
		}

		$zip = new ZipArchive;

		if (!is_dir(ES_MEDIA . '/tmp'))
		{
			Folder::create(ES_MEDIA . '/tmp', 0755);
		}

		$zipFile = ES_MEDIA . '/tmp/EasyShop-export-data.' . date('Y-m-d-H-i-s') . '.zip';

		if (is_file($zipFile))
		{
			File::delete($zipFile);
		}

		if (true !== $zip->open($zipFile, ZipArchive::CREATE))
		{
			return false;
		}

		if ($productsList = $this->getItemsFromDatabase('#__easyshop_products'))
		{
			$zip->addFromString('products.csv', $this->dataToCSV($productsList));
			$ids   = [];
			$cid   = [];
			$bid   = [];
			$files = [];

			foreach ($productsList as $product)
			{
				$ids[] = (int) $product->id;
				$cid[] = (int) $product->category_id;

				if ($product->brand_id)
				{
					$bid[] = (int) $product->brand_id;
				}
			}

			if ($media = $this->getItemsFromDatabase('#__easyshop_medias'))
			{
				foreach ($media as $file)
				{
					if (is_file(ES_MEDIA . '/' . $file->file_path))
					{
						$files[] = $file;
						$zip->addFile(ES_MEDIA . '/' . $file->file_path, 'media/' . $file->file_path);
					}
				}
			}

			if ($files)
			{
				$zip->addFromString('media.csv', $this->dataToCSV($files));
			}

			if ($data = $this->getItemsFromDatabase('#__categories', 'id IN (' . implode(',', $cid) . ')'))
			{
				$zip->addFromString('categories.csv', $this->dataToCSV($data));
			}

			if ($bid && $data = $this->getItemsFromDatabase('#__categories', 'id IN (' . implode(',', $bid) . ')'))
			{
				$zip->addFromString('brands.csv', $this->dataToCSV($data));
			}

			if ($data = $this->getItemsFromDatabase('#__easyshop_tag_items', 'item_id IN (' . implode(',', $ids) . ')'))
			{
				$zip->addFromString('tags.csv', $this->dataToCSV($data));
			}

			$tables = [
				'easyshop_customfields' => 'fields.csv',
				'easyshop_prices'       => 'prices.csv',
				'easyshop_price_days'   => 'priceDays.csv',
				'easyshop_taxes'        => 'taxes.csv',
			];

			foreach ($tables as $table => $localName)
			{
				if ($data = $this->getItemsFromDatabase('#__' . $table))
				{
					$zip->addFromString($localName, $this->dataToCSV($data));
				}
			}

			if ($data = $this->getItemsFromDatabase('#__easyshop_customfield_values', 'reflector = \'com_easyshop.product.customfield\''))
			{
				$zip->addFromString('fieldValues.csv', $this->dataToCSV($data));
			}

			if ($data = $this->getItemsFromDatabase('#__easyshop_tags', 'context = \'com_easyshop.product\''))
			{
				$zip->addFromString('tags.csv', $this->dataToCSV($data));
			}

			if ($data = $this->getItemsFromDatabase('#__easyshop_params', 'context LIKE \'%.product\''))
			{
				$zip->addFromString('params.csv', $this->dataToCSV($data));
			}
		}

		$zip->close();

		return $zipFile;
	}

	protected function getItemsFromDatabase($table, $extraWhere = null)
	{
		$db    = easyshop('db');
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName($table));

		if ($extraWhere)
		{
			$query->where($extraWhere);
		}

		return $db->setQuery($query)->loadObjectList();
	}

	protected function dataToCSV(array $data)
	{
		ob_start();
		$fp = fopen('php://output', 'w');
		fputcsv($fp, array_keys((array) $data[0]));

		foreach ($data as $datum)
		{
			fputcsv($fp, array_values((array) $datum));
		}

		fclose($fp);

		return ob_get_clean();
	}

	public function getExpiredLayoutsFiles()
	{
		$db    = easyshop('db');
		$query = $db->getQuery(true)
			->select('a.template')
			->from($db->quoteName('#__template_styles', 'a'))
			->join('INNER', $db->quoteName('#__extensions', 'a2') . ' ON a2.element = a.template AND a2.type = ' . $db->quote('template'))
			->where('a.client_id = 0 AND a2.enabled = 1');
		$db->setQuery($query);
		$templates = array_unique($db->loadColumn());
		$results   = [];

		foreach ($templates as $template)
		{
			$path = JPATH_SITE . '/templates/' . $template . '/html/com_easyshop';

			if (is_dir($path))
			{
				foreach (Folder::files($path, '\.php$', true, true) as $file)
				{
					$fp = fopen($file, 'r');

					if (false !== $fp)
					{
						while (!feof($fp))
						{
							$line = fgets($fp);
							preg_match('/\*\s*\@version\s+([0-9]{1}\.[0-9]{1}\.[0-9]{1,2})/i', $line, $matches);

							if (!empty($matches[1])
								&& version_compare(ES_VERSION, $matches[1], 'gt')
							)
							{
								$results[$template][$matches[1]] = $file;
								break;
							}
						}

						fclose($fp);
					}
				}
			}
		}

		return $results;
	}
}
