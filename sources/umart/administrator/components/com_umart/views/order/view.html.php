<?php
/**
 
 
 
 
 
 */

defined('_JEXEC') or die;

use Umart\Classes\Addon;
use Umart\Classes\Method;
use Umart\Classes\Order;
use Umart\View\ItemView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

class UmartViewOrder extends ItemView
{
	protected $payment = null;
	protected $order = null;

	protected function addToolbar()
	{
		plg_sytem_umart_main('app')->input->set('hidemainmenu', true);
		ToolbarHelper::title(Text::sprintf('COM_UMART_ORDER_EDIT', $this->item->order_code));

		if ($this->item->id)
		{
			$dataOrder = htmlspecialchars(json_encode(
				[
					'id'    => $this->item->id,
					'code'  => $this->item->order_code,
					'email' => $this->item->user_email,
				]
			), ENT_COMPAT, 'UTF-8');
			$bar       = JToolbar::getInstance();
			$bar->appendButton('Custom', '<button class="btn btn-small" type="button" id="es-print-order" data-order="' . $dataOrder . '"><i class="fas fa-print"></i> '
				. Text::_('COM_UMART_PRINT_ORDER') . '</button>', 'email');
			HTMLHelper::_('umart.printOrder', Text::sprintf('COM_UMART_ORDER_PRINT_TITLE_FORMAT', '#es-print-order'));
			/**
			 * @var UmartModelEmails $emailsModel
			 * @var UmartModelEmail  $emailModel
			 */
			$emailsModel = plg_sytem_umart_main('model', 'Emails');
			$emailsModel->setState('filter.published', 1);
			$emailsModel->setState('filter.send_on', ['[ON_NEW_ORDER]', '[ON_ORDER_CHANGE_STATE]', '[ON_ORDER_CHANGE_PAYMENT]']);
			$emailsModel->setState('list.select', 'a.id, a.name');

			if ($items = $emailsModel->getItems())
			{
				$emailModel = plg_sytem_umart_main('model', 'Email');
				$form       = $emailModel->getForm();
				$form->setFieldAttribute('send_from_name', 'class', 'uk-input');
				$form->setFieldAttribute('send_from_email', 'class', 'uk-input');
				$form->setFieldAttribute('send_subject', 'class', 'uk-input');
				$form->setFieldAttribute('send_to_emails', 'class', 'uk-textarea');

				$selectBox = '<div class="uk-modal-header uk-padding-remove-left"><div class="uk-flex uk-flex-middle">'
					. '<select class="uk-select umartui_width-medium@s" id="es-emails">'
					. '<option value="">' . Text::_('COM_UMART_EMAIL_SELECT') . '</option>';

				foreach ($items as $item)
				{
					$selectBox .= '<option value="' . $item->id . '">' . $item->name . '</option>';
				}

				$selectBox .= '</select><a id="es-send-link" class="uk-button uk-button-primary">'
					. '<span uk-icon="icon: mail"></span>' . Text::_('COM_UMART_SEND')
					. '</a></div><a class="uk-modal-close-full" uk-close></a></div>';
				$addOns    = plg_sytem_umart_main(Addon::class)->getAddons('email');
				$addOnHtml = '<div id="es-addon-area">';

				foreach ($addOns as $element => $addOnForm)
				{
					$groups = $addOnForm->getGroup('');

					if (count($groups))
					{
						$addOnHtml .= '<h4 class="uk-h5 uk-heading-bullet uk-margin-remove">'
							. Text::_('PLG_UMART_' . strtoupper($element) . '_ADDON_LABEL') . '</h4>';

						foreach ($groups as $field)
						{
							$addOnHtml .= $field->renderField();
						}
					}
				}

				$addOnHtml .= '</div>';
				echo '<div id="es-email-modal" class="uk-modal-full" uk-modal>'
					. '<div class="uk-modal-dialog uk-modal-body">'
					. $selectBox . '<form method="post" action="'
					. JRoute::_('index.php?option=com_umart&view=order&layout=edit&id=' . $this->item->id, false)
					. '" class="uk-form-horizontal uk-margin"><input type="hidden" name="task" value="order.sendEmail"/>'
					. $form->renderFieldset('templates') . $addOnHtml . HTMLHelper::_('form.token') . '</form></div></div>';

				$bar->appendButton('Custom', '<a href="#" class="btn btn-small" uk-toggle="target: #es-email-modal"><i class="icon-mail"></i> '
					. Text::_('COM_UMART_EMAIL') . '</a>', 'email');
				plg_sytem_umart_main('doc')->addScriptDeclaration('
					document.addEventListener("DOMContentLoaded", function() {
						var $ = _umart.$;
						var sendBody = $("#jform_send_body");
						var contentContainer = sendBody.parent();
						var contentEditor = false;
						
						if (Joomla.editors
							&& Joomla.editors.instances
							&& Joomla.editors.instances.hasOwnProperty("jform_send_body")
						) {
							contentEditor = Joomla.editors.instances.jform_send_body;							
						}
						
						if (contentEditor 
							&& contentContainer.hasClass("js-editor-tinymce")
							&& typeof contentEditor.instance.destroy === "function"
						) {							
							$("#es-email-modal").on("shown", function () {
								contentEditor.instance.destroy();
								Joomla.JoomlaTinyMCE.setupEditors(contentContainer.parent().get(0));							
							});
						}
						
						$("#es-emails").on("change", function() {
							var el = $(this);							
							if(el.val() != ""){
								_umart.ajax(_umart.getData("uri").pathRoot + "/administrator/index.php?option=com_umart&task=order.loadEmailTemplate", {
									umartArea: $("#es-email-modal"),
									emailId: el.val(),
									orderId: ' . (int) $this->item->id . '
								}, function(response){
									var form = $("#es-email-modal form");
									form.find("[name=\'jform[send_from_name]\']").val(response.data.send_from_name);										
									form.find("[name=\'jform[send_from_email]\']").val(response.data.send_from_email);										
									form.find("[name=\'jform[send_to_emails]\']").val(response.data.send_to_emails);										
									form.find("[name=\'jform[send_subject]\']").val(response.data.send_subject);										
									form.find("[name=\'jform[send_body]\']").val(response.data.send_body);
									form.find("#es-addon-area").html(response.data.addon);
									
									if (contentEditor) {
										Joomla.editors.instances.jform_send_body.setValue(response.data.send_body);										
									}
									
									$(document).trigger("runUmartSwitcher");
								});
							}
						});
						
						$("#es-send-link").on("click", function(e){
							e.preventDefault();							
							if($("#es-email-modal form").find("input, select, textarea").es_validate()){
								$("#es-email-modal form").submit();
							}
						});					
						
					});
				');
			}
		}

		ToolbarHelper::cancel('order.cancel', 'JTOOLBAR_CLOSE');
	}

	protected function beforeDisplay()
	{
		$paymentId   = (int) $this->item->payment_id;
		$this->order = plg_sytem_umart_main(Order::class);
		$this->order->load($this->item->id);

		if ($paymentId > 0)
		{
			/** @var Method $methodClass */
			$methodClass = plg_sytem_umart_main(Method::class);

			if ($payment = $methodClass->get($paymentId))
			{
				PluginHelper::importPlugin('umart_payment', $payment->element);
				$this->payment         = clone $payment;
				$registry              = new Registry($this->item->payment_data);
				$this->payment->data   = $registry;
				$registry              = new Registry($this->payment->params);
				$this->payment->params = $registry;
				plg_sytem_umart_main('app')->triggerEvent('onUmartpaymentOrderArea', [$this->order, $this->payment]);
			}
		}
	}
}
