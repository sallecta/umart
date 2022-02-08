<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

class EasyshopHelperMedia
{
	public static function getLink($mediaPath = '', $mediaType = 'image', $task = null)
	{
		$input    = easyshop('app')->input;
		$tmplType = $input->getString('tmpl');
		$method   = $input->getString('method');
		$multiple = $input->getString('multiple');
		$thumb    = $input->getString('thumb', 'true');

		if (null !== $task)
		{
			$token = JSession::getFormToken() . '=1';
			$link  = 'index.php?option=com_easyshop&task=' . $task . '&' . $token;
		}
		else
		{
			$link = 'index.php?option=com_easyshop&view=media';
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
