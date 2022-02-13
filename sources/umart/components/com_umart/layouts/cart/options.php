<?php
/**
 
 
 
 
 
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
