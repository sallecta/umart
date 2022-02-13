<?php

/**
 
 
 
 
 
 */

use Umart\Classes\Utility;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;
/** @var array $displayData */
$utility = plg_sytem_umart_main(Utility::class);
?>
<div class="uk-modal-body">
    <h1 class="uk-text-uppercase uk-heading-bullet uk-text-lead">
		<?php echo Text::_('COM_UMART_THE_RECENT_LOGS'); ?>
    </h1>
    <ul class="uk-list uk-list-line uk-list-divider es-log-notification">
		<?php foreach ($displayData['logs'] as $log): ?>
            <li>
                <div class="uk-grid uk-grid-small">
                    <div class="umartui_width-expand">
						<?php

						if (!empty($log->sprintf_data))
						{
							$sprintsData = json_decode($log->sprintf_data, true);
							array_unshift($sprintsData, $log->string_key);
							echo call_user_func_array('Joomla\\CMS\\Language\\Text::sprintf', array_values($sprintsData));
						}
						else
						{
							echo Text::_($log->string_key);
						}

						?>
                        <div class="uk-text-meta">
                            <span uk-icon="icon: calendar"></span>
							<?php echo $utility->displayDate($log->created_date, true, true); ?>
							<?php if (!empty($log->author)): ?>
                                <span uk-icon="icon: user"></span>
								<?php echo $log->author . '<small>(' . $log->username . ')</small>'; ?>
							<?php endif; ?>
                        </div>
                    </div>
                    <div class="umartui_width-auto">
						<?php echo plg_sytem_umart_main('prepare.logip', $log->ip); ?>
                    </div>
                </div>
            </li>
		<?php endforeach; ?>
    </ul>
</div>
