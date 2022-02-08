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

use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Table\Table as CMSTable;

class Log
{
	public function addEntry($context, $stringKey, array $sprintfData = [], $previousData = null, $modifiedData = null)
	{
		CMSTable::addIncludePath(ES_COMPONENT_ADMINISTRATOR . '/tables');
		$logTable = CMSTable::getInstance('Log', 'EasyshopTable');
		$isNew    = true;
		$data     = [
			'context'       => $context,
			'string_key'    => strtoupper($stringKey),
			'sprintf_data'  => json_encode($sprintfData),
			'previous_data' => is_array($previousData) ? json_encode($previousData) : (is_string($previousData) ? $previousData : 'NULL'),
			'modified_data' => is_array($modifiedData) ? json_encode($modifiedData) : (is_string($modifiedData) ? $modifiedData : 'NULL'),
			'juser_id'      => (int) CMSFactory::getUser()->id,
			'ip'            => easyshop(Utility::class)->getClientIp(),
			'user_agent'    => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
			'created_date'  => CMSFactory::getDate()->toSql(),
			'referer'       => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
		];

		if ($logTable->bind($data) && $logTable->store())
		{
			CMSFactory::getApplication()->triggerEvent('onEasyshopAfterSave', ['com_easyshop.log', $logTable, $isNew, $data]);

			return true;
		}

		return false;
	}
}
