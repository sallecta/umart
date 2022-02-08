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

class Event
{
	protected static $events = [];

	public function register($event, array $arguments = [])
	{
		if (!isset(static::$events[$event]))
		{
			static::$events[$event] = [];
		}

		static::$events[$event][] = $arguments;

		return $this;
	}

	public function execute($event)
	{
		$results = [];

		if (isset(static::$events[$event]))
		{
			foreach (static::$events[$event] as $arguments)
			{
				$result    = easyshop('app')->triggerEvent($event, $arguments);
				$results[] = trim(implode(PHP_EOL, $result));
			}

			unset(static::$events[$event]);
		}

		return trim(implode(PHP_EOL, $results));
	}
}
