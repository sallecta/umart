<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

class JFormFieldOptionRepeat extends JFormField
{
	protected $type = 'OptionRepeat';

	protected function getInput()
	{
		$options = ArrayHelper::arrayUnique((array) $this->value);
		ob_start();
		?>
        <table class="keep-js-table uk-table uk-table-small es-input-100">
            <thead>
            <tr>
                <th><i class="fa fa-sort"></i></th>
                <th colspan="2"><?php echo Text::_('COM_EASYSHOP_OPTIONS'); ?></th>
                <th>
                    <button
                            type="button"
                            class="uk-button uk-button-primary uk-button-small"
                            onclick="_es.events.addOption('#es-option-box');">
                        <i class="fa fa-plus"></i>
                    </button>
                </th>
            </tr>
            </thead>
            <tbody class="uk-sortable" data-uk-sortable>
			<?php foreach ($options as $option): ?>
                <tr>
                    <td>
                        <i class="fa fa-sort"></i>
                    </td>
                    <td>
                        <input type="text" name="<?php echo $this->name; ?>[value][]"
                               class="uk-input"
                               placeholder="<?php echo Text::_((string) $this->element['hintValue'] ?: 'COM_EASYSHOP_OPTION_VALUE'); ?>"
                               value="<?php echo @htmlspecialchars($option['value']); ?>"/>
                    </td>
                    <td>
                        <input type="text" name="<?php echo $this->name; ?>[text][]"
                               placeholder="<?php echo Text::_((string) $this->element['hintText'] ?: 'COM_EASYSHOP_OPTION_TEXT'); ?>"
                               class="uk-input"
                               value="<?php echo @htmlspecialchars($option['text']); ?>"/>
                    </td>
                    <td>
                        <button type="button" class="uk-button uk-button-small uk-button-danger"
                                onclick="_es.events.removeParentBox(this, 'tr');">
                            <i class="fa fa-times"></i>
                        </button>
                    </td>
                </tr>
			<?php endforeach; ?>
            </tbody>
        </table>
		<?php
		return '</div><div>' . ob_get_clean();
	}
}
