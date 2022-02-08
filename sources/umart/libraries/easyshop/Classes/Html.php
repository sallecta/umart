<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ES\Classes;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class Html
{
	protected $includePaths = [];

	public function __construct($config = [])
	{
		$template           = easyshop('app')->getTemplate();
		$this->includePaths = [
			JPATH_THEMES . '/' . $template . '/html/com_easyshop',
			JPATH_THEMES . '/' . $template,
		];

		// @since 1.1.6 optional load framework
		if (!empty($config['framework']))
		{
			HTMLHelper::_('easyshop.framework');
		}
	}

	public function addPath($paths)
	{
		settype($paths, 'array');

		foreach ($paths as $path)
		{
			$path = Path::clean($path);

			if (!in_array($path, $this->includePaths))
			{
				$this->includePaths[] = $path;
			}
		}

		return $this;
	}

	/**
	 * Init chosen library
	 * @since 1.1.3
	 */
	public function initChosen()
	{
		static $chosen = false;

		if (!$chosen)
		{
			if (IS_JOOMLA_V4)
			{
				$this->addCss('chosen.css')
					->addJs('chosen.jquery.min.js');
			}
			else
			{
				HTMLHelper::_('script', 'jui/chosen.jquery.min.js', ['version' => 'auto', 'relative' => true]);
				HTMLHelper::_('stylesheet', 'jui/chosen.css', ['version' => 'auto', 'relative' => true]);
			}

			$chosen = true;
			easyshop('doc')->addScriptDeclaration('
				jQuery(function ($) {
					_es.initChosen = function(container) {
						var select,
							options = {
							disable_search_threshold: 10,
							search_contains: true,
							allow_single_deselect: true,
							placeholder_text_multiple: "' . Text::_('JGLOBAL_TYPE_OR_SELECT_SOME_OPTIONS') . '",
							placeholder_text_single: "' . Text::_('JGLOBAL_SELECT_AN_OPTION') . '",
							no_results_text: "' . Text::_('JGLOBAL_SELECT_NO_RESULTS_MATCH') . '"						
						}, list;
						
						if (container){
							list = $(container).find("select");
						} else {
						    list = $(document).find(".es-scope select");
						}
						
						list.each(function(){
							select = $(this);	
							select.chosen("destroy");
													
							if ((select.prop("multiple") || select.find("option").length > 10)
								&& !select.hasClass("not-chosen")
							){
								select.chosen(options);
							}						
						});
					};
					
					_es.initChosen();
					$("body").on("subform-row-add", _es.initChosen);
				});
			');
		}

		return $this;
	}

	public function addJs($file, $version = null)
	{
		return $this->addMedia($file, 'js', $version);
	}

	protected function addMedia($file, $type, $version = null)
	{
		static $mediaList = [];

		if (!in_array($file, $mediaList))
		{
			$mediaList[]  = $file;
			$includePaths = array_merge($this->includePaths, [ES_MEDIA]);

			if ($file = Path::find($includePaths, $type . '/' . $file))
			{
				$file     = Path::clean($file, '/');
				$file     = str_replace(Path::clean(JPATH_ROOT, '/'), Uri::root(true), $file);
				$callBack = $type === 'js' ? 'addScript' : 'addStylesheet';
				call_user_func_array([easyshop('doc'), $callBack], [$file, ['version' => $version ? $version : null]]);
			}
		}

		return $this;
	}

	public function addCss($file, $version = null)
	{
		return $this->addMedia($file, 'css', $version);
	}

	public function initDateTimePicker()
	{
		static $done = false;

		if (!$done)
		{
			$done = true;
			$this->jui(['datepicker', 'slider', 'timepicker-addon'])
				->addCss('ui/ui.css');
		}
	}

	/**
	 * @param array $ui
	 *
	 * @return Html
	 * @since 1.1.0
	 */
	public function jui(array $ui = [])
	{
		$doc = easyshop('doc');

		if (IS_JOOMLA_V4)
		{
			$doc->addScript(ES_MEDIA_URL . '/js/ui/jquery.ui.core.min.js');
		}
		else
		{
			HTMLHelper::_('jquery.ui', ['core']);
		}

		if ($ui)
		{
			$datePicker = in_array('datepicker', $ui);
			$timePicker = in_array('timepicker-addon', $ui);

			foreach ($ui as $component)
			{
				$doc->addScript(ES_MEDIA_URL . '/js/ui/jquery.ui.' . $component . '.min.js');
			}

			if ($datePicker || $timePicker)
			{
				$appendJs = PHP_EOL . 'var dateTimeOpts = ' . json_encode([
						'closeText'          => Text::_('COM_EASYSHOP_UI_DATETIME_CLOSETEXT'),
						'prevText'           => Text::_('COM_EASYSHOP_UI_DATETIME_PREVTEXT'),
						'nextText'           => Text::_('COM_EASYSHOP_UI_DATETIME_NEXTTEXT'),
						'currentText'        => Text::_('COM_EASYSHOP_UI_DATETIME_CURRENTTEXT'),
						'monthNames'         => array_map('trim', explode(',', Text::_('COM_EASYSHOP_UI_DATETIME_MONTHSNAME'))),
						'monthNamesShort'    => array_map('trim', explode(',', Text::_('COM_EASYSHOP_UI_DATETIME_MONTHSNAMESHORT'))),
						'dayNames'           => array_map('trim', explode(',', Text::_('COM_EASYSHOP_UI_DATETIME_DAYNAMES'))),
						'dayNamesShort'      => array_map('trim', explode(',', Text::_('COM_EASYSHOP_UI_DATETIME_DAYNAMESSHORT'))),
						'dayNamesMin'        => array_map('trim', explode(',', Text::_('COM_EASYSHOP_UI_DATETIME_DAYNAMESMIN'))),
						'weekHeader'         => Text::_('COM_EASYSHOP_UI_DATETIME_WEEKHEADER'),
						'dateFormat'         => Text::_('COM_EASYSHOP_UI_DATETIME_DATEFORMAT'),
						'firstDay'           => (int) Text::_('COM_EASYSHOP_UI_DATETIME_FIRSTDAY'),
						'isRTL'              => $doc->direction === 'rtl',
						'showMonthAfterYear' => in_array(strtolower(Text::_('COM_EASYSHOP_UI_DATETIME_SHOWMONTHAFTERYEAR')), ['1', 'true', 'yes'], true),
						'yearSuffix'         => Text::_('COM_EASYSHOP_UI_DATETIME_YEARSUFFIX'),
						'amNames'            => array_map('trim', explode(',', Text::_('COM_EASYSHOP_UI_DATETIME_AMNAMES'))),
						'pmNames'            => array_map('trim', explode(',', Text::_('COM_EASYSHOP_UI_DATETIME_PMNAMES'))),
						'timeFormat'         => Text::_('COM_EASYSHOP_UI_DATETIME_TIMEFORMAT'),
						'timeSuffix'         => Text::_('COM_EASYSHOP_UI_DATETIME_TIMESUFFIX'),
						'timeOnlyTitle'      => Text::_('COM_EASYSHOP_UI_DATETIME_TIMEONLYTITLE'),
						'timeText'           => Text::_('COM_EASYSHOP_UI_DATETIME_TIMETEXT'),
						'hourText'           => Text::_('COM_EASYSHOP_UI_DATETIME_HOURTEXT'),
						'minuteText'         => Text::_('COM_EASYSHOP_UI_DATETIME_MINUTETEXT'),
						'secondText'         => Text::_('COM_EASYSHOP_UI_DATETIME_SECONDTEXT'),
						'millisecText'       => Text::_('COM_EASYSHOP_UI_DATETIME_MILLISECTEXT'),
						'microsecText'       => Text::_('COM_EASYSHOP_UI_DATETIME_MICROSECTEXT'),
						'timezoneText'       => Text::_('COM_EASYSHOP_UI_DATETIME_TIMEZONETEXT'),
					]) . ';';

				if ($datePicker)
				{
					$appendJs .= PHP_EOL . '_es.$.datepicker.setDefaults(dateTimeOpts);';
				}

				if ($timePicker)
				{
					$appendJs .= PHP_EOL . '_es.$.timepicker.setDefaults(dateTimeOpts);';
				}

				$doc->addScriptDeclaration($appendJs);
			}
		}

		return $this;
	}

	public function flatPicker($selector = null, &$options = [])
	{
		static $loaded = false;
		$language = CMSFactory::getApplication()->getLanguage();

		if (!$loaded)
		{
			$this->addCss('flatpickr.min.css')
				->addJs('flatpickr.min.js');
			$loaded   = true;
			$tag      = $language->getTag();
			$langMaps = [
				'vi-VN' => 'vn',
			];

			if (isset($langMaps[$tag]))
			{
				$lang = $langMaps[$tag];
			}
			else
			{
				$lang = explode('-', strtolower($tag), 2)[0];
			}

			if (is_file(ES_MEDIA . '/js/l10n/' . $lang . '.js'))
			{
				$this->addJs('l10n/' . $lang . '.js');
				easyshop('doc')->addScriptDeclaration('flatpickr.localize(flatpickr.l10ns.' . $lang . ');');
			}
		}

		if ($selector)
		{
			$options = array_merge(
				[
					'showMonths'    => 1,
					'mode'          => 'single',
					'dateFormat'    => 'Y-m-d H:i:s',
					'locale'        => [
						'rangeSeparator' => ' >> ',
						'firstDayOfWeek' => (int) $language->getFirstDay(),
					],
					'enableTime'    => true,
					'enableSeconds' => true,
					'altInput'      => true,
					'time_24hr'     => true,
					'wrap'          => true,
				],
				$options
			);

			if (!isset($options['altFormat']))
			{
				$config               = easyshop('config');
				$dateFormat           = $config->get('php_date_format', 'Y-m-d');
				$timeFormat           = $config->get('php_time_format', 'H:i:s');
				$options['altFormat'] = $options['enableTime'] ? $dateFormat . ' ' . $timeFormat : $dateFormat;
			}

			$disableDays = empty($options['disableDays']) ? '[]' : json_encode($options['disableDays']);
			$dataJs      = json_encode($options);
			easyshop('doc')->addScriptDeclaration(<<<JAVASCRIPT
jQuery(document).ready(function($) {
	var jsonData = {$dataJs},
		disableDays = {$disableDays};
		
	if (disableDays.length) {
		jsonData.disable = jsonData.disable || [];		
		jsonData.disable.push(function(date) {
			if (disableDays.indexOf(date.getDay()) !== -1) {
				return true;
			}
		});
	}	
	
	var flatPicker = new flatpickr('{$selector}', jsonData);	
	$('{$selector}').data('flat-picker', flatPicker).trigger('flatPickerInit', [flatPicker]);	 	
});
JAVASCRIPT
			);
		}

		return $this;
	}
}
