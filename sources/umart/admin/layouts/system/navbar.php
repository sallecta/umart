<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

HTMLHelper::addIncludePath(ES_COMPONENT_ADMINISTRATOR . '/helpers/html');
$input          = easyshop('app')->input;
$view           = $input->getCmd('view');
$extension      = $input->getCmd('extension');
$reflector      = $input->getCmd('reflector');
$filterUserType = $input->getCmd('filter_user_type');
$mediaType      = $input->getCmd('media_type');

if ($extension)
{
	$view .= '&extension=' . $extension;
}

if ($reflector)
{
	$view .= '&reflector=' . $reflector;
}

if ($filterUserType)
{
	$view .= '&filter_user_type=' . $filterUserType;
}

if ($mediaType)
{
	$view .= '&media_type=' . $mediaType;
}

if ($view == 'methods')
{
	$view = $input->get('filter_type');
}

$isMobile = JBrowser::getInstance()->isMobile();
$config   = easyshop('config');

?>
<div id="es-navbar" class="uk-width-1-4@m uk-width-1-5@xl uk-width-1-3@s">
    <div class="uk-card uk-card-small uk-card-default uk-card-body">
        <div class="uk-grid uk-grid-collapse uk-child-width-1-2">
            <div class="es-logo">
				<?php echo Text::_('COM_EASYSHOP'); ?>
                <small class="version">
					<?php echo ES_VERSION; ?>
                </small>
            </div>
			<?php echo $displayData['renderer']->render('notification.notification'); ?>
        </div>

        <ul class="uk-nav uk-nav-default uk-nav-parent-icon" data-uk-nav="multiple: true">
			<?php foreach ($displayData['items'] as $key => $item):
				$hasChildren = isset($item['children']);
				$extraClass = ['parent'];

				if (preg_match('/(' . $view . ')$/i', $item['url']))
				{
					$extraClass[] = 'uk-active';
				}

				if ($hasChildren)
				{
					$subNav       = [];
					$extraClass[] = 'uk-parent' . ($isMobile ? '' : ' uk-open');

					foreach ($item['children'] as $subItem)
					{
						$active   = preg_match('/(' . $view . ')$/i', $subItem['url']) ? 'uk-active' : '';
						$subNav[] = '<li class="' . $active . '">';
						$subNav[] = '    <a href="' . Route::_($subItem['url'], false) . '">';
						$subNav[] = '         ' . HTMLHelper::_('easyshop.icon', $subItem['icon']);
						$subNav[] = '         <span>' . Text::_($subItem['title']) . (isset($subItem['afterTitle']) ? $subItem['afterTitle'] : '') . '</span>';
						$subNav[] = '     </a>';
						$subNav[] = '</li>';

						if ($active)
						{
							$extraClass[] = 'uk-active';
						}
					}
				}
				?>
                <li class="<?php echo implode(' ', array_unique($extraClass)); ?>">
                    <a href="<?php echo Route::_($item['url'], false); ?>">
						<?php echo HTMLHelper::_('easyshop.icon', $item['icon']); ?>
                        <span>
                            <?php echo Text::_($item['title']) . (isset($item['afterTitle']) ? $item['afterTitle'] : ''); ?>
                        </span>
                    </a>
					<?php if ($hasChildren): ?>
                        <ul class="uk-nav-sub">
							<?php echo implode(PHP_EOL, $subNav); ?>
                        </ul>
					<?php endif; ?>
                </li>
			<?php endforeach; ?>
        </ul>
    </div>
</div>
