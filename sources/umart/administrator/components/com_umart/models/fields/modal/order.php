<?php
/**
 
 
 
 
 
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

class JFormFieldModal_Order extends JFormField
{
	protected $type = 'Modal_Order';

	protected function getInput()
	{
		$orders   = [];
		$multiple = $this->getAttribute('multiple');
		$multiple = !empty($multiple) && ($multiple == '1' || $multiple == 'true' || $multiple == 'multiple');

		if ($this->value)
		{
			$db    = plg_sytem_umart_main('db');
			$query = $db->getQuery(true)
				->select('a.id, a.order_code')
				->from($db->quoteName('#__umart_orders', 'a'));

			if ($multiple)
			{
				$inKeys = implode(',', ArrayHelper::toInteger($this->value));
				$query->where('a.id IN (' . $inKeys . ')')
					->order('FIELD(a.id, ' . $inKeys . ')');
			}
			else
			{
				$value = is_numeric($this->value) ? (int) $this->value : (int) $this->value[0];
				$query->where('a.id = ' . $value);
			}

			$db->setQuery($query);
			$orders = (array) $db->loadObjectList();
		}

		if ($onChange = $this->getAttribute('onchange', ''))
		{
			$onChange = ' onchange="' . $onChange . '"';
		}

		$displayData = [
			'id'       => $this->id,
			'name'     => $this->name,
			'value'    => $this->value,
			'multiple' => $multiple,
			'orders'   => $orders,
			'onChange' => $onChange,
		];

		static $modalLoad = [];

		if (!isset($modalLoad[$this->id]))
		{
			JText::script('COM_UMART_LIST_SELECT_WARNING');
			$modalLoad[$this->id] = true;
			$multiple             = $multiple ? 'true' : 'false';
			$js                   = <<<JAVASCRIPT
				_umart.$(document).ready(function ($){
					var id = '#{$this->id}';
					var input = $(id);
					var multiple = {$multiple};					
					var modal = $(id + '_modal');
					var iframe = modal.find('iframe');
					
					modal.on('beforeshow', function() {
						if (!modal.data('iframeHandled')) {
							modal.data('iframeHandled', true);
							iframe.attr(iframe.data('attributes'));
						}
					});
					
					if (multiple) {
						var list = $(id + '-list');
						var insert = function (link) {
							if (!list.find('[data-target-id="' + link.data('orderId') + '"]').length) {									
								var t = list.prev('script').text();									
								t = t.replace(/\{value\}/gi, link.data('orderId'));
								t = t.replace(/\{code\}/gi, $.trim(link.text()));
								list.append(t);
							}
								
							_umart.umartui.modal(modal).hide();							
						};
						
						list.on('click', '.es-order-remove', function(){
							$(this).parents('[data-target-id]:eq(0)').remove();
							input.trigger('change');
						});
						
						iframe.on('load', function () {
							var contents = $(this).contents();							
							contents.find('a[data-order-id]').on('click', function(e){
								e.preventDefault();
								insert($(this));
							});
							
							modal.find('.es-button-insert').unbind('click').bind('click', function (e) {							
								e.preventDefault();
								e.stopPropagation();							
								var chb = contents.find('input[name="cid[]"]:checked');		
													
								if(!chb.length) {								
									alert(Joomla.JText._('COM_UMART_LIST_SELECT_WARNING'));
								} else{
									chb.each(function(){
										var link = $(this).parents('tr').find('a[data-order-id]');
										insert(link);		
									});
																	
									chb.prop('checked', false);		
									input.trigger('change');		
								}
							});							
						});
					
					} else {
						var aSelect = $(id + '_select');
						var aClear = $(id + '_clear');
						var uId = $('input' + id);
						var uName = $('input' + id + '_name');	
						var toggleDisplay = function () {
							if (uId.val()) {
								aSelect.addClass('uk-hidden');
								aClear.removeClass('uk-hidden');
							} else {
								aSelect.removeClass('uk-hidden');
								aClear.addClass('uk-hidden');
							}
							
							return false;
						};
						
						aClear.on('click', toggleDisplay);					
						iframe.on('load', function(){
							$(this).contents().find('a[data-order-id]').on('click', function(e){
								e.preventDefault();
								var el = $(this);
								uId.val(el.data('orderId')).trigger('change');
								uName.val($.trim(el.text()));
								toggleDisplay();						
								_umart.umartui.modal(modal).hide();
							});
						});
					}
				});
JAVASCRIPT;

			plg_sytem_umart_main('doc')->addScriptDeclaration($js);
		}

		return plg_sytem_umart_main('renderer')
			->render('modal.order', $displayData);
	}
}
