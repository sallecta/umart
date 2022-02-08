<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;
/**
 * @var $displayData       array
 * @var $attributes        array
 * @var $image             \stdClass
 * @var $size              string
 * @var $lazyLoad          boolean
 */
extract($displayData);

if (!isset($attributes) || !is_array($attributes))
{
	$attributes = [];
}

$attributes                      = array_merge($attributes, ['itemprop' => 'image']);
$attributes['data-image-origin'] = $image->originBasePath;
$attributes['data-image-size']   = $size;

if (!isset($lazyLoad))
{
	$lazyLoad = easyshop('config', 'image_lazy_load', '0');
}

if ($lazyLoad)
{
	foreach (['tiny', 'small', 'medium', 'large', 'xlarge'] as $sizeSrc)
	{
		$attributes['data-image-' . $sizeSrc . '-src'] = $image->{$sizeSrc};
	}
}
else
{
	$attributes['src'] = $image->{$size};
}

$img = '<img';

foreach ($attributes as $name => $value)
{
	$img .= ' ' . $name . '="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"';
}

echo $img . '/>';
