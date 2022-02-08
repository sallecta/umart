<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\Html;
use ES\Classes\Utility;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

abstract class JHtmlEasyshop
{
	public static function currencies($name, $attribs = null, $selected = null, $idtag = false, $translate = false, $selectText = true)
	{
		return HTMLHelper::_('select.genericlist', self::getCurrencies($selectText), $name, $attribs, 'value', 'text', $selected, $idtag, $translate);
	}

	public static function getCurrencies($selectText)
	{
		static $currencies = null;

		if (null === $currencies)
		{
			$option = [];

			if ($selectText)
			{
				$option[] = [
					'value' => '',
					'text'  => Text::_('COM_EASYSHOP_CURRENCY_SELECT'),
				];
			}

			$query = easyshop('db')->getQuery(true)
				->select('a.id AS value, a.name AS text')
				->from(easyshop('db')->qn('#__easyshop_currencies', 'a'))
				->where('a.state = 1')
				->order('a.ordering ASC');
			easyshop('db')->setQuery($query);

			$currencies = array_merge($option, easyshop('db')->loadObjectList());
		}

		return $currencies;
	}

	public static function printOrder($pageTitle = '', $buttonSelector = '#es-print-order')
	{
		static $selectorsLoaded = [];

		if (!in_array($buttonSelector, $selectorsLoaded))
		{
			$selectorsLoaded[] = $buttonSelector;

			easyshop('doc')->addScriptDeclaration('
			_es.$(document).ready(function($) {
				$(document).on("click", "' . $buttonSelector . '", function(e){
					e.preventDefault();					
					var el = $(this);					
					var pageTitle = "' . htmlspecialchars($pageTitle, ENT_COMPAT, 'UTF-8') . '";		
					var dataOrder = el.data("order") || {};			
					
					if (pageTitle == "") {
						pageTitle = el.data("pageTitle");
					}
					
					dataOrder.id = dataOrder.id || el.data("orderId") || 0;
					dataOrder.code = dataOrder.code || el.data("orderCode") || "";
					dataOrder.email = dataOrder.email || el.data("orderEmail") || "";					
					_es.events.printOrder(dataOrder || {}, pageTitle);
				});
			});
		');
		}
	}

	/**
	 * Load assets css and js framework
	 * @since 1.1.6
	 */
	public static function framework()
	{
		static $framework = false;

		if (!$framework)
		{
			$framework = true;

			if (defined('UKUI_PATH'))
			{
				HTMLHelper::_('ukui.framework');
				HTMLHelper::_('ukui.iconsFramework');
			}

			/** @var Html $html */
			$html = easyshop(Html::class);
			$html->addCss('easyshop.default-common.css', ES_VERSION_HASH);

			if (easyshop('site'))
			{
				$html->addCss('easyshop.default-frontend.css', ES_VERSION_HASH);
			}
			else
			{
				$html->addCss('easyshop.default-backend.css', ES_VERSION_HASH);
			}

			$html->addJs('validate.js', ES_VERSION_HASH)
				->addJs('easyshop.js', ES_VERSION_HASH);
		}
	}

	/**
	 * Render UI DateTimePicker
	 * @return String
	 * @var string $name    the name of input
	 * @var string $value   the value of input
	 * @var array  $options DatetimePicker options
	 * @since 1.2.0
	 * @var string $id      the ID of input
	 */

	public static function datetimePicker($id, $name, $value = '', array $options = [])
	{
		$config       = easyshop('config');
		$utilityClass = easyshop(Utility::class);
		$htmlClass    = easyshop(Html::class);
		$htmlClass->initDateTimePicker();
		$dateFormat     = $config->get('php_date_format', 'Y-m-d');
		$timeFormat     = $config->get('php_time_format', 'H:i:s');
		$changeMonth    = isset($options['changeMonth']) ? $options['changeMonth'] : 'false';
		$changeYear     = isset($options['changeYear']) ? $options['changeYear'] : 'false';
		$showTime       = isset($options['showTime']) ? $options['showTime'] : 'true';
		$jsDateFormat   = isset($options['jsDateFormat']) ? $options['jsDateFormat'] : $utilityClass->convertPHPToJSDateTimeFormat($dateFormat);
		$jsTimeFormat   = isset($options['jsTimeFormat']) ? $options['jsTimeFormat'] : $utilityClass->convertPHPToJSDateTimeFormat($timeFormat);
		$yearRange      = isset($options['yearRange']) ? $options['yearRange'] : 'c-10:c+10';
		$numberOfMonths = isset($options['numberOfMonths']) ? $options['numberOfMonths'] : '1';
		$todayButton    = isset($options['todayButton']) ? $options['todayButton'] : 'true';
		$controlType    = isset($options['controlType']) ? $options['controlType'] : 'slider';
		$rangeFromId    = isset($options['rangeFromId']) ? $options['rangeFromId'] : '';
		$rangeToId      = isset($options['rangeToId']) ? $options['rangeToId'] : '';
		$closeOnSelect  = !empty($options['closeOnSelect']) ? 'true' : 'false';
		$inline         = !empty($options['inline']);
		$onchange       = !empty($options['onchange']) ? $options['onchange'] : '';
		$hint           = !empty($options['hint']) ? $options['hint'] : '';
		$dateTimePicker = $showTime === true || $showTime === 'true' || (int) $showTime === 1;
		$doc            = easyshop('doc');
		$isRtl          = $doc->direction == 'rtl' ? 'true' : 'false';
		$noSelectedText = Text::_('COM_EASYSHOP_NO_DATETIME_SELECTED');
		$js             = <<<JAVASCRIPT
_es.$(document).ready(function ($) { 	
    var valueInput = $('#{$id}'); 
    var container = $('#{$id}-container');
    var displayInput = $('#{$id}-display'); 
    var inlineDatepicker = $('#{$id}-inline');
    var fieldInline = $('#{$id}-field-inline');
    var showTime = {$showTime};
    var jsDateFormat = '{$jsDateFormat}';
    var jsTimeFormat = '{$jsTimeFormat}';    
    var options = {        
        defaultDate: null,
        controlType: '{$controlType}',
        altField: '#{$id}',       
	    altFieldTimeOnly: false,
	    showTimepicker: showTime,
	    dateFormat: jsDateFormat,
	    timeFormat: jsTimeFormat,
	    changeMonth: {$changeMonth},
        changeYear: {$changeYear},
        isRTL: {$isRtl},        	
	    yearRange: '{$yearRange}',	    
	    showButtonPanel: {$todayButton},
	    numberOfMonths: {$numberOfMonths},
	    altFormat: 'yy-mm-dd',
	    altTimeFormat: 'HH:mm:ss',	    
	    onSelect: function (selectedDateTimeFormatted, datetimePicker) {
	        container.find('.es-date-display').text(selectedDateTimeFormatted);	        	        
	        var rangeFromId = '{$rangeFromId}';	       
	        var rangeToId = '{$rangeToId}';
	        var dateObj = inlineDatepicker.datetimepicker('getDate');
	        
	        if (!showTime) {	            
	            // Fix click NOW button and no change for value
	            valueInput.val($.datepicker.formatDate('yy-mm-dd', dateObj));
	        } 
	       
	        if (rangeFromId.length) {
	            $('#' + rangeFromId).datetimepicker('option', 'maxDate', dateObj);
	        } else if (rangeToId.length) {
	            $('#' + rangeToId).datetimepicker('option', 'minDate', dateObj);
	        }
	        
	        displayInput.val(selectedDateTimeFormatted).trigger('change'); 
	        
	        if ({$closeOnSelect}) {
	            container.prop('hidden', true);
	        } 
	    }
    };    
    
    inlineDatepicker.datetimepicker(options);
    inlineDatepicker.trigger('onInitDateTimePicker');
    // Fix wrong default time value
    var value = '{$value}';   
    
    if (value.length) {
        var defaultValue;
        
        if (value.indexOf(' ') === -1) {
            defaultValue = new Date(Date.parse(value, 'yy-mm-dd'));
        } else {            
            defaultValue = new Date(Date.parse(value, 'yy-mm-dd HH:mm:ss'));
        }
        
        inlineDatepicker.datetimepicker('setDate', defaultValue);
    } else {
        valueInput.val('');
    }
    
    valueInput.on('change', function () {
        var onchange = '{$onchange}';
        
        if ('' !== onchange) {
            eval(onchange);
        }
    });
    
    container.find('.es-icon-refresh').on('click', function (e)   {
        e.preventDefault();
        container.find('.es-date-display').text('{$noSelectedText}');
        displayInput.val('');
	    valueInput.val('');
    });    
    
    container.find('.es-icon-check, .es-icon-close').on('click', function (e) {
        e.preventDefault();
        
        if ($(this).hasClass('es-icon-check')) {
            valueInput.trigger('change');
        }
        
        container.prop('hidden', true);       
    });   
    
    setTimeout(function() {
        inlineDatepicker.find('select').chosen('destroy');
    }, 500);    
});
JAVASCRIPT;
		$doc->addScriptDeclaration($js);
		$displayData = [
			'id'          => $id,
			'name'        => $name,
			'value'       => $value,
			'hint'        => $hint,
			'required'    => !empty($options['required']),
			'inline'      => $inline,
			'valueFormat' => '',
		];

		if (!empty($value))
		{
			$displayFormat = $dateFormat;
			$valueFormat   = 'Y-m-d';

			if ($dateTimePicker)
			{
				$displayFormat .= ' ' . $timeFormat;
				$valueFormat   .= ' H:i:s';
			}

			try
			{
				$date = CMSFactory::getDate($value, 'UTC');
				$date->setTimezone(CMSFactory::getUser()->getTimezone());
				$displayData['value']       = $date->format($valueFormat);
				$displayData['valueFormat'] = $date->format($displayFormat);
			}
			catch (Exception $e)
			{

			}
		}

		// Layout ID for custom layout, this is not applied for form field
		$layoutId = isset($options['layoutId']) ? $options['layoutId'] : 'ui.datetimepicker';

		return easyshop('renderer')->render($layoutId, $displayData);
	}

	public static function gridCheckAll($name = 'checkall-toggle', $tip = 'JGLOBAL_CHECK_ALL', $action = 'Joomla.checkAll(this)')
	{
		HTMLHelper::_('behavior.core');

		return '<input type="checkbox" name="' . $name . '" value="" class="uk-checkbox" title="' . HTMLHelper::_('tooltipText', $tip)
			. '" onclick="' . $action . '" uk-tooltip/>';
	}

	public static function gridId($rowNum, $recId, $checkedOut = false, $name = 'cid', $stub = 'cb')
	{
		return $checkedOut ? '' : '<input type="checkbox" id="' . $stub . $rowNum . '" class="uk-checkbox" name="' . $name . '[]" value="' . $recId
			. '" onclick="Joomla.isChecked(this.checked);" />';
	}

	public static function gridPublished($state, $rowNum, $prefix, $canChange, $stub = 'cb')
	{
		$icon     = $state ? 'check' : 'close';
		$disabled = $canChange ? '' : ' disabled="disabled"';
		$title    = $state ? Text::_('JLIB_HTML_UNPUBLISH_ITEM', true) : Text::_('JLIB_HTML_PUBLISH_ITEM', true);
		$task     = $prefix . ($state ? 'unpublish' : 'publish');
		$class    = $state ? 'uk-text-success' : 'uk-text-danger';

		return <<<HTML
			<a class="uk-icon-link {$class}" href="javascript:void(0);" onclick="return Joomla.listItemTask('{$stub}{$rowNum}','{$task}')" uk-tooltip="{$title}"{$disabled}>
				<span uk-icon="icon: {$icon}"></span>
			</a>
HTML;

	}

	public static function gridCheckedOut($rowNum, $editorName, $checkedTimeOut, $prefix, $canCheckin, $checkbox = 'cb')
	{
		$title    = '<strong>' . Text::_('JLIB_HTML_CHECKIN', true) . '</strong>: ' . $editorName . '<br />' . easyshop(Utility::class)->displayDate($checkedTimeOut);
		$disabled = $canCheckin ? '' : ' disabled="disabled"';

		return <<<HTML
			<a class="uk-icon-link uk-text-warning" href="javascript:void(0);" onclick="return Joomla.listItemTask('{$checkbox}{$rowNum}','{$prefix}checkin')" uk-tooltip title="{$title}"{$disabled}>
				<span uk-icon="icon: lock"></span>
			</a>
HTML;

	}

	/**
	 * @param     $icon
	 * @param int $width
	 * @param int $height
	 *
	 * @return mixed
	 * @since      1.3.0
	 * @deprecated 2.0.0
	 */

	public function svgIcon($icon, $width = 20, $height = 20)
	{
		return self::icon($icon, $width, $height);
	}

	public static function icon($icon, $width = 20, $height = 20)
	{
		static $iconsLoaded = [];
		$key = preg_replace('/[^0-9a-z\-_\.\:]/i', '', $icon . ':' . $width . ':' . $height);

		if (!isset($iconsLoaded[$key]))
		{
			$rootUrl  = Uri::root(true);
			$isEsIcon = strpos($icon, 'es-icon-') === 0;
			$isSvgExt = strpos($icon, '.svg') !== false;

			if ($isEsIcon || $isSvgExt)
			{
				// B/C
				if ($isSvgExt)
				{
					$icon = 'es-icon-' . basename($icon, '.svg');
				}

				$iconsLoaded[$key] = <<<XHTML
					<svg class="{$icon}" width="{$width}" height="{$height}">
    					<use xlink:href="{$rootUrl}/media/com_easyshop/images/icons.svg#{$icon}"></use>
					</svg>
XHTML;

				return $iconsLoaded[$key];
			}

			if (strpos($icon, '/') === 0
				|| strpos($icon, 'http') === 0
			)
			{
				$iconsLoaded[$key] = '<img src="' . $icon . '" width="' . $width . '" height="' . $height . '" uk-svg/>';
			}
			else
			{
				if (strpos($icon, '<') === 0)
				{
					$iconsLoaded[$key] = $icon;
				}
				elseif (strpos($icon, 'fa') === 0)
				{
					$iconsLoaded[$key] = '<i class="fa ' . $icon . '"></i>';
				}
				else
				{
					$iconsLoaded[$key] = '<span uk-icon="icon: ' . $icon . '"></span>';
				}
			}
		}

		return $iconsLoaded[$key];
	}
}
