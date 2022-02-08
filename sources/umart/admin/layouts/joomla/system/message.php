<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('JPATH_BASE') or die;

$msgList = $displayData['msgList'];
$typeMap = [
	'error'   => 'danger',
	'success' => 'success',
	'message' => 'success',
	'warning' => 'warning',
];

?>
<div id="system-message-container" class="uk-scope">
	<?php if (is_array($msgList) && !empty($msgList)) : ?>
		<div id="system-message">
			<?php foreach ($msgList as $type => $msgs) : ?>
				<div class="uk-alert uk-alert-<?php echo $typeMap[$type]; ?>" data-uk-alert>
					<a href="" class="uk-alert-close uk-close" data-uk-close></a>

					<?php if (!empty($msgs)) : ?>
						<h4 class="alert-heading"><?php echo JText::_($type); ?></h4>
						<div>
							<?php foreach ($msgs as $msg) : ?>
								<div class="alert-message"><?php echo $msg; ?></div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
