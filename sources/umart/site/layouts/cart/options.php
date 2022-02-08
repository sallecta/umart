<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;
?>
<div class="product-cart-options">
	<?php if (!empty($displayData['options'])): ?>
        <div class="product-cart-options uk-text-nowrap">
            <ul class="uk-list uk-margin-remove">
				<?php foreach ($displayData['options'] as $id => $option): ?>
                    <li class="product-cart-option">
                        <span><?php echo $option['name']; ?>:</span>
                        <label>
							<?php
							if (strcasecmp($option['name'], $option['value']) !== 0)
							{
								echo $option['text'];
							}

							if ($option['prefix'] != 0.00)
							{
								$prefix = $displayData['currency']->toFormat(abs($option['prefix']), true);

								if ($option['prefix'] > 0.00)
								{
									$prefix = '+' . $prefix;
								}
								else
								{
									$prefix = '-' . $prefix;
								}

								echo '(' . $prefix . ')';
							}
							?>
                        </label>
                    </li>
				<?php endforeach; ?>
            </ul>
        </div>
	<?php endif; ?>
</div>
