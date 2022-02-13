<?php
/**
 
 
 
 
 
 */

namespace Umart\Classes;
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
				$result    = plg_sytem_umart_main('app')->triggerEvent($event, $arguments);
				$results[] = trim(implode(PHP_EOL, $result));
			}

			unset(static::$events[$event]);
		}

		return trim(implode(PHP_EOL, $results));
	}
}
