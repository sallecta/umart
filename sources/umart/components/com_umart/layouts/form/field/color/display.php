<?php
/**
 
 * @version     1.0.5
 
 
 
 */

defined('_JEXEC') or die;
/**
 * @var array $displayData
 */
extract($displayData);

?>
<div class="es-display-colors umartui_scope">
	<?php if (!empty($colors)): ?>
        <div class="uk-flex-inline es-flex-colors" uk-margin>
			<?php foreach ($colors as $color): ?>
                <label class="es-label-color" style="background-color: <?php echo $color; ?>"></label>
			<?php endforeach; ?>
        </div>
	<?php endif; ?>
</div>
