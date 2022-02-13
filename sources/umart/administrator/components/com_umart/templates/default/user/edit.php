<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\Addon;
use Umart\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

echo $this->getFormLayout('head');
/** @var Form $form */
$form     = $this->form;
$type     = (int) $form->getValue('user_type');
$addOns   = plg_sytem_umart_main(Addon::class)->getAddons('user', (int) $this->item->id);
$renderer = plg_sytem_umart_main('renderer');
$renderer->setPaths(
	[
		JPATH_THEMES . '/' . plg_sytem_umart_main('app')->getTemplate() . '/html/com_umart/layouts',
		UMART_COMPONENT_SITE . '/layouts',
		UMART_COMPONENT_ADMINISTRATOR . '/layouts',
	]
);

if ($this->item->id)
{
	plg_sytem_umart_main('doc')->addScriptDeclaration('
    _umart.$(document).ready(function($) {
	    $("#es-user-wrap").on("click", "#user-orders .uk-pagination>li>a", function(e){
		    e.preventDefault();		    
		    var 
		        a = $(this), 
		        splits = a.attr("href").toString().split("?"),
		        start = 0;
		    if (splits[1] !== undefined) {
		        start = parseInt(splits[1].replace("start=", ""));
		    }
		    
		    if (isNaN(start)) {
		        start = 0;
		    }
		   
		    if(!a.parent("li").hasClass(".uk-disabled")) {
		        _umart.ajax("' . Uri::base(true) . '/index.php?option=com_umart&task=user.loadOrderList", {
		            userId: ' . (int) $this->item->id . ',
		            start: start,
		            umartArea: $("#user-orders")
		        }, function (response) {
		            if(response.data !== "") {
		                $("#user-orders .uk-card").html(response.data);
		            }
		        });
		    }
	    });
    });
');
}

?>
<div id="es-user-wrap">
    <div class="es-custom-fields uk-form-horizontal">
		<?php echo $this->getFormLayout('general'); ?>
		<?php HTMLHelper::_('umartui.addTab', Text::_('COM_UMART_PROFILE', true), 'info-circle'); ?>
        <fiv class="uk-grid-small uk-child-width-1-2@m" uk-grid>
            <div class="user-fields es-input-100" data-zone-group>
                <div class="uk-card uk-card-default uk-card-small uk-card-body es-border">
					<?php echo $renderer->render('form.fields', [
						'fields' => $form->getGroup('customfields'),
					]); ?>
                </div>
            </div>
            <div id="user-orders">
				<?php if (is_object($this->orderModelList)): ?>
                    <div class="uk-card uk-card-default uk-card-small uk-card-body es-border">
						<?php echo $renderer->render('order.summary', [
							'orders'     => $this->orderModelList->getItems(),
							'pagination' => $this->orderModelList->getPagination(),
						]) ?>
                    </div>
				<?php endif; ?>
            </div>
        </fiv>
		<?php HTMLHelper::_('umartui.endTab'); ?>

		<?php
		if (!empty($addOns))
		{
			HTMLHelper::_('umartui.addTab', Text::_('COM_UMART_ADDONS', true), 'puzzle-piece');
			HTMLHelper::_('umartui.openTab', 'userAddons');

			foreach ($addOns as $element => $form)
			{
				$groups = $form->getGroup('');

				if (empty($groups))
				{
					continue;
				}

				HTMLHelper::_('umartui.addTab', Text::_('PLG_UMART_' . strtoupper($element) . '_ADDON_LABEL'));

				echo '<fieldset class="uk-form-horizontal" data-zone-group>';

				foreach ($groups as $field)
				{
					echo $field->renderField();
				}

				echo '</fieldset>';

				HTMLHelper::_('umartui.endTab');
			}

			echo HTMLHelper::_('umartui.renderTab', 'tab-left', ['responsive' => true, 'tabId' => 'addOns']);
			HTMLHelper::_('umartui.endTab');
		}
		?>

		<?php echo HTMLHelper::_('umartui.renderTab'); ?>
    </div>
	<?php echo $this->getFormLayout('foot'); ?>
</div>
