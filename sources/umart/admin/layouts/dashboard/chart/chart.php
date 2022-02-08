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

extract($displayData);
$currency = [
	'format'    => $currencyClass->get('format'),
	'symbol'    => $currencyClass->get('symbol'),
	'decimals'  => $currencyClass->get('decimals'),
	'separator' => $currencyClass->get('separator'),
	'point'     => $currencyClass->get('point'),
	'code'      => $currencyClass->get('code'),
];
?>
<canvas id="es-sales-data" height="95"></canvas>
<div id="es-total-sales" class="uk-grid uk-grid-small uk-child-width-1-3@s uk-text-center" uk-grid>
    <div class="col">
        <div class="es-paid-amount"></div>
        <h4><?php echo $currencyClass->toFormat($paidAmount); ?></h4>
    </div>
    <div class="col">
        <div class="es-unpaid-amount"></div>
        <h4><?php echo $currencyClass->toFormat($unpaidAmount); ?></h4>
    </div>
    <div class="col">
        <div class="es-refund-amount"></div>
        <h4><?php echo $currencyClass->toFormat($refundAmount); ?></h4>
    </div>
</div>

<script>
    var currency = <?php echo json_encode($currency); ?>;
    var salesData = {
        labels: <?php echo $labels; ?>,
        datasets: [
            {
                label: '<?php echo Text::_('COM_EASYSHOP_PAYMENT_PAID'); ?>',
                backgroundColor: 'rgba(102, 255, 178, 0.2)',
                borderColor: 'rgba(88, 188, 116, 1)',
                pointBorderColor: 'rgba(88, 188, 116, 1)',
                pointBackgroundColor: '#fff',
                pointHighlightStroke: 'rgba(225,225,225,0.9)',
                data: <?php echo json_encode($paidAmountJS); ?>
            },
            {
                label: '<?php echo Text::_('COM_EASYSHOP_PAYMENT_UNPAID'); ?>',
                backgroundColor: 'rgba(195, 40, 96, 0.1)',
                borderColor: 'rgba(195, 40, 96, 1)',
                pointBorderColor: 'rgba(195, 40, 96, 1)',
                pointBackgroundColor: '#fff',
                pointHighlightStroke: 'rgba(225,225,225,0.9)',
                data: <?php echo json_encode($unpaidAmountJS); ?>
            },
            {
                label: '<?php echo Text::_('COM_EASYSHOP_PAYMENT_REFUND'); ?>',
                backgroundColor: 'rgba(255, 172, 100, 0.2)',
                borderColor: 'rgba(255, 172, 100, 1)',
                pointBorderColor: 'rgba(255, 172, 100, 1)',
                pointBackgroundColor: '#fff',
                pointHighlightStroke: 'rgba(225,225,225,0.9)',
                data: <?php echo json_encode($refundAmountJS); ?>
            }
        ]
    };

    if (_es.getData('chart', false) === false) {
        _es.setData('chart', new Chart(document.getElementById('es-sales-data').getContext('2d')));
    }

    Chart.Line(document.getElementById('es-sales-data').getContext('2d'), {
        data: salesData,
        options: {
            pointDotRadius: 6,
            pointDotStrokeWidth: 2,
            datasetStrokeWidth: 3,
            scaleShowVerticalLines: false,
            scaleGridLineWidth: 2,
            scaleShowGridLines: true,
            scaleGridLineColor: 'rgba(225, 255, 255, 0.02)',
            scaleOverride: true,
            scaleSteps: 9,
            scaleStepWidth: 500,
            scaleStartValue: 0,
            responsive: true,
            scales: {
                xAxes: [
                    {
                        gridLines: {
                            offsetGridLines: true,
                        },
                    }
                ],
                yAxes: [
                    {
                        ticks: {
                            callback: function (value, index, values) {
                                return _es.currencyFormat(value, currency);
                            }
                        }
                    }
                ]
            },
            tooltips: {
                callbacks: {
                    label: function (tooltipItem, data) {
                        return _es.currencyFormat(tooltipItem.yLabel, currency);
                    }
                }
            }
        }
    })
    ;

    var
        paidProgressBar = new ProgressBar.Circle('#es-total-sales .es-paid-amount', {
            color: '#2bab51',
            strokeWidth: 3,
            trailWidth: 3,
            duration: 1500,
            text: {
                value: '0%'
            },
            step: function (state, bar) {
                bar.setText((bar.value() * 100).toFixed(2) + '%');
            }
        }).animate(<?php echo round($paidRate, 4); ?>),
        unpaidProgressBar = new ProgressBar.Circle('#es-total-sales .es-unpaid-amount', {
            color: '#e81760',
            strokeWidth: 3,
            trailWidth: 3,
            duration: 1500,
            text: {
                value: '0%'
            },
            step: function (state, bar) {
                bar.setText((bar.value() * 100).toFixed(2) + '%');
            }
        }).animate(<?php echo round($unpaidRate, 4); ?>),

        refundProgressBar = new ProgressBar.Circle('#es-total-sales .es-refund-amount', {
            color: '#e88e3c',
            strokeWidth: 3,
            trailWidth: 3,
            duration: 1500,
            text: {
                value: '0%'
            },
            step: function (state, bar) {
                bar.setText((bar.value() * 100).toFixed(2) + '%');
            }
        }).animate(<?php echo round($refundRate, 4); ?>);
</script>
