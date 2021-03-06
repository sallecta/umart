<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\StringHelper;
use Umart\Classes\User;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * @var array $displayData
 */

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
$view      = $displayData;
$state     = $view->getProperty('state');
$app       = plg_sytem_umart_main('app');
$user      = plg_sytem_umart_main(User::class);
$listOrder = $view->escape($state->get('list.ordering'));
$listDirn  = $view->escape($state->get('list.direction'));
$trashed   = $state->get('filter.published') == -2 ? true : false;
$saveOrder = $listOrder == 'a.ordering';
$name      = $view->getName();
$idList    = plg_sytem_umart_main(StringHelper::class)->toSingular($name) . 'List';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_umart&task=' . $name . '.saveOrderAjax&tmpl=component';

	if ($group = $state->get('filter.group'))
	{
		$saveOrderingUrl .= '&filter_group=' . $group;
	}

	if ($type = $state->get('filter.type'))
	{
		$saveOrderingUrl .= '&filter_type=' . $type;
	}

	if ($reflector = $state->get('filter.reflector'))
	{
		$saveOrderingUrl .= '&reflector=' . $reflector;
	}

	if (IS_JOOMLA_V4)
	{
		HTMLHelper::_('draggablelist.draggable');
	}
	else
	{
		HTMLHelper::_('sortablelist.sortable', $idList, 'adminForm', strtolower($listDirn), $saveOrderingUrl);
	}
}

$view->set('layout.storage', [
	'listOrder' => $listOrder,
	'listDirn'  => $listDirn,
	'trashed'   => $trashed,
	'saveOrder' => $saveOrder,
	'user'      => $user,
	'prefix'    => $name . '.'
]);

$navbar = $view->get('navbar');

if ($view->getLayout() != 'modal')
{
	echo(!empty($navbar) ? $navbar : '');
}

?>

<div id="umart_body"
     class="<?php echo !empty($navbar) ? 'umartui_width-3-4@m umartui_width-4-5@xl umartui_width-2-3@s' : 'umartui_width-1-1'; ?>">
    <form action="<?php echo JUri::getInstance()->toString(['path', 'query']); ?>" method="post" name="adminForm"
          id="adminForm">
		<?php if ($view->getProperty('filterForm')): ?>
			<?php echo $view->getRenderer()->render('form.searchtools.filters', ['view' => $view]); ?>
		<?php endif; ?>
        <div id="uk-main-container">
			<?php if (empty($view->getProperty('items'))) : ?>
                <div class="uk-alert uk-alert-warning uk-margin-top" uk-alert>
                    <a href="#" class="uk-alert-close" uk-close></a>
					<?php echo JText::_('COM_UMART_NO_MATCHING_RESULTS'); ?>
                </div>
			<?php endif; ?>
            <div>
                <table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small"
                       id="<?php echo $idList; ?>">
					<?php if (IS_JOOMLA_V4): ?>
                        <caption id="captionTable" class="sr-only">
							<?php echo Text::_('COM_CONTENT_ARTICLES_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                        </caption>
					<?php endif; ?>
                    <tbody <?php if ($saveOrder) : ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php endif; ?>>
