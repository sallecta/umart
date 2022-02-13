<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

class UmartHelperMedia
{
	public static function getLink($mediaPath = '', $mediaType = 'image', $task = null)
	{
		$input    = plg_sytem_umart_main('app')->input;
		$tmplType = $input->getString('tmpl');
		$method   = $input->getString('method');
		$multiple = $input->getString('multiple');
		$thumb    = $input->getString('thumb', 'true');

		if (null !== $task)
		{
			$token = JSession::getFormToken() . '=1';
			$link  = 'index.php?option=com_umart&task=' . $task . '&' . $token;
		}
		else
		{
			$link = 'index.php?option=com_umart&view=media';
		}

		if (!empty($tmplType))
		{
			$link .= '&tmpl=' . $tmplType;
		}

		if (!empty($method))
		{
			$link .= '&method=' . $method;

			if ($multiple == 'true')
			{
				$link .= '&multiple=true';
			}
		}

		if ($thumb == '0' || $thumb == 'false')
		{
			$link .= '&thumb=0';
		}

		$link .= '&media_path=' . $mediaPath . '&media_type=' . $mediaType;

		return Route::_($link, false);
	}
}
