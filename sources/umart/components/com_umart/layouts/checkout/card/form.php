<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * @var array    $displayData
 * @var stdClass $payment
 */

extract($displayData);
$acceptCards = $payment->params->get('accepted_cards', []);

?>
<div class="es-card-form uk-form-stack">
	<?php if ($payment->params->get('card_show_holder_name')): ?>
        <input type="text" name="jform[card_holder_name_<?php echo $payment->id; ?>]"
               class="uk-input es-card-holder-name" data-card-holder-name
               placeholder="<?php echo Text::_('COM_UMART_CARD_HOLDER_NAME'); ?>"/>
	<?php endif; ?>
    <input type="text" name="jform[card_number_<?php echo $payment->id; ?>]" class="uk-input es-card-number"
           data-cards="<?php echo htmlspecialchars(json_encode($acceptCards), ENT_COMPAT, 'UTF-8'); ?>" data-card-number
           placeholder="<?php echo Text::_('COM_UMART_CARD_NUMBER'); ?>"/>
    <div class="uk-clearfix" data-date="<?php echo date('Y-m-d'); ?>">
        <select name="jform[card_expiry_month_<?php echo $payment->id; ?>]"
                class="uk-select es-card-expiry-month not-chosen" data-card-expiry-month>
            <option value="1">01</option>
            <option value="2">02</option>
            <option value="3">03</option>
            <option value="4">04</option>
            <option value="5">05</option>
            <option value="6">06</option>
            <option value="7">07</option>
            <option value="8">08</option>
            <option value="9">09</option>
            <option value="10">10</option>
            <option value="11">11</option>
            <option value="12">12</option>
        </select>
        <select name="jform[card_expiry_year_<?php echo $payment->id; ?>]"
                class="uk-select es-card-expiry-year not-chosen" data-card-expiry-year>
			<?php
			$currentYear = (int) date('y', time());
			for ($y = $currentYear, $n = $currentYear + 10; $y <= $n; $y++) : ?>
                <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
			<?php endfor ?>
        </select>
    </div>
    <input type="text" name="jform[card_cvv_<?php echo $payment->id; ?>]" class="uk-input umartui_width-1-2 es-card-cvv"
           data-card-cvv
           placeholder="<?php echo Text::_('COM_UMART_CARD_CVV'); ?>"/>
    <div class="uk-clearfix"></div>
	<?php if (count($acceptCards)): ?>
		<?php echo Text::_('COM_UMART_ACCEPTED_CARDS') . ': ' . implode(', ', array_map(function ($acceptCard) {
				return Text::_('COM_UMART_CARD_' . strtoupper($acceptCard));
			}, $acceptCards)); ?>
	<?php endif; ?>
</div>
