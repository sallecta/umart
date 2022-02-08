<?php
/**
 * @package     com_easyshop
 * @version     1.0.5
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;
/**
 * @var array $displayData
 */
extract($displayData);

?>
<div class="es-display-colors uk-scope">
	<?php if (!empty($colors)): ?>
        <div class="uk-flex-inline es-flex-colors" uk-margin>
			<?php foreach ($colors as $color): ?>
                <label class="es-label-color" style="background-color: <?php echo $color; ?>"></label>
			<?php endforeach; ?>
        </div>
	<?php endif; ?>
</div>
