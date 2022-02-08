<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

$formClass = $params->get('form_layout', 'stacked');
$fieldset  = $form->getFieldset('search');
$count     = count($fieldset);

if ($formClass == 'inline')
{
	$formClass  = 'uk-grid uk-grid-collapse uk-child-width-auto';
	$groupClass = '';
	$hideLabel  = $params->get('hide_label', '1');
}
else
{
	$formClass  = 'uk-form-' . $formClass;
	$groupClass = 'uk-margin-small';
}

?>
<div class="uk-scope mod-easyshop-search <?php $moduleClassSfx; ?>">
    <form action="<?php echo $action; ?>" method="get" class="<?php echo $formClass; ?>">
		<?php foreach ($fieldset as $field):
			if (!empty($hideLabel))
			{
				$field->__set('labelclass', 'uk-hidden');
			}
			?>
            <div class="<?php echo $groupClass; ?>">
				<?php echo $field->__get('label'); ?>
                <div class="uk-form-controls">
					<?php echo $field->__get('input'); ?>
                </div>
            </div>
		<?php endforeach; ?>
        <div class="<?php echo (count($fieldset) == 1 ? 'uk-padding-remove-left ' : '') . $groupClass; ?>">
			<?php if (isset($hideLabel) && !$hideLabel): ?>
                <label class="uk-form-label">&nbsp;</label>
			<?php endif; ?>
            <button type="submit" class="uk-button uk-button-primary no-radius">
                <i class="fa fa-search"></i>
                <span class="uk-visible@s">
                    <?php echo JText::_('COM_EASYSHOP_SEARCH'); ?>
                </span>
            </button>
			<?php foreach ($hidden as $name => $value): ?>
                <input type="hidden" name="<?php echo $name; ?>"
                       value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>"/>
			<?php endforeach; ?>
        </div>
    </form>
    <script>
        _es.$(document).ready(function ($) {
            var form = $('.mod-easyshop-search form');
            form.on('submit', function () {
                var
                    form = $(this),
                    data = form.serializeArray();
                for (var i = 0, n = data.length; i < n; i++) {
                    if (data[i].value == '') {
                        form.find('[name="' + data[i].name + '"]').attr('disabled', 'disabled');
                    }
                }
            });
        });
    </script>
</div>
