<?php

/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
use Umart\Helper\Navbar;

echo Navbar::render();

?>
<div id="es-body" class="uk-width-3-4@m uk-width-4-5@xl uk-width-2-3@s">
    <form action="<?php echo JRoute::_('index.php?option=com_umart&view=market', false); ?>" method="post"
          name="adminForm" id="adminForm">
		<?php if (empty($this->data['packages'])): ?>
            <div class="uk-alert uk-alert-warning uk-margin-top" uk-alert>
                <a href="#" class="uk-alert-close" uk-close></a>
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
            </div>
		<?php else: ?>
            <div class="uk-grid-small uk-child-width-1-4@m uk-child-width-1-5@xl uk-child-width-1-2@s"
                 uk-grid="masonry: true">
				<?php echo $this->getRenderer()->render('market.package', [
					'view' => $this,
				]); ?>
            </div>
		<?php endif; ?>
        <input type="hidden" name="task"/>
    </form>
</div>
