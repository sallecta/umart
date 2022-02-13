<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
$count          = count($displayData->pages);
$renderer       = plg_sytem_umart_main('renderer');
$start          = $displayData->start;
$start->text    = '<i class="fa fa-angle-double-left"></i>';
$previous       = $displayData->previous;
$previous->text = '<i class="fa fa-angle-left"></i>';
$next           = $displayData->next;
$next->text     = '<i class="fa fa-angle-right"></i>';
$end            = $displayData->end;
$end->text      = '<i class="fa fa-angle-double-right"></i>';
?>
<div class="uk-margin">
	<?php if ($count): ?>
        <ul class="uk-pagination uk-pagination-left uk-margin-remove">
			<?php echo $renderer->render('pagination.link', $start); ?>
			<?php echo $renderer->render('pagination.link', $previous); ?>
			<?php foreach ($displayData->pages as $key => $page): ?>
				<?php echo $renderer->render('pagination.link', $page); ?>
			<?php endforeach; ?>
			<?php echo $renderer->render('pagination.link', $next); ?>
			<?php echo $renderer->render('pagination.link', $end); ?>
        </ul>
	<?php endif; ?>
</div>
