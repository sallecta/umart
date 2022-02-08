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

/**
 * @var array   $displayData
 * @var integer $countdownLabel
 */
extract($displayData);

if ($displayData['product']->expireDate instanceof DateTime): ?>
    <div class="es-product-countdown">
        <div class="uk-grid-small uk-child-width-auto" uk-grid
             uk-countdown="date: <?php echo $displayData['product']->expireDate->format('c'); ?>">
			<?php if (!in_array($countdownLabel, [0, 2, 3])): ?>
                <div>
                    <div class="uk-countdown-number uk-countdown-days"
                         title="<?php echo Text::_('COM_EASYSHOP_DAYS', true); ?>" uk-tooltip></div>
					<?php if ($showLabel): ?>
                        <div class="uk-countdown-label uk-margin-small uk-text-center uk-visible@s">
							<?php echo Text::_('COM_EASYSHOP_DAYS'); ?>
                        </div>
					<?php endif; ?>
                </div>
                <div class="uk-countdown-separator">:</div>
			<?php endif; ?>

			<?php if (!in_array($countdownLabel, [0, 3])): ?>
                <div>
                    <div class="uk-countdown-number uk-countdown-hours"
                         title="<?php echo Text::_('COM_EASYSHOP_HOURS', true); ?>" uk-tooltip></div>
					<?php if ($showLabel): ?>
                        <div class="uk-countdown-label uk-margin-small uk-text-center uk-visible@s">
							<?php echo Text::_('COM_EASYSHOP_HOURS'); ?>
                        </div>
					<?php endif; ?>
                </div>
                <div class="uk-countdown-separator">:</div>
			<?php endif; ?>

            <div>
                <div class="uk-countdown-number uk-countdown-minutes"
                     title="<?php echo Text::_('COM_EASYSHOP_MINUTES', true); ?>" uk-tooltip></div>
				<?php if ($showLabel): ?>
                    <div class="uk-countdown-label uk-margin-small uk-text-center uk-visible@s">
						<?php echo Text::_('COM_EASYSHOP_MINUTES'); ?>
                    </div>
				<?php endif; ?>
            </div>
            <div class="uk-countdown-separator">:</div>
            <div>
                <div class="uk-countdown-number uk-countdown-seconds"
                     title="<?php echo Text::_('COM_EASYSHOP_SECONDS', true); ?>" uk-tooltip></div>
				<?php if ($showLabel): ?>
                    <div class="uk-countdown-label uk-margin-small uk-text-center uk-visible@s">
						<?php echo Text::_('COM_EASYSHOP_SECONDS'); ?>
                    </div>
				<?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
