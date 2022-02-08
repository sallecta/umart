<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use ES\Classes\Media;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

class JFormFieldModal_User extends FormField
{
	protected $type = 'Modal_User';

	protected function getInput()
	{
		$users    = [];
		$multiple = $this->getAttribute('multiple');
		$vendor   = $this->getAttribute('vendor', 'false') === 'true';
		$multiple = !empty($multiple) && ($multiple == '1' || $multiple == 'true');

		if ($this->value)
		{
			$db    = easyshop('db');
			$query = $db->getQuery(true)
				->select('a.id, a.avatar, u.name')
				->from($db->quoteName('#__easyshop_users', 'a'))
				->innerJoin($db->quoteName('#__users', 'u') . ' ON a.user_id = u.id');

			if ($vendor)
			{
				$query->where('a.vendor = 1');
			}

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

			if ($users = $db->loadObjectList())
			{
				/** @var $mediaClass Media */

				$mediaClass = easyshop(Media::class);
				$rootUrl    = Uri::root(true);

				foreach ($users as $user)
				{
					if ($user->avatar)
					{
						$user->avatar = $rootUrl . '/' . $mediaClass->getResizeImageBasePath($user->avatar, '30x30', true);
					}
					else
					{
						$user->avatar = ES_MEDIA_URL . '/images/no-avatar.jpg';
					}
				}
			}
		}

		if ($onChange = $this->getAttribute('onchange', ''))
		{
			$onChange = ' onchange="' . $onChange . '"';
		}

		$displayData = [
			'id'       => $this->id,
			'name'     => $this->name,
			'value'    => $this->value,
			'hint'     => $this->hint,
			'multiple' => $multiple,
			'users'    => $users,
			'onChange' => $onChange,
			'vendor'   => $vendor,
		];

		static $modalLoad = [];

		if (!isset($modalLoad[$this->id]))
		{
			Text::script('COM_EASYSHOP_LIST_SELECT_WARNING');
			$modalLoad[$this->id] = true;
			$multiple             = $multiple ? 'true' : 'false';

			$js = <<<JAVASCRIPT
				window.usersJs = window.usersJs || {};
				window.usersJs['{$this->id}'] = function() {					
					var $ = _es.$;
					var id = '#{$this->id}';
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
						list.on('click', '.es-user-remove', function(){
							$(this).parents('[data-target-id]:eq(0)').remove();
						});
						
						iframe.on('load', function() {
							var contents = $(this).contents();
							var insert = function(link) {
								
								if (!list.find('[data-target-id="' + link.data('userId') + '"]').length) {									
									var t = list.prev('script').text();
									var img;
									t = t.replace(/\{value\}/gi, link.data('userId'));
									t = t.replace(/\{name\}/gi, $.trim(link.text()));
									t = t.replace(/\{image\}/gi, link.parents('tr:eq(0)').find('.es-user-avatar').html());
									list.append(t);
								}
								
								_es.uikit.modal(modal).hide();
							};	
							
							contents.find('a[data-user-id]').on('click', function (e) {
								e.preventDefault();
								insert($(this));
							});
							
							modal.find('.es-button-insert').unbind('click').bind('click', function(e){							
								e.preventDefault();
								e.stopPropagation();							
								var chb = contents.find('input[name="cid[]"]:checked');		
													
								if(!chb.length) {								
									alert(Joomla.JText._('COM_EASYSHOP_LIST_SELECT_WARNING'));
								} else{
									chb.each(function(){
										var link = $(this).parents('tr').find('a[data-user-id]');
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
						iframe.on('load', function() {
							$(this).contents().find('a[data-user-id]').on('click', function (e) {					
								var el = $(this);
								uId.val(el.data('userId')).trigger('change');
								uName.val($.trim(el.text()));
								toggleDisplay();
								_es.uikit.modal(modal).hide();
							});
						});
					}
				};		
				
				_es.$(document).ready(window.usersJs['{$this->id}']);
JAVASCRIPT;

			easyshop('doc')->addScriptDeclaration($js);
		}

		return easyshop('renderer')
			->refreshDefaultPaths()
			->render('modal.user', $displayData);
	}
}
