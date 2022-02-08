<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      www.joomtech.net
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

$text  = preg_replace('/\n|\r\n?/', '_EOL_', trim(strip_tags($displayData['text'])));
$text  = preg_replace('/(_EOL_){2,}/', '_EOL__EOL_', $text);
$array = preg_split('/_EOL__EOL_/', $text, -1, PREG_SPLIT_NO_EMPTY);
$text  = '<p>' . implode('</p><p>', $array) . '</p>';
$text  = str_replace('_EOL_', '</br>', $text);

echo $text;
