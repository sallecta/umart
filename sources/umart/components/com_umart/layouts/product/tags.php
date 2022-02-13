<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

/**
 * @var array $displayData
 */
JLoader::register('UmartHelperRoute', UMART_COMPONENT_SITE . '/helpers/route.php');
$tags = explode('|', plg_sytem_umart_main('app')->input->getString('tag'));

?>

<ul class="es-tags" uk-margin>
	<?php foreach ($displayData['tags'] as $tag):
		$tagUrl = UmartHelperRoute::getTagRoute($tag->alias, $tag->language);
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
