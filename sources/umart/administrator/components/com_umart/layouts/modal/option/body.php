<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * @var array $displayData
 */

$dataValues = (array) $displayData['value'];
$dataValues = [];

foreach ((array) $displayData['value'] as $dataValue)
{
	if (!empty($dataValue))
	{
		$dataValues[] = $dataValue;
	}
}

$multiCurrencies = $displayData['multiCurrencies'];
$disabled        = '';
$count           = count($dataValues);

if (!$count
	|| ($count === 1 && $dataValues[0] === 'disabled')
)
{
	$disabled = ' disabled="disabled"';
}

if (!$count || $disabled)
{
	$dataValues = [
		[
			'action'       => '+',
			'min_quantity' => '',
			'price'        => '',
			'currency'     => '',
			'images'       => [],
		],
	];
}

?>
<div class="uk-panel uk-card uk-margin">
    <div class="uk-clearfix"></div>
    <div class="uk-text-small uk-text-uppercase uk-margin-small">
        <a href="#es-option-modal-stack" class="uk-link-reset">
            <span><?php echo $displayData['name']; ?></span>
            <span uk-icon="icon: image"></span>
            <span class="uk-badge uk-hidden"></span>
        </a>
    </div>
	<?php foreach ($dataValues as $data):
		$images = empty($data['images']) ? '{}' : json_encode($data['images']);
		?>
        <div class="uk-flex uk-margin-small-bottom"
             data-images="<?php echo @htmlspecialchars($images, ENT_COMPAT, 'UTF-8'); ?>"
             data-option-group<?php echo $disabled; ?>>
            <input name="min_quantity" type="text" class="uk-input uk-form-small"
                   placeholder="<?php echo Text::_('COM_UMART_MIN_QUANTITY'); ?>"
                   value="<?php echo @$data['min_quantity']; ?>"<?php echo $disabled; ?>/>
            <select name="action" class="uk-select uk-form-small"<?php echo $disabled; ?>>
                <option value="+"<?php echo @$data['action'] == '+' ? ' selected' : ''; ?>>+</option>
                <option value="-"<?php echo @$data['action'] == '-' ? ' selected' : ''; ?>>-</option>
                <option value="x"<?php echo @$data['action'] == 'x' ? ' selected' : ''; ?>>x</option>
                <option value="/"<?php echo @$data['action'] == '/' ? ' selected' : ''; ?>>/</option>
            </select>
            <input name="price" type="text" class="uk-input uk-form-small"
                   placeholder="<?php echo Text::_('COM_UMART_PRICE'); ?>"
                   value="<?php echo @$data['price']; ?>"<?php echo $disabled; ?>/>
			<?php if ($multiCurrencies): ?>
				<?php echo HTMLHelper::_('umart.currencies', 'option_currency', 'class="uk-select uk-form-small"', @$data['currency']); ?>
			<?php endif; ?>
            <a class="es-button-remove uk-button uk-button-small uk-button-danger"<?php echo $disabled; ?>>
                <i class="fa fa-times"></i>
            </a>
            <a class="es-button-value-add uk-button uk-button-small uk-button-primary">
                <i class="fa fa-plus"></i>
            </a>
        </div>
	<?php endforeach; ?>
</div>
