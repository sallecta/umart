<?php

/**
 
 
 
 
 
 */

defined('_JEXEC') or die;

use Umart\Classes\User;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;

class PlgButtonUmartImage extends CMSPlugin
{
	protected $autoloadLanguage = true;

	public function onDisplay($name, $asset, $author)
	{
		if (!function_exists('umart'))
		{
			return false;
		}

		/** @var User $user */
		$user = umart(User::class);

		if ($user->core('admin') || $user->isCustomer())
		{
			$link   = 'index.php?option=com_umart&view=media&method=importMedia&media_type=image&tmpl=component&e_id=' . $name;
			$button = new CMSObject;
			$button->set('modal', true);
			$button->set('class', 'btn');
			$button->set('name', 'pictures');
			$button->set('text', Text::_('PLG_EDITORS_XTD_UMART_IMAGE_BUTTON_IMAGE'));
			$button->set('link', $link);
			$button->set('options', '{handler: \'iframe\', size: {x: 800, y: 500}}');

			return $button;
		}

		return false;
	}
}
