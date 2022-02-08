<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\Addon;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

easyshop('doc')->addScriptDeclaration('
	_es.$(document).ready(function ($) {
		var loadFields = function(){		   
		   Joomla.submitform("product.loadFields", document.getElementById("item-form"));
		};
		
		$("#jform_category_id").on("change", loadFields);
	});
');

$this->addOns = easyshop(Addon::class)
	->getAddons('product', (int) $this->item->id);
echo $this->getFormLayout('head');

?>
<!-- Product details-->
<?php echo $this->getRenderer()->render(
	'view.item.name-alias',
	[
		'form'               => $this->form,
		'translationEnabled' => true,
	]
); ?>

<?php HTMLHelper::_('ukui.addTab', Text::_('COM_EASYSHOP_PRODUCT_DETAILS', true), [
	'icon' => '<span uk-icon="icon: info"></span>',
]); ?>
<div id="es-product-details" class="es-input-100 es-border uk-margin">
    <div class="uk-grid uk-grid-small uk-child-width-1-5@m uk-child-width-1-6@xl uk-child-width-1-3@s">
		<?php foreach ($this->form->getFieldset('details') as $field): ?>
			<?php echo $field->renderField(); ?>
		<?php endforeach; ?>
    </div>
</div>
<div class="uk-padding-small uk-background-muted es-border uk-margin-small-bottom">
	<?php echo $this->form->getInput('summary'); ?>
</div>
<div class="uk-padding-small uk-background-muted es-border uk-clearfix">
	<?php echo $this->form->getInput('description'); ?>
</div>
<?php HTMLHelper::_('ukui.endTab'); ?>
<!-- Product prices and tax-->
<?php HTMLHelper::_('ukui.addTab', Text::_('COM_EASYSHOP_PRODUCT_PRICES_TAXES', true), [
	'icon' => '<span uk-icon="icon: credit-card"></span>',
]); ?>
<?php echo $this->loadTemplate('price'); ?>
<?php HTMLHelper::_('ukui.endTab'); ?>
<!-- Product media-->
<?php HTMLHelper::_('ukui.addTab', Text::_('COM_EASYSHOP_MEDIA', true), [
	'icon' => '<span uk-icon="icon: image"></span>',
]); ?>
<?php echo $this->loadTemplate('media'); ?>
<?php HTMLHelper::_('ukui.endTab'); ?>
<!-- Customfields -->
<?php echo $this->loadTemplate('customfields'); ?>
<?php HTMLHelper::_('ukui.addTab', Text::_('COM_EASYSHOP_DISPLAY', true), [
	'icon' => '<span uk-icon="icon: search"></span>',
]); ?>

<?php if ($fieldSets = $this->form->getFieldsets('params')): ?>
	<?php HTMLHelper::_('ukui.openTab', 'params'); ?>

	<?php foreach ($fieldSets as $fieldSet): ?>
		<?php HTMLHelper::_('ukui.addTab', Text::_($fieldSet->label, true)); ?>

        <div class="uk-card uk-card-small uk-card-default uk-card-body es-border">
            <div class="uk-form-horizontal" data-zone-group>
				<?php echo $this->form->renderFieldset($fieldSet->name); ?>
            </div>
        </div>

		<?php HTMLHelper::_('ukui.endTab'); ?>
	<?php endforeach; ?>

	<?php echo HTMLHelper::_('ukui.renderTab', 'tab-left', [
		'responsive' => true,
		'tabId'      => 'params',
		'icon'       => '<span uk-icon="icon: search"></span>',
	]); ?>

	<?php HTMLHelper::_('ukui.endTab'); ?>

<?php endif; ?>

<?php $addOns = trim($this->loadTemplate('addons')); ?>

<?php if (!empty($addOns)): ?>
	<?php HTMLHelper::_('ukui.addTab', Text::_('COM_EASYSHOP_ADDONS', true),
		[
			'icon' => '<span uk-icon="icon: paint-bucket"></span>',
		]
	); ?>
	<?php echo $addOns; ?>
	<?php HTMLHelper::_('ukui.endTab'); ?>
<?php endif; ?>

<?php HTMLHelper::_('ukui.addTab', Text::_('COM_EASYSHOP_METADATA', true),
	[
		'icon' => '<span uk-icon="icon: link"></span>',
	]
); ?>

<fieldset class="uk-fieldset uk-form-horizontal">
	<?php echo $this->form->renderFieldset('metadata'); ?>
</fieldset>

<?php HTMLHelper::_('ukui.endTab'); ?>

<?php echo HTMLHelper::_('ukui.renderTab'); ?>
<?php echo $this->getFormLayout('foot'); ?>
<div id="es-product-bottom"></div>