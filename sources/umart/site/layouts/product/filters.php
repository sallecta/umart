<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Classes\Utility;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/**
 * @var array   $displayData      List limit box
 * @var array   $displayLimit     List limit box
 * @var array   $filters          Active filter user state
 * @var integer $showToggleButton Show or hide toggle button
 * @var string  $filterKey        Filter key for user state
 */

extract($displayData);
$sortOption    = easyshop(Utility::class)->getOrderingData();
$displayOption = [
	['value' => '', 'text' => Text::_('COM_EASYSHOP_SHOW')],
];

foreach ($displayLimit as $limit)
{
	$displayOption[] = ['value' => $limit, 'text' => $limit];
}

easyshop('doc')->addScriptDeclaration(<<<JAVASCRIPT
function submitFilterForm() {
    document.esFilterForm.return.value = window.location.href;
    document.esFilterForm.submit();
}
JAVASCRIPT
)
?>
<div class="product-filter-bar uk-grid-small uk-margin" uk-grid>
	<?php if ($showToggleButton): ?>
        <div class="product-view-type uk-button-group uk-width-auto uk-visible@s" role="group">
            <button type="button" class="uk-button uk-button-default uk-button-small es-btn-grid"
                    onclick="_es.toggleView('grid', this);" uk-icon="icon: grid"></button>
            <button type="button" class="uk-button uk-button-default uk-button-small es-btn-list"
                    onclick="_es.toggleView('list', this);" uk-icon="icon: list"></button>
        </div>
	<?php endif; ?>
    <div class="product-filter-tools uk-width-expand">
        <form method="post"
              name="esFilterForm"
              action="<?php echo Route::_('index.php?option=com_easyshop&task=filters', false) ?>"
              class="uk-form">
            <select name="filters[sort]" id="product-filter-sort"
                    class="uk-select uk-form-width-medium not-chosen"
                    onchange="submitFilterForm();">
				<?php echo HTMLHelper::_('select.options', $sortOption, 'value', 'text', $filters['sort']); ?>
            </select>
            <select name="filters[display]" id="product-list-display"
                    class="uk-select uk-form-width-xsmall not-chosen"
                    onchange="submitFilterForm()">
				<?php echo HTMLHelper::_('select.options', $displayOption, 'value', 'text', $filters['display']); ?>
            </select>
            <input name="return" type="hidden" value=""/>
            <input name="filterKey" type="hidden" value="<?php echo $filterKey; ?>"/>
        </form>
    </div>
</div>
