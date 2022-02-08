<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use ES\Classes\Currency;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

/** @var $currency Currency */

$moduleClassSfx = htmlspecialchars($params->get('moduleclass_sfx'));
$currency       = easyshop(Currency::class);

if ($currency->isMultiMode())
{
	require ModuleHelper::getLayoutPath('mod_easyshop_currencies', $params->get('layout', 'default'));
}
else
{
	echo '<p>' . Text::_('MOD_EASYSHOP_CURRENCIES_WARNING_NO_MULTI_MODE') . '</p>';
}
