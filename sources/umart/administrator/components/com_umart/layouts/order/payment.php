<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
?>
<div class="uk-grid uk-grid-small uk-form-grid" data-panel>
	<?php foreach ($displayData['form']->getFieldset('payment') as $field):
		$name = $field->getAttribute('name');
		$value = $field->value;

		if (in_array($name, ['payment_txn_id', 'shipping_id', 'payment_id']) && empty($value))
		{
			continue;
		}

		switch ($name)
		{
			case 'payment_status':
				$value = $displayData['paymentStatus'][$value];
				break;

			case 'shipping_id':
			case 'payment_id':
				$value = $displayData['utility']->getMethodName($value);
				break;

			case 'total_shipping':
			case 'total_price':
			case 'total_paid':
			case 'total_discount':
			case 'total_fee':
				$value = $displayData['currency']->toFormat($value);
				break;
		}
		?>
		<div class="umartui_width-1-2 umartui_width-2-5@s">
			<?php echo $field->label; ?>
		</div>
		<div class="umartui_width-1-2 umartui_width-3-5@s">
			<?php echo '<div>' . $value . '</div>'; ?>
		</div>
	<?php endforeach; ?>
</div>
