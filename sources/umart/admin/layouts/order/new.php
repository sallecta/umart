<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

/** @var array $displayData */
extract($displayData);

easyshop('doc')->addScriptDeclaration(<<<JAVASCRIPT
_es.$(function ($) {
    var wrap = $('#es-modal-new');
    wrap.on('click', 'a.copy', function (e) {
        e.preventDefault();
        wrap.find('.form-body [name^="jform[billing]"]').each(function () {
            var input = $(this),
                targetName = input.attr('name').toString().replace('jform[billing]', '');
            wrap.find('[name="jform[shipping]' + targetName + '"]')
                .val(input.val())
                .trigger('change');
            _es.initChosen(wrap);
        });
    });
});
JAVASCRIPT
);

?>
<div id="es-modal-new" class="uk-modal-container" uk-modal style="z-index: 9999">
    <div class="uk-modal-dialog" uk-overflow-auto>
        <div class="uk-modal-header">
            <a class="uk-modal-close-default" uk-close></a>
            <h3 class="uk-h4">
				<?php echo Text::_('COM_EASYSHOP_CREATE_NEW_ORDER'); ?>
            </h3>
        </div>
        <div class="form-body uk-modal-body">
            <form action="<?php echo Route::_('index.php?option=com_easyshop&task=order.createNew', false); ?>"
                  method="post" novalidate data-validate>
                <div class="uk-width-xlarge">
                    <fieldset data-zone-group>
                        <div class="uk-panel">
                            <h3 class="uk-panel-title">
                                <i class="fa fa-barcode"></i>
								<?php echo Text::_('COM_EASYSHOP_GENERAL'); ?>
                            </h3>
                            <div class="uk-grid uk-grid-small uk-child-width-1-2@s">
								<?php if ($currency->isMultiMode()): ?>
                                    <div>
										<?php echo $form->getField('currency_id')->input; ?>
                                    </div>
								<?php endif; ?>
                                <div>
									<?php echo $form->getField('user_id')->input; ?>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="uk-grid uk-margin uk-form-horizontal es-input-100">
                    <div class="uk-width-1-2@m">
                        <fieldset data-zone-group>
                            <div class="uk-panel ">
                                <h3 class="uk-panel-title">
                                    <i class="fa fa-map-marker"></i>
									<?php echo Text::_('COM_EASYSHOP_BILLING_ADDRESS'); ?>
                                </h3>
                                <div class="es-billing-form">
									<?php foreach ($form->getGroup('billing') as $field): ?>
										<?php
										$type  = strtolower($field->__get('type'));
										$class = $field->__get('class');

										switch ($type)
										{
											case 'text':
											case 'email':
												$field->__set('class', 'uk-input ' . $class);
												break;

											case 'textarea':
												$field->__set('class', 'uk-textarea ' . $class);
												break;
										}

										echo $field->renderField();
										?>
									<?php endforeach; ?>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <div class="uk-width-1-2@m">
                        <fieldset data-zone-group>
                            <div class="uk-panel">
                                <h3 class="uk-panel-title">
                                    <i class="fa fa-truck"></i>
									<?php echo Text::_('COM_EASYSHOP_SHIPPING_ADDRESS'); ?>
                                    <a href="#" class="uk-text-muted copy">
                                        <span uk-icon="icon: copy"></span>
                                    </a>
                                </h3>
                                <div class="es-shipping-form">
									<?php foreach ($form->getGroup('shipping') as $field): ?>
										<?php
										$type  = strtolower($field->__get('type'));
										$class = $field->__get('class');

										switch ($type)
										{
											case 'text':
											case 'email':
												$field->__set('class', 'uk-input ' . $class);
												break;

											case 'textarea':
												$field->__set('class', 'uk-textarea ' . $class);
												break;
										}

										echo $field->renderField();
										?>
									<?php endforeach; ?>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
                <div class="uk-modal-footer uk-clearfix">
                    <div class="uk-float-right">
                        <button type="submit" class="uk-button uk-button-primary">
                            <i class="fa fa-plus"></i>
							<?php echo Text::_('COM_EASYSHOP_CREATE_NEW_ORDER'); ?>
                        </button>
                        <button type="button" class="uk-button uk-button-default" data-uk-toggle="#es-modal-new">
                            <i class="fa fa-times-circle"></i>
							<?php echo Text::_('COM_EASYSHOP_CANCEL'); ?>
                        </button>
                    </div>
                </div>
				<?php echo HTMLHelper::_('form.token'); ?>
            </form>
        </div>
    </div>
</div>