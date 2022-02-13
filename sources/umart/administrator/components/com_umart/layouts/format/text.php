<?php
/**
 
 
 * @author      github.com/sallecta/umart
 
 
 */

defined('_JEXEC') or die;

$text  = preg_replace('/\n|\r\n?/', '_EOL_', trim(strip_tags($displayData['text'])));
$text  = preg_replace('/(_EOL_){2,}/', '_EOL__EOL_', $text);
$array = preg_split('/_EOL__EOL_/', $text, -1, PREG_SPLIT_NO_EMPTY);
$text  = '<p>' . implode('</p><p>', $array) . '</p>';
$text  = str_replace('_EOL_', '</br>', $text);

echo $text;
