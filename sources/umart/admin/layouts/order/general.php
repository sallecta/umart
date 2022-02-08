<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use ES\Classes\Utility;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

/**
 * @var array   $displayData
 * @var Utility $utility
 */

extract($displayData);
$utility = easyshop(Utility::class);

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
				$value = empty($customerName) ? Text::_('COM_EASYSHOP_GUEST') : $customerName;
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
        <div class="uk-width-1-2 uk-width-2-5@s<?php echo $hiddenMaybe; ?>">
			<?php echo $field->label; ?>
        </div>
        <div class="uk-width-1-2 uk-width-3-5@s<?php echo $hiddenMaybe; ?>">
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
