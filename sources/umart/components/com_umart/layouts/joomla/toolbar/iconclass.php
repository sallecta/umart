<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

/**
 * @var array $displayData
 */

$icon     = $displayData['icon'];
$iconsMap = [
	'new'       => 'fa fa-plus es-btn-new',
	'publish'   => 'fa fa-check uk-text-success',
	'unpublish' => 'fa fa-times-circle uk-text-danger',
	'trash'     => 'fa fa-trash uk-text-muted',
	'checkin'   => 'fa fa-check uk-text-warning',
	'edit'      => 'fa fa-edit',
	'apply'     => 'fa fa-check-circle',
	'save'      => 'fa fa-save uk-text-success',
	'save-new'  => 'fa fa-plus uk-text-success',
	'save-copy' => 'fa fa-copy uk-text-success',
	'cancel'    => 'fa fa-times uk-text-danger',
];

echo isset($iconsMap[$icon]) ? $iconsMap[$icon] : $icon;
