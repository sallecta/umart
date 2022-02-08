<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

/**
 * @var array $displayData
 */
JLoader::register('EasyshopHelperRoute', ES_COMPONENT_SITE . '/helpers/route.php');
$tags = explode('|', easyshop('app')->input->getString('tag'));

?>

<ul class="es-tags" uk-margin>
	<?php foreach ($displayData['tags'] as $tag):
		$tagUrl = EasyshopHelperRoute::getTagRoute($tag->alias, $tag->language);
		?>
        <li>
            <a href="<?php echo Route::_($tagUrl, false); ?>"
               class="es-tag-display<?php echo in_array($tag->alias, $tags) ? ' active' : ''; ?>"
               data-tag="<?php echo htmlspecialchars($tag->alias, ENT_COMPAT, 'UTF-8') ?>">
				<?php echo $tag->name; ?>
                <span><?php echo $tag->tagCount; ?></span>
            </a>
        </li>
	<?php endforeach; ?>
</ul>
