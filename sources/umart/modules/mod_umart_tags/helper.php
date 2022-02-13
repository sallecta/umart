<?php
/**
 
 
 
 
 
 */

use Umart\Classes\Tags;

defined('_JEXEC') or die;

class ModUmartTagsHelper
{
	public static function getTags()
	{
		return umart(Tags::class)->getTags();
	}
}
