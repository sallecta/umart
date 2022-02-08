<?php

/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use ES\Classes\User;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;

class PlgButtonEasyshopImage extends CMSPlugin
{
	protected $autoloadLanguage = true;

	public function onDisplay($name, $asset, $author)
	{
		if (!function_exists('easyshop'))
		{
			return false;
		}

		/** @var User $user */
		$user = easyshop(User::class);

		if ($user->core('admin') || $user->isCustomer())
		{
			$link   = 'index.php?option=com_easyshop&view=media&method=importMedia&media_type=image&tmpl=component&e_id=' . $name;
			$button = new CMSObject;
			$button->set('modal', true);
			$button->set('class', 'btn');
			$button->set('name', 'pictures');
			$button->set('text', Text::_('PLG_EDITORS_XTD_EASYSHOPIMAGE_BUTTON_IMAGE'));
			$button->set('link', $link);
			$button->set('options', '{handler: \'iframe\', size: {x: 800, y: 500}}');

			return $button;
		}

		return false;
	}
}
