<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

echo $this->getRenderer()->render('customer.order', [
	'orderForm'          => $this->orderForm,
	'order'              => $this->order,
	'payment'            => $this->payment,
	'paymentOptionsList' => $this->paymentOptionsList,
]);
