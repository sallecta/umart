<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$umartConfig  = plg_sytem_umart_main('config');
$multiCurrencies = $umartConfig->get('multi_currencies_mode', '0');
$attribute       = $multiCurrencies ? 'class="uk-select"' : 'class="uk-select" disabled';
$format          = $umartConfig->get('php_date_format', 'Y-m-d') . ' ' . $umartConfig->get('php_time_format', 'H:i:s');
plg_sytem_umart_main('doc')->addScriptDeclaration('
	_umart.$(document).ready(function ($) {
		_umart.setData("currency_box", ' . json_encode(HTMLHelper::_('umart.currencies', 'jform[prices][currency_id][]', $attribute)) . ');
		_umart.setData("multi_currencies", ' . (int) $multiCurrencies . ');        
		_umart.setData("dateTimeFormat", "' . $format . '");        
        $("#es-table-prices .flatpickr").each(function() {
            flatpickr(this, {                
                enableTime: true,
                dateFormat: "Y-m-d H:i:s",
                altFormat: "' . $format . '",
                enableSeconds: true,
	            altInput: true,
	            time_24hr: true,
	            wrap: true,            
                showMonths: 2,
            });
        });        
	});
');

plg_sytem_umart_main()->addLangText([
	'COM_UMART_FROM_DATE',
	'COM_UMART_TO_DATE',
]);

$weekDays = [
	'MONDAY'    => 1,
	'TUESDAY'   => 2,
	'WEDNESDAY' => 3,
	'THURSDAY'  => 4,
	'FRIDAY'    => 5,
	'SATURDAY'  => 6,
];

$weekPriceDays = [];
$firstDay      = (int) CMSFactory::getLanguage()->getFirstDay();

if (0 === $firstDay)
{
	$weekDays = array_merge(['SUNDAY' => 0], $weekDays);
}
else
{
	$weekDays['SUNDAY'] = 0;
}

if (!empty($this->item->weekPriceDays))
{
	foreach ($this->item->weekPriceDays as $weekPriceDay)
	{
		$weekPriceDays[(int) $weekPriceDay->week_day] = (float) $weekPriceDay->price;
	}
}

?>
<div id="price-tax-box">
    <fieldset class="uk-fieldset uk-form-horizontal uk-card uk-card-small uk-card-default uk-card-body">
		<?php echo $this->form->renderField('taxes'); ?>
		<?php echo $this->form->renderField('price'); ?>
        <h5 class="uk-heading-bullet uk-text-uppercase">
			<?php echo Text::_('COM_UMART_PRICE_BY_DAY'); ?>
        </h5>
        <p class="uk-text-meta">
			<?php echo Text::_('COM_UMART_PRICE_BY_DAY_DESC'); ?>
        </p>
        <div class="uk-flex uk-margin">
			<?php foreach ($weekDays as $text => $value): ?>
                <div class="es-week-day-price-container">
                    <label class="uk-display-block">
						<?php echo Text::_($text); ?>
                    </label>
                    <input class="uk-input" type="number" min="0"
                           name="jform[prices][week_price_days][<?php echo $value; ?>]"
                           value="<?php echo isset($weekPriceDays[$value]) ? $weekPriceDays[$value] : ''; ?>"/>
                </div>
			<?php endforeach; ?>
        </div>
        <div class="uk-overflow-auto">
            <table class="uk-table uk-table-striped uk-table-small" id="es-table-prices">
                <thead>
                <tr>
                    <th><?php echo Text::_('COM_UMART_MIN_QUANTITY'); ?></th>
                    <th><?php echo Text::_('COM_UMART_PRODUCT_PRICE'); ?></th>
                    <th><?php echo Text::_('COM_UMART_CURRENCY'); ?></th>
                    <th><?php echo Text::_('COM_UMART_FROM_DATE'); ?></th>
                    <th><?php echo Text::_('COM_UMART_TO_DATE'); ?></th>
                    <th>
                        <button
                                type="button"
                                class="uk-button uk-button-primary uk-button-small"
                                onclick="_umart.events.addProductPrice('#price-tax-box');">
                            <i class="fa fa-plus"></i>
                        </button>
                    </th>
                </tr>
                </thead>
                <tbody>
				<?php if (!empty($this->item->prices)):
					$nullDate = plg_sytem_umart_main('db')->getNullDate();

					foreach ($this->item->prices as $price):

						if ($price->valid_from_date == $nullDate
							|| $price->valid_to_date == $nullDate
						)
						{
							$price->valid_from_date = '';
							$price->valid_to_date   = '';
						}

						if (!empty($price->valid_from_date)
							&& !empty($price->valid_to_date)
						)
						{
							try
							{
								$price->valid_from_date = HTMLHelper::_('date', $price->valid_from_date, 'Y-m-d H:i:s');
								$price->valid_to_date   = HTMLHelper::_('date', $price->valid_to_date, 'Y-m-d H:i:s');
							}
							catch (Exception $e)
							{
								$price->valid_from_date = '';
								$price->valid_to_date   = '';
							}
						}

						?>
                        <tr>
                            <td>
                                <input type="number" name="jform[prices][min_quantity][]"
                                       class="uk-input" min="0"
                                       value="<?php echo (int) $price->min_quantity; ?>"/>
                            </td>
                            <td>
                                <input type="number" name="jform[prices][price][]"
                                       class="price uk-input" min="0"
                                       value="<?php echo (float) $price->price_value; ?>"/>
                            </td>
                            <td>
								<?php echo HTMLHelper::_('umart.currencies', 'jform[prices][currency_id][]', $attribute, $price->currency_id); ?>
                            </td>
                            <td>
                                <div class="flatpickr uk-position-relative">
                                    <a class="uk-form-icon uk-form-icon-flip" uk-icon="icon: close" data-clear></a>
                                    <input type="text" name="jform[prices][valid_from_date][]"
                                           class="datetime-picker uk-input" autocomplete="off"
                                           placeholder="<?php echo Text::_('COM_UMART_FROM_DATE', true); ?>"
                                           value="<?php echo $price->valid_from_date; ?>" data-input/>
                                </div>

                            </td>
                            <td>
                                <div class="flatpickr uk-position-relative">
                                    <a class="uk-form-icon uk-form-icon-flip" uk-icon="icon: close" data-clear></a>
                                    <input type="text" name="jform[prices][valid_to_date][]"
                                           class="datetime-picker uk-input" autocomplete="off"
                                           placeholder="<?php echo Text::_('COM_UMART_TO_DATE', true); ?>"
                                           value="<?php echo $price->valid_to_date; ?>" data-input/>
                                </div>
                            </td>
                            <td>
                                <button type="button" class="uk-button uk-button-danger uk-button-small"
                                        onclick="_umart.events.removeParentBox(this, 'tr');">
                                    <i class="fa fa-times"></i>
                                </button>
                            </td>
                        </tr>
					<?php endforeach; ?>
				<?php endif; ?>
                </tbody>
            </table>
        </div>
    </fieldset>
</div>
