<?php
/**
 
 
 
 
 
 */

namespace Umart\Classes;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Table\Table as CMSTable;

class Log
{
	public function addEntry($context, $stringKey, array $sprintfData = [], $previousData = null, $modifiedData = null)
	{
		CMSTable::addIncludePath(UMART_COMPONENT_ADMINISTRATOR . '/tables');
		$logTable = CMSTable::getInstance('Log', 'UmartTable');
		$isNew    = true;
		$data     = [
			'context'       => $context,
			'string_key'    => strtoupper($stringKey),
			'sprintf_data'  => json_encode($sprintfData),
			'previous_data' => is_array($previousData) ? json_encode($previousData) : (is_string($previousData) ? $previousData : 'NULL'),
			'modified_data' => is_array($modifiedData) ? json_encode($modifiedData) : (is_string($modifiedData) ? $modifiedData : 'NULL'),
			'juser_id'      => (int) CMSFactory::getUser()->id,
			'ip'            => plg_sytem_umart_main(Utility::class)->getClientIp(),
			'user_agent'    => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
			'created_date'  => CMSFactory::getDate()->toSql(),
			'referer'       => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
		];

		if ($logTable->bind($data) && $logTable->store())
		{
			CMSFactory::getApplication()->triggerEvent('onUmartAfterSave', ['com_umart.log', $logTable, $isNew, $data]);

			return true;
		}

		return false;
	}
}
