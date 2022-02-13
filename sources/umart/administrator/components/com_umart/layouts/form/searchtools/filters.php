<?php
/**
 
 
 
 
 
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('searchtools.form', '#adminForm', [
	'searchFieldSelector'     => '#filter_search',
	'mainContainerSelector'   => '.es-search-stools',
	//'clearBtnSelector'        => '.es-stools-btn-clear',
	'filterContainerSelector' => '#es-filters',
	'orderFieldSelector'      => '#list_fullordering',
]);

$defaultLimit = plg_sytem_umart_main('app')->get('list_limit', IS_JOOMLA_V4 ? 20 : 25);
plg_sytem_umart_main('doc')->addScriptDeclaration(<<<JS
    _umart.$(document).ready(function($) {
      $('.es-search-stools .es-stools-btn-clear').on('click', function (e) {
          e.preventDefault();
          $('.es-search-stools input, .es-search-stools select').val('');
          $('.es-search-stools select[name="list[limit]"]').val('{$defaultLimit}');
          if ($(this).parents('form').length) {
              $(this).parents('form').submit();
          } else {
              $('#adminForm').submit();
          }
      });
    });
JS
);

/** @var array $displayData */

$filters = $displayData['view']->filterForm->getGroup('filter');
$list    = $displayData['view']->filterForm->getGroup('list');

?>
<div class="uk-inline es-search-stools">
    <div class="uk-grid-collapse uk-flex-middle" uk-grid>
		<?php if (!empty($filters['filter_search'])) : ?>
            <div>
                <div class="uk-inline umartui_width-medium@m">
                    <a class="uk-form-icon es-stools-btn-clear" uk-tooltip
                       title="<?php echo HTMLHelper::_('tooltipText', 'JSEARCH_FILTER_CLEAR'); ?>"
                       uk-icon="icon: close"></a>
                    <button type="submit" class="uk-form-icon uk-form-icon-flip" uk-tooltip
                            title="<?php echo HTMLHelper::_('tooltipText', 'JSEARCH_FILTER_SUBMIT'); ?>"
                            uk-icon="icon: search"></button>
					<?php
					echo $filters['filter_search']->input;
					unset($filters['filter_search']);
					?>
                </div>
            </div>
		<?php endif; ?>
        <div>
			<?php

			if ($list)
			{
				foreach ($list as $field)
				{
					if ($field->getAttribute('name') === 'limit')
					{
						$field->class = 'uk-select uk-display-inline-block not-chosen';
					}

					echo $field->input;
				}
			}

			$stillHaveFilters = !empty($filters);
			?>
        </div>
		<?php if ($stillHaveFilters): ?>
            <div>
                <button type="button" class="uk-button uk-button-primary no-radius" uk-icon="icon: chevron-down"
                        uk-toggle="target: #es-filters">
                    <span class="uk-visible@m"><?php echo JText::_('COM_UMART_FILTERS'); ?></span>
                </button>
            </div>
		<?php endif; ?>
    </div>
	<?php if ($stillHaveFilters): ?>
        <div id="es-filters"
             class="uk-card uk-card-small uk-card-default uk-card-body umartui_width-1-1 uk-position-absolute uk-position-z-index uk-preserve-width es-border es-input-100"
             style="top: 42px; left: 0" hidden>
            <div class="uk-grid-small uk-child-width-1-1 uk-child-width-1-2@m" uk-grid>
				<?php foreach ($filters as $filter): ?>
                    <div<?php echo $filter->getAttribute('type') == 'hidden' ? ' hidden' : ''; ?>>
						<?php if (IS_JOOMLA_V4): ?>
                            <span class="sr-only">
                            <?php echo $filter->label; ?>
                        </span>
						<?php endif; ?>

						<?php echo $filter->input; ?>
                    </div>
				<?php endforeach; ?>
            </div>
        </div>
	<?php endif; ?>
</div>
