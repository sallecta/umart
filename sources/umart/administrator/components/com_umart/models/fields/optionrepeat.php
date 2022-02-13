<?php
/**
 
 
 
 
 
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
                <th colspan="2"><?php echo Text::_('COM_UMART_OPTIONS'); ?></th>
                <th>
                    <button
                            type="button"
                            class="uk-button uk-button-primary uk-button-small"
                            onclick="_umart.events.addOption('#es-option-box');">
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
                               placeholder="<?php echo Text::_((string) $this->element['hintValue'] ?: 'COM_UMART_OPTION_VALUE'); ?>"
                               value="<?php echo @htmlspecialchars($option['value']); ?>"/>
                    </td>
                    <td>
                        <input type="text" name="<?php echo $this->name; ?>[text][]"
                               placeholder="<?php echo Text::_((string) $this->element['hintText'] ?: 'COM_UMART_OPTION_TEXT'); ?>"
                               class="uk-input"
                               value="<?php echo @htmlspecialchars($option['text']); ?>"/>
                    </td>
                    <td>
                        <button type="button" class="uk-button uk-button-small uk-button-danger"
                                onclick="_umart.events.removeParentBox(this, 'tr');">
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
