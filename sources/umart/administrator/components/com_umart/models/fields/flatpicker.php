<?php
/**
 
 
 
 
 
 */

defined('_JEXEC') or die;

use Umart\Classes\Html;
use Umart\Classes\Utility;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

class JFormFieldFlatPicker extends FormField
{
	protected $type = 'FlatPicker';
	protected $mode = 'single';
	protected $numberOfMonths = 1;
	protected $showTime = true;
	protected $required = false;
	protected $minDate = null;
	protected $maxDate = null;
	protected $minTime = null;
	protected $maxTime = null;
	protected $disableDate = null;
	protected $onchange = null;
	protected $hint = null;

	public function getDisplayOptions()
	{
		return [
			'mode'           => $this->mode,
			'showTime'       => $this->showTime,
			'numberOfMonths' => $this->numberOfMonths,
			'minDate'        => $this->minDate,
			'maxDate'        => $this->maxDate,
		];
	}

	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		if ($return = parent::setup($element, $value, $group))
		{
			$this->showTime       = ((string) $this->element['showTime'] ? (string) $this->element['showTime'] : '1') ? true : false;
			$this->numberOfMonths = (int) ((string) $this->element['numberOfMonths'] ? (string) $this->element['numberOfMonths'] : '1');
			$this->required       = (string) $this->element['required'] === 'true';
			$this->minDate        = trim((string) $this->element['minDate']);
			$this->maxDate        = trim((string) $this->element['maxDate']);
			$this->minTime        = trim((string) $this->element['minTime']);
			$this->maxTime        = trim((string) $this->element['maxTime']);
			$this->mode           = trim((string) $this->element['mode']);
			$this->onchange       = trim((string) $this->element['onchange']);
			$this->hint           = trim((string) $this->element['hint']);
			$this->disableDate    = trim((string) $this->element['disableDate']);

			if (!in_array($this->mode, ['single', 'multiple', 'range']))
			{
				$this->mode = 'single';
			}
		}

		return $return;
	}

	public function filter($value, $group = null, Registry $input = null)
	{
		return UmartHelper::filterDate($value, $this->mode);
	}

	protected function getInput()
	{
		$db = CMSFactory::getDbo();

		if (empty($this->value) || $this->value === $db->getNullDate())
		{
			$this->value = '';
		}
		else
		{
			$nullDate = $db->getNullDate();
			$timezone = CMSFactory::getUser()->getTimezone();

			if ('single' === $this->mode)
			{
				try
				{
					$date = CMSFactory::getDate($this->value, 'UTC');
					$date->setTimezone($timezone);
					$this->value = $date->format('Y-m-d H:i:s', true, false);
				}
				catch (Exception $e)
				{
					$this->value = '';
				}
			}
			else
			{
				$delimiter = $this->mode === 'range' ? UmartHelper::PICKER_RANGE_SEPARATOR : UmartHelper::PICKER_MULTIPLE_SEPARATOR;
				$values    = $this->value;
				$dates     = [];

				if (!is_array($values))
				{
					$values = explode($delimiter, $values);
				}

				foreach ($values as $date)
				{
					if (!empty($date) && $date !== $nullDate)
					{
						try
						{
							$date = CMSFactory::getDate($date, 'UTC');
							$date->setTimezone($timezone);
							$dates[] = $date->format('Y-m-d H:i:s', true, false);
						}
						catch (Exception $e)
						{

						}
					}
				}

				$this->value = implode($delimiter, $dates);
			}
		}

		$options = [
			'showMonths' => $this->numberOfMonths,
			'enableTime' => $this->showTime,
			'mode'       => $this->mode,
			'locale'     => [
				'rangeSeparator' => UmartHelper::PICKER_RANGE_SEPARATOR,
			],
		];

		if (!empty($this->minDate))
		{
			$options['minDate'] = $this->parseMinMaxDate($this->minDate);
		}

		if (!empty($this->maxDate))
		{
			$options['maxDate'] = $this->parseMinMaxDate($this->maxDate);
		}

		if (!empty($this->showTime))
		{
			if (!empty($this->minTime))
			{
				$options['minTime'] = $this->minTime;
			}

			if (!empty($this->maxTime))
			{
				$options['maxTime'] = $this->maxTime;
			}
		}

		if (!empty($this->disableDate))
		{
			$dates   = preg_split('/\r\n|\n/', $this->disableDate, -1, PREG_SPLIT_NO_EMPTY);
			$daysMap = [
				'monday'    => 1,
				'tuesday'   => 2,
				'wednesday' => 3,
				'thursday'  => 4,
				'friday'    => 5,
				'saturday'  => 6,
				'sunday'    => 0,
			];

			foreach ($dates as $date)
			{
				$date = strtolower(trim($date));

				if (isset($daysMap[$date]))
				{
					$options['disableDays'][] = $daysMap[$date];
				}
				elseif (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date))
				{
					$options['disable'][] = $date;
				}
			}
		}

		plg_sytem_umart_main(Html::class)->flatPicker('#' . $this->id . '-picker-container', $options);
		$dataJs = json_encode($options);
		$input  = '<input type="text" name="' . $this->name . '" class="' . trim($this->class . ' uk-input') . '" id="' . $this->id . '" value="' . $this->value . '" data-input';
		$dataJs = htmlspecialchars($dataJs, ENT_COMPAT, 'UTF-8');

		if ((string) $this->element['readonly'] === 'true')
		{
			$input .= ' readonly';
		}

		if ((string) $this->element['disabled'] === 'true')
		{
			$input .= ' disabled';
		}

		if ($this->hint)
		{
			$input .= ' placeholder="' . htmlspecialchars(Text::_($this->hint), ENT_COMPAT, 'UTF-8') . '"';
		}

		if ($this->onchange)
		{
			$input .= ' onchange="' . htmlspecialchars($this->onchange, ENT_COMPAT, 'UTF-8') . '"';
		}

		if ($this->required)
		{
			$input .= ' required';
		}
		else
		{
			$input = '<a class="uk-form-icon" uk-icon="icon: close" data-clear></a>' . $input;
		}

		$input .= '/>';

		return <<<HTML
<div class="uk-inline flatpickr" id="{$this->id}-picker-container" data-flatpickr="{$dataJs}">
	<a class="uk-form-icon uk-form-icon-flip" uk-icon="icon: calendar" data-toggle></a>
	{$input}
</div>
HTML;

	}

	protected function parseMinMaxDate($value)
	{
		if (preg_match('/^(\+|-)[0-9]+/', $value))
		{
			/** @var Utility $utility */
			$utility = plg_sytem_umart_main(Utility::class);
			$days    = intval($value);
			$date    = $utility->getDate('now', true);

			if ($days > 0)
			{
				$date->add(new DateInterval('P' . $days . 'D'));
			}
			else
			{
				$date->sub(new DateInterval('P' . abs($days) . 'D'));
			}

			return $date->format('Y-m-d H:i:s', true);
		}

		if (preg_match('/^[0-9]{4}-[0-9]{2}(-[0-9]{2})?$/', $value))
		{
			return $value;
		}

		return $value == 'today' ? 'today' : null;
	}
}
