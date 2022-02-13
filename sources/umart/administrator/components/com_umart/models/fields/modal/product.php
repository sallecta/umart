<?php
/**
 
 
 
 
 
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

class JFormFieldModal_Product extends JFormField
{
	protected $type = 'Modal_Product';

	protected function getInput()
	{
		$products   = [];
		$filterType = $this->getAttribute('filter_type');
		$multiple   = $this->getAttribute('multiple');
		$multiple   = !empty($multiple) && ($multiple == '1' || $multiple == 'true' || $multiple == 'multiple');

		if ($this->value)
		{
			$db    = plg_sytem_umart_main('db');
			$query = $db->getQuery(true)
				->select('a.id, a.name')
				->from($db->quoteName('#__umart_products', 'a'));

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

			$products = (array) $db->loadObjectList();
		}

		if ($onChange = $this->getAttribute('onchange', ''))
		{
			$onChange = ' onchange="' . $onChange . '"';
		}

		$displayData = [
			'id'         => $this->id,
			'name'       => $this->name,
			'value'      => $this->value,
			'hint'       => $this->hint,
			'multiple'   => $multiple,
			'products'   => $products,
			'onChange'   => $onChange,
			'filterType' => $filterType,
		];

		static $modalLoad = [];

		if (!isset($modalLoad[$this->id]))
		{
			JText::script('COM_UMART_LIST_SELECT_WARNING');
			$modalLoad[$this->id] = true;
			$multiple             = $multiple ? 'true' : 'false';
			$js                   = <<<JAVASCRIPT
				_umart.$(document).ready(function ($) {
					var id = '#{$this->id}';
					var select = $(id);
					var modal = $(id + '_modal');
					var iframe = modal.find('iframe');
					var multiple = {$multiple};
					
					modal.on('beforeshow', function() {
						if (!modal.data('iframeHandled')) {
							modal.data('iframeHandled', true);
							iframe.attr(iframe.data('attributes'));
						}
					});
					
					if (multiple) {
						var list = $(id + '-list');
						
						list.on('click', '.es-product-remove', function(){
							$(this).parents('[data-target-id]:eq(0)').remove();
						});
						
						iframe.on('load', function() {
							var contents = $(this).contents();
							var insert = function(link) {
								
								if (!list.find('[data-target-id="' + link.data('productId') + '"]').length) {
									var s = link.parents('td:eq(0)').find('[uk-lightbox]>a').clone();
									var t = list.prev('script').text();
									var img;
									t = t.replace(/\{value\}/gi, link.data('productId'));
									t = t.replace(/\{name\}/gi, $.trim(link.text()));
									t = t.replace(/\{image\}/gi, s.html());
									t = $(t);
									img = t.find('img').removeClass('uk-preserve-width').removeAttr('width');
									
									if (img.data('imageSmallSrc')) {
										img.attr('src', img.data('imageSmallSrc'));
									}
									
									list.append(t);
								}
								
								_umart.umartui.modal(modal).hide();
							};	
							
							contents.find('a[data-product-id]').on('click', function (e) {
								e.preventDefault();
								insert($(this));
							});
							
							modal.find('.es-button-insert').unbind('click').bind('click', function(e){							
								e.preventDefault();
								e.stopPropagation();							
								var chb = contents.find('input[name="cid[]"]:checked');		
													
								if(!chb.length) {								
									alert(Joomla.JText._('COM_UMART_LIST_SELECT_WARNING'));
								} else{
									chb.each(function(){
										var link = $(this).parents('tr').find('a[data-product-id]');
										insert(link);		
									});
																	
									chb.prop('checked', false);									
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
							$(this).contents().find('a[data-product-id]').on('click', function(e) {
								e.preventDefault();
								var el = $(this);
								uId.val(el.data('productId')).trigger('change');
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
			->render('modal.product', $displayData);
	}
}
