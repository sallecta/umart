<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\Html;
use Umart\Classes\Utility;
use Umart\Helper\Navbar;
use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
plg_sytem_umart_main(Html::class)
	->initChosen()
	->addCss('daterangepicker.css')
	->addJS('moment.min.js')
	->addJS('daterangepicker.min.js')
	->addJS('chart.min.js')
	->addJS('progressbar.min.js');
$navbar        = Navbar::render();
$currencyClass = $this->currencyClass;
$cDefaultId    = $currencyClass->get('id');
$renderer      = $this->getRenderer();
$nowDate       = $this->utility->getDate()->format('Y-m-d H:i:s', true);
$token         = Session::getFormToken();
$url           = Route::_('index.php?option=com_umart');
$firstDay      = (int) CMSFactory::getApplication()->getLanguage()->getFirstDay();
$jsDateFormat  = plg_sytem_umart_main(Utility::class)->convertPHPToMomentFormat($this->config->get('php_date_format', 'Y-m-d'));
Text::script('COM_UMART_TODAY');
Text::script('COM_UMART_YESTERDAY');
Text::script('COM_UMART_THIS_WEEK');
Text::script('COM_UMART_LAST_WEEK');
Text::script('COM_UMART_THIS_MONTH');
Text::script('COM_UMART_LAST_MONTH');
Text::script('COM_UMART_LAST_3_MONTHS');
Text::script('COM_UMART_LAST_6_MONTHS');
Text::script('COM_UMART_THIS_YEAR');
Text::script('COM_UMART_LAST_YEAR');
Text::script('COM_UMART_LAST_2_YEARS');
Text::script('COM_UMART_APPLY');
Text::script('COM_UMART_CANCEL');
Text::script('COM_UMART_CUSTOM');
plg_sytem_umart_main('doc')->addScriptDeclaration(<<<JAVASCRIPT
_umart.$(function ($) {
    var esChart = $('#es-chart'),
        ranges = {},
        dates = $('input[name="dates"]');       
    ranges[Joomla.Text._('COM_UMART_TODAY')] = [moment(), moment()];
    ranges[Joomla.Text._('COM_UMART_YESTERDAY')] = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
    ranges[Joomla.Text._('COM_UMART_THIS_WEEK')] = [moment().startOf('isoWeek'), moment()];
    ranges[Joomla.Text._('COM_UMART_LAST_WEEK')] = [moment().subtract(1, 'week').startOf('isoWeek'), moment().subtract(1, 'week').endOf('isoWeek')];
    ranges[Joomla.Text._('COM_UMART_THIS_MONTH')] = [moment().startOf('month'), moment().endOf('month')];
    ranges[Joomla.Text._('COM_UMART_LAST_MONTH')] = [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];
    ranges[Joomla.Text._('COM_UMART_LAST_3_MONTHS')] = [moment().subtract(3, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];
    ranges[Joomla.Text._('COM_UMART_LAST_6_MONTHS')] = [moment().subtract(6, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];
    ranges[Joomla.Text._('COM_UMART_THIS_YEAR')] = [moment().startOf('year'), moment().endOf('year')];
    ranges[Joomla.Text._('COM_UMART_LAST_YEAR')] = [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')];
    ranges[Joomla.Text._('COM_UMART_LAST_2_YEARS')] = [moment().subtract(2, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')];
    dates.daterangepicker({
        ranges: ranges,
        alwaysShowCalendars: true,
        showDropdowns: true,
        startDate: moment().startOf('month'),
        endDate: moment().endOf('month'),
        locale: {
            format: '{$jsDateFormat}',
            applyLabel: Joomla.Text._('COM_UMART_APPLY'),
            cancelLabel: Joomla.Text._('COM_UMART_CANCEL'),
            customRangeLabel: Joomla.Text._('COM_UMART_CUSTOM'),
            firstDay: {$firstDay},
        }
    });
    
    window.esLoadChartData = function () {
        var el = $(this),
            picker = dates.data('daterangepicker');
        if (picker) {                   
            _umart.ajax('{$url}', {
                task: 'dashboard.loadChartData',
                startDate: picker.startDate.format('YYYY-MM-DD'),            
                endDate: picker.endDate.format('YYYY-MM-DD'),            
                currencyId: esChart.find('.es-currency-id').val(),
                umartArea: $('#umart_body'),
                '{$token}': 1
            }, function (response) {
                $('#es-chart .main-chart').html(response.data.chart);
                $('#es-latest-orders table').html($(response.data.latestOrderHTML).html());
                if (el.hasClass('es-currency-id')) {
                    $('#es-dashboard .es-tile').html($(response.data.tiles).html());
                }
            });
        }
    };
    
    dates.on('change', window.esLoadChartData);    
    esChart.on('change', '.es-currency-id', esLoadChartData);
    esLoadChartData();    
});
JAVASCRIPT
);

echo $navbar;
?>

<div id="umart_body"
     class="<?php echo empty($navbar) ? 'umartui_width-1-1' : 'umartui_width-3-4@m umartui_width-4-5@xl umartui_width-2-3@s' ?>">
    <form id="es-dashboard">
		<?php echo $this->tiles; ?>
        <div id="es-chart" class="uk-visible@m uk-margin">
            <div class="uk-card uk-card-small uk-card-body uk-card-default">
                <div class="title-bar uk-flex uk-flex-middle">
					<?php if ($currencyClass->isMultiMode()): ?>
                        <select class="es-currency-id uk-select uk-margin-small-right">
							<?php foreach ($currencyClass->getList() as $currency): ?>
                                <option value="<?php echo $currency->id; ?>"
									<?php echo $currency->id == $cDefaultId ? ' selected' : ''; ?>>
									<?php echo $currency->name; ?>
                                </option>
							<?php endforeach; ?>
                        </select>
					<?php endif; ?>
                    <div class="uk-inline">
                        <a class="uk-form-icon uk-form-icon-flip"
                           href="javascript: _umart.$('input[name=dates]').trigger('click')" uk-icon="icon: calendar"></a>
                        <input class="uk-input" name="dates" type="text" autocomplete="off"/>
                    </div>
                </div>
                <div class="main-chart"></div>
            </div>
        </div>
        <div id="es-latest-orders" class="uk-margin">
            <div class="uk-card uk-card-small uk-card-body uk-card-default uk-overflow-auto">
                <h4 class="uk-heading-bullet"><?php echo Text::_('COM_UMART_LATEST_ORDERS'); ?></h4>
				<?php echo $renderer->render('order.summary', [
					'orders' => $this->latestOrders,
				]); ?>
            </div>
        </div>
		<?php if ($this->config->get('best_products', 1)
			|| $this->config->get('best_customers', 1)
		): ?>
            <div id="es-best" class="uk-grid-small uk-child-width-1-2@m" uk-grid>
                <div>
					<?php if ($this->config->get('best_products', 1)): ?>
						<?php echo $renderer->render('dashboard.best.product', [
							'products' => $this->get('BestProducts'),
							'style'    => $this->config->get('best_products_style', 'primary'),
						]); ?>
					<?php endif; ?>
                </div>
                <div>
					<?php if ($this->config->get('best_customers', 1)): ?>
						<?php echo $renderer->render('dashboard.best.customer', [
							'customers' => $this->get('BestCustomers'),
							'style'     => $this->config->get('best_customers_style', 'secondary'),
						]); ?>
					<?php endif; ?>
                </div>
            </div>

		<?php endif; ?>
    </form>

	<?php if (plg_sytem_umart_main('config', 'display_footer', 1)): ?>
        <p style="text-align: center">
            Powered by <a href="https://github.com/sallecta/umart/" target="_blank">JoomTech</a>
            | <i class="fab fa-joomla"></i>
            <a href="https://github.com/sallecta/umart" target="_blank">Rate on JED</a>
        </p>
	<?php endif; ?>

</div>
