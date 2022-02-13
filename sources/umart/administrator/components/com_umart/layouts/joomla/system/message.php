<?php
/**
 
 
 
 
 
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
<div id="system-message-container" class="umartui_scope">
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
