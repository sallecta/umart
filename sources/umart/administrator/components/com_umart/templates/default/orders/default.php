<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\Currency;
use Umart\Classes\Order;
use Umart\Classes\Utility;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

/**
 * @var Currency $currency
 * @var Order    $order
 * @var Utility  $utility
 */

echo $this->getFormLayout('head');
$vars          = $this->get('layout.storage');
$user          = $vars['user'];
$userId        = (int) $user->get()->id;
$this->columns = 7;
$currency      = plg_sytem_umart_main(Currency::class);
$order         = plg_sytem_umart_main(Order::class);
$utility       = plg_sytem_umart_main(Utility::class);
$model         = plg_sytem_umart_main('model', 'order');
$paymentStatus = $order->getPaymentStatus();
$orderStatus   = $order->getOrderStatus();
$iconMaps      = [
	'0'  => 'fa fa-plus',
	'1'  => 'fa fa-check',
	'2'  => 'fa fa-tasks',
	'3'  => 'fa fa-truck',
	'4'  => 'fa fa-check-circle',
	'5'  => 'fa fa-ban',
	'6'  => 'fa fa-history',
	'-2' => 'fa fa-trash',
];

HTMLHelper::_('umart.printOrder', '', 'a.es-print-order');
plg_sytem_umart_main('doc')->addScriptDeclaration('   
	Joomla.submitbutton = function(task){		
		if(task == "order.add"){				
			_umart.umartui.modal("#es-modal-new").show();
		}else{			
			if(task == "order.createNew"){				
				if(_umart.$("#es-modal-new").find("input,select,textarea").es_validate()){
					Joomla.submitform(task);
				}				
			}else{
				Joomla.submitform(task);
			}		
		}
	};	
	_umart.$(document).ready(function($){		
		$("#jform_user_id").on("change", function(){
		    var userId = parseInt($(this).val());
		    var ajaxUrl = "' . Uri::base(true) . '/index.php?option=com_umart&task=order.loadUserFieldData";
		    _umart.ajax(ajaxUrl, {userId: userId, "' . Session::getFormToken() . '": 1}, function (response) {
		        $("#es-modal-new .es-billing-form").html(response.data.billing);
		        $("#es-modal-new .es-shipping-form").html(response.data.shipping);
		        _umart.initChosen("#es-modal-new");
		    });		    
		});
	});
');

$coreCreate    = $user->core('create');
$coreEdit      = $user->core('edit');
$coreEditState = $user->core('edit.state');
$coreEditOwn   = $user->core('edit.own');
$coreCheckIn   = $user->core('manage');
?>
<thead>
<tr>
    <th colspan="7">
		<?php foreach ($orderStatus as $value => $text): ?>
            <div class="es-order-icon-wrap">
                <div class="es-order-icon es-order-icon-<?php echo $value; ?>">
                    <i class="<?php echo $iconMaps[$value]; ?>"></i>
                </div>
                <div>
					<?php echo $text; ?>
                </div>
            </div>
		<?php endforeach; ?>
    </th>
</tr>
<tr>
    <th class="uk-table-shrink uk-text-nowrap uk-text-center uk-visible@m">
		<?php echo HTMLHelper::_('umart.gridCheckall'); ?>
    </th>
    <th class="uk-table-shrink uk-text-nowrap uk-text-center">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_UMART_CODE', 'a.order_code', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-table-shrink uk-text-nowrap uk-text-center">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_UMART_ORDER_STATUS', 'a.state', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-table-shrink uk-text-nowrap uk-text-center">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_UMART_TOTAL_PRICE', 'a.total_price', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_UMART_CREATED_DATE', 'a.created_date', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
    <th class="uk-text-nowrap">
		<?php echo Text::_('COM_UMART_CUSTOMER'); ?>
    </th>
    <th width="1%" class="uk-table-shrink uk-text-nowrap uk-text-center uk-visible@m">
		<?php echo HTMLHelper::_('searchtools.sort', 'COM_UMART_ID', 'a.id', $vars['listDirn'], $vars['listOrder']); ?>
    </th>
</tr>
</thead>
<tbody>
<?php foreach ($this->items as $i => $item):
	$canCheckin = $coreCheckIn || $item->checked_out == $userId || $item->checked_out == 0;
	$canEditOwn = $coreEditOwn && $item->created_by == $userId;
	$canChange = $coreEditState && $canCheckin;
	$address = $order->getAddress($item->id);
	?>
    <tr>
        <td class="uk-text-nowrap uk-text-center uk-visible@m">
			<?php echo HTMLHelper::_('umart.gridId', $i, $item->id); ?>
        </td>
        <td class="uk-text-nowrap uk-text-center">
			<?php if ($coreEdit || $coreEditOwn) : ?>
                <a class="es-order-code" href="<?php echo $this->getItemLink($item->id); ?>">
					<?php echo $this->escape($item->order_code); ?>
                </a>
			<?php else : ?>
                <div class="es-order-code">
					<?php echo $this->escape($item->order_code); ?>
                </div>
			<?php endif; ?>
        </td>
        <td class="uk-text-center uk-text-nowrap">
			<?php if ($item->checked_out) : ?>
				<?php echo HTMLHelper::_('umart.gridCheckedOut', $i, $item->editor, $item->checked_out_time, $vars['prefix'], $canCheckin); ?>
			<?php endif; ?>
            <div class="es-order-icon es-order-icon-<?php echo $item->state; ?>"
                 title="<?php echo htmlspecialchars($orderStatus[$item->state], ENT_COMPAT, 'UTF-8'); ?>" uk-tooltip>
                <i class="<?php echo $iconMaps[$item->state]; ?>"></i>
            </div>
            <div class="es-order-icon es-payment-icon-<?php echo $item->payment_status; ?>"
                 title="<?php echo htmlspecialchars($paymentStatus[$item->payment_status], ENT_COMPAT, 'UTF-8'); ?>"
                 uk-tooltip>
				<?php if ($item->payment_status == 1): ?>
                    <i class="fa fa-check"></i>
				<?php elseif ($item->payment_status == 2): ?>
                    <i class="fa fa-undo"></i>
				<?php else: ?>
                    <i class="fa fa-times"></i>
				<?php endif; ?>
            </div>
            <a href="#" class="es-order-icon es-print-order"
               title="<?php echo htmlspecialchars(Text::_('COM_UMART_PRINT_ORDER'), ENT_COMPAT, 'UTF-8'); ?>"
               data-order-id="<?php echo $item->id; ?>"
               data-order-code="<?php echo $item->order_code; ?>"
               data-order-email="<?php echo $item->user_email; ?>"
               data-page-title="<?php echo htmlspecialchars(Text::sprintf('COM_UMART_ORDER_PRINT_TITLE_FORMAT', $item->order_code), ENT_COMPAT, 'UTF-8'); ?>"
               uk-tooltip>
                <i class="fa fa-print"></i>
            </a>
        </td>
        <td class="uk-text-center uk-text-bold uk-text-nowrap">
			<?php
			if ($item->currency_id)
			{
				echo $currency->load($item->currency_id)->toFormat($item->total_price);
			}
			else
			{
				echo $currency->getDefault()->toFormat($item->total_price);
			}
			?>
        </td>
        <td class="uk-visible@m">
            <span uk-icon="icon: calendar"></span>
			<?php echo $utility->displayDate($item->created_date); ?>
        </td>
        <td class="uk-text-nowrap">
			<?php
			$title = '<strong>' . Text::_('COM_UMART_BILLING') . ': </strong>' . $utility->formatAddress($address['billing'])
				. '<br/><strong>' . Text::_('COM_UMART_SHIPPING') . ': </strong>' . $utility->formatAddress($address['shipping']);
			?>

            <div class="uk-display-inline-block" uk-tooltip
                 title="<?php echo htmlspecialchars($title, ENT_COMPAT, 'UTF-8'); ?>">
                <span uk-icon="icon: location"></span>
				<?php echo $item->customerName . '. ' . $item->user_email; ?>
            </div>
        </td>
        <td class="uk-text-nowrap uk-text-center uk-visible@m">
			<?php echo $item->id; ?>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
<?php

echo $this->getFormLayout('foot');

if ($user->core('create'))
{
	echo $this->getRenderer()->render('order.new', [
		'currency' => $currency,
		'form'     => $model->getForm([], false),
	]);
}
?>

<div id="es-modal-invoice" class="uk-modal-container" uk-modal>
    <div class="uk-modal-dialog">
        <a class="uk-modal-close-default" uk-close></a>
        <iframe width="100%" class="uk-height-viewport"></iframe>
    </div>
</div>
