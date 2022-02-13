<?php
/**
 
 
 
 
 
 */

use Umart\Classes\Utility;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

/**
 * @var array   $displayData
 * @var Utility $utility
 */

extract($displayData);
$utility = plg_sytem_umart_main(Utility::class);

?>
<div class="uk-grid uk-grid-small" data-panel>
	<?php foreach ($form->getFieldset('general') as $field):
		$name = $field->getAttribute('name');
		$type = $field->getAttribute('type');

		switch (strtolower($type))
		{
			case 'ui_datetimepicker':
				$value = $utility->displayDate($field->value);
				break;

			case 'flatpicker':
				$value = $utility->displayPicker($field->value, $field->getDisplayOptions());
				break;

			case 'modal_user':
				$value = empty($customerName) ? Text::_('COM_UMART_GUEST') : $customerName;
				break;

			case 'currency':
				$value = $currency->get('code') . ' (' . $currency->get('name') . ')';
				break;

			default:
				$value = $field->value;
				break;
		}

		$hiddenMaybe = $type == 'hidden' ? ' uk-hidden' : '';

		?>
        <div class="umartui_width-1-2 umartui_width-2-5@s<?php echo $hiddenMaybe; ?>">
			<?php echo $field->label; ?>
        </div>
        <div class="umartui_width-1-2 umartui_width-3-5@s<?php echo $hiddenMaybe; ?>">
            <div>
				<?php if ($name == 'state'): ?>
					<?php echo $displayData['orderStatus'][$value]; ?>
				<?php else: ?>
					<?php echo $value; ?>
				<?php endif; ?>
            </div>
        </div>
	<?php endforeach; ?>
</div>
