<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$customerUrl    = Route::_(UmartHelperRoute::getCustomerRoute(), false);
$view           = plg_sytem_umart_main('app')->input->get('view');
$isCustomerView = $view === 'customer';
?>
<div id="es-customer-navbar" class="umartui_scope">
    <nav class="uk-navbar-container uk-margin" uk-navbar>
        <div class="uk-navbar-left">
            <div class="uk-navbar-item">
                <img src="<?php echo UMART_MEDIA_URL . '/' . (!empty($this->customer->avatar) ? $this->customer->avatar : 'images/no-avatar.jpg'); ?>"
                     width="50"
                     height="50"
                     alt="<?php echo htmlspecialchars('', ENT_COMPAT, 'UTF-8'); ?>"/>
                <form action="<?php echo Route::_('index.php?option=com_users&task=user.logout', false); ?>"
                      method="post">
                    <div class="es-customer-name">
						<?php echo $this->customer->getName(); ?>
                    </div>
                    <button type="submit" class="uk-button uk-button-link">
						<?php echo Text::_('COM_UMART_LOGOUT'); ?>
                    </button>
                    <input type="hidden" name="return"
                           value="<?php echo base64_encode($customerUrl); ?>"/>
					<?php echo HTMLHelper::_('form.token'); ?>
                </form>
            </div>
            <ul class="uk-navbar-nav">
				<?php foreach ($this->navbar as $page => $navbar):

					$active = '';

					if ($isCustomerView && preg_match('/' . $this->page . '(s|es)?$/', $page))
					{
						$active = ' class="umartui_active"';
					}
                    elseif (!$isCustomerView)
					{
						parse_str($navbar['url'], $results);

						if (!empty($results['view']) && $results['view'] === $view)
						{
							$active = ' class="umartui_active"';
						}
					}

					?>
                    <li<?php echo $active; ?>>
                        <a href="<?php echo Route::_($navbar['url'], false); ?>">
							<?php if (!empty($navbar['icon'])): ?>
								<?php echo HTMLHelper::_('umart.icon', $navbar['icon']) . '&nbsp;'; ?>
							<?php endif; ?>
							<?php echo Text::_($navbar['title']); ?>
							<?php if (!empty($navbar['children'])): ?>
                                <span uk-icon="icon: chevron-down"></span>
							<?php endif; ?>
                        </a>
						<?php if (!empty($navbar['children'])): ?>
                            <div uk-drop="boundary: #es-customer-navbar; pos: bottom-center; boundary-align: false; offset: 10">
                                <div class="uk-card uk-card-default uk-card-small uk-card-body">
                                    <ul class="uk-nav uk-nav-default uk-text-center uk-navbar-dropdown-nav es-customer-nav">
										<?php foreach ($navbar['children'] as $child): ?>
                                            <li>
                                                <a href="<?php echo Route::_($child['url'], false); ?>">
													<?php if (!empty($child['icon'])): ?>
														<?php echo HTMLHelper::_('umart.icon', $child['icon']); ?>
													<?php endif; ?>
													<?php echo Text::_($child['title']); ?>
                                                </a>
                                            </li>
										<?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
						<?php endif; ?>
                    </li>
				<?php endforeach; ?>
            </ul>
        </div>
    </nav>
</div>
