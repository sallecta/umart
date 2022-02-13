<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\Addon;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

plg_sytem_umart_main('doc')->addScriptDeclaration('
	_umart.$(document).ready(function ($) {
		var loadFields = function(){		   
		   Joomla.submitform("product.loadFields", document.getElementById("item-form"));
		};
		
		$("#jform_category_id").on("change", loadFields);
	});
');

$this->addOns = plg_sytem_umart_main(Addon::class)
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

<?php HTMLHelper::_('umartui.addTab', Text::_('COM_UMART_PRODUCT_DETAILS', true), [
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
<?php HTMLHelper::_('umartui.endTab'); ?>
<!-- Product prices and tax-->
<?php HTMLHelper::_('umartui.addTab', Text::_('COM_UMART_PRODUCT_PRICES_TAXES', true), [
	'icon' => '<span uk-icon="icon: credit-card"></span>',
]); ?>
<?php echo $this->loadTemplate('price'); ?>
<?php HTMLHelper::_('umartui.endTab'); ?>
<!-- Product media-->
<?php HTMLHelper::_('umartui.addTab', Text::_('COM_UMART_MEDIA', true), [
	'icon' => '<span uk-icon="icon: image"></span>',
]); ?>
<?php echo $this->loadTemplate('media'); ?>
<?php HTMLHelper::_('umartui.endTab'); ?>
<!-- Customfields -->
<?php echo $this->loadTemplate('customfields'); ?>
<?php HTMLHelper::_('umartui.addTab', Text::_('COM_UMART_DISPLAY', true), [
	'icon' => '<span uk-icon="icon: search"></span>',
]); ?>

<?php if ($fieldSets = $this->form->getFieldsets('params')): ?>
	<?php HTMLHelper::_('umartui.openTab', 'params'); ?>

	<?php foreach ($fieldSets as $fieldSet): ?>
		<?php HTMLHelper::_('umartui.addTab', Text::_($fieldSet->label, true)); ?>

        <div class="uk-card uk-card-small uk-card-default uk-card-body es-border">
            <div class="uk-form-horizontal" data-zone-group>
				<?php echo $this->form->renderFieldset($fieldSet->name); ?>
            </div>
        </div>

		<?php HTMLHelper::_('umartui.endTab'); ?>
	<?php endforeach; ?>

	<?php echo HTMLHelper::_('umartui.renderTab', 'tab-left', [
		'responsive' => true,
		'tabId'      => 'params',
		'icon'       => '<span uk-icon="icon: search"></span>',
	]); ?>

	<?php HTMLHelper::_('umartui.endTab'); ?>

<?php endif; ?>

<?php $addOns = trim($this->loadTemplate('addons')); ?>

<?php if (!empty($addOns)): ?>
	<?php HTMLHelper::_('umartui.addTab', Text::_('COM_UMART_ADDONS', true),
		[
			'icon' => '<span uk-icon="icon: paint-bucket"></span>',
		]
	); ?>
	<?php echo $addOns; ?>
	<?php HTMLHelper::_('umartui.endTab'); ?>
<?php endif; ?>

<?php HTMLHelper::_('umartui.addTab', Text::_('COM_UMART_METADATA', true),
	[
		'icon' => '<span uk-icon="icon: link"></span>',
	]
); ?>

<fieldset class="uk-fieldset uk-form-horizontal">
	<?php echo $this->form->renderFieldset('metadata'); ?>
</fieldset>

<?php HTMLHelper::_('umartui.endTab'); ?>

<?php echo HTMLHelper::_('umartui.renderTab'); ?>
<?php echo $this->getFormLayout('foot'); ?>
<div id="es-product-bottom"></div>