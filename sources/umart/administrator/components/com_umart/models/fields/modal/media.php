<?php
/**
 
 
 
 
 
 */

use Umart\Classes\User;

defined('_JEXEC') or die;

class JFormFieldModal_Media extends JFormField
{
	protected $type = 'Modal_Media';

	protected function getInput()
	{
		$multiple = $this->getAttribute('multiple');
		$multiple = !empty($multiple) && ($multiple == '1' || $multiple == 'true' || $multiple == 'multiple');
		$thumb    = $this->getAttribute('thumb');
		$thumb    = empty($thumb) || $thumb == '1' || $thumb == 'true';
		$readOnly = $this->getAttribute('readonly');

		if ($this->getAttribute('media_type') == 'file' && JPluginHelper::isEnabled('umart', 'file'))
		{
			$mediaType = 'file';
		}
		else
		{
			$mediaType = 'image';
		}

		if ($onChange = $this->getAttribute('onchange', ''))
		{
			$onChange = ' onchange="' . $onChange . '"';
		}

		$userId = plg_sytem_umart_main(User::class)->get()->id;

		if (plg_sytem_umart_main('site'))
		{
			$userPath = 'assets/' . $mediaType . 's/user_customers/' . $userId . '/';
		}
		else
		{
			$userPath = 'assets/' . $mediaType . 's/';
		}

		if ($multiple)
		{
			$value = [];

			if (!empty($this->value))
			{
				foreach ((array) $this->value as $file)
				{
					$value[] = str_replace($userPath, '', $file);
				}
			}
		}
		else
		{
			$value = str_replace($userPath, '', $this->value);
		}

		$displayData = [
			'id'        => $this->id,
			'name'      => $this->name,
			'value'     => $value,
			'multiple'  => $multiple,
			'onChange'  => $onChange,
			'mediaType' => $mediaType,
			'thumb'     => $thumb,
			'readonly'  => $readOnly === '1' || $readOnly === 'true' || $readOnly === 'readonly',
		];

		static $modalLoad = [];

		if (!isset($modalLoad[$this->id]))
		{
			$modalLoad[$this->id] = true;
			$multiple             = $multiple ? 'true' : 'false';
			JText::script('COM_UMART_NO_SELECTED_MEDIA');

			$js = <<<JAVASCRIPT
				_umart.$(document).ready(function($) {
				    var id = '#{$this->id}';
				    var multiple = {$multiple};
				    var select = $(id);
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
						list.on('click', '.es-media-remove', function(e){
							e.preventDefault();
							$(this).parents('[data-file]:eq(0)').remove();
						});
						
						iframe.on('load', function() {
							var contents = $(this).contents();
							contents.find('.file-selected-insert').on('click', function (e) {
								e.preventDefault();
								contents.find('.es-file-selected').each(function () {
									var media = $(this).parent();
									var option, t;
																		
									if(!list.find('[data-file="' + media.data('file') + '"]').length){
										t = list.prev('script').text();										
										t = t.replace(/\{value\}/gi, media.data('file'));
										t = t.replace(/\{name\}/gi, $.trim(media.data('alias')));
										t = t.replace(/\{image\}/gi, media.find('img').get(0).outerHTML);
										list.append(t);
									}
								});
								
								_umart.umartui.modal(modal).hide();
							});
						});	
				    } else {
				        var aSelect = $(id + '_select');
				        var aClear = $(id + '_clear');
				        var uId = $('input' + id);
				        var toggleDisplay = function(){
							if (uId.val()){
								aSelect.addClass('uk-hidden');
								aClear.removeClass('uk-hidden');
							} else{
								aSelect.removeClass('uk-hidden');
								aClear.addClass('uk-hidden');
							}
						};
						
						aClear.on('click', toggleDisplay); 						
						iframe.on('load', function(){
							$(this).contents().find('.file-selected-insert').on('click', function (e) {
								e.preventDefault();		
								var file = $(this).parents('#umart_body').find('.es-file-selected:eq(0)');
								
								if(!file.length){
									alert(Joomla.JText._('COM_UMART_NO_SELECTED_MEDIA'));
								} else {
									var dataFile = file.parent();						
									uId.val(dataFile.data('alias')).trigger('change');
									toggleDisplay();											
									_umart.umartui.modal(modal).hide();		
								}													
							});
						});      							
				    }				       
				});
JAVASCRIPT;
			plg_sytem_umart_main('doc')->addScriptDeclaration($js);
		}

		return plg_sytem_umart_main('renderer')
			->render('modal.media', $displayData);
	}
}
