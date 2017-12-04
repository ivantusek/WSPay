<?php

class WspayValidationModuleFrontController extends ModuleFrontController
{
	public function postProcess()
	{
		$shopid	= Configuration::get('WSPAY_ID');
		$secretkey = Configuration::get('WSPAY_KEY');
		$credit_card_processed	= $_REQUEST['credit_card_processed'];
		$order_number = $_REQUEST['order_number'];
		$cart_id = $_REQUEST['merchant_order_id'];

		$cart=new Cart($cart_id);
		$wspay = new wspay();
		
		
			$amount = number_format($cart->getOrderTotal(true, 3), 2, '.', '');
			$currency_from = Currency::getCurrency($cart->id_currency);
			$currency_to = Currency::getCurrency(Configuration::get('WSPAY_CURRENCY'));
			$amount = Tools::ps_round($amount / $currency_from['conversion_rate'], 2);
			$total = number_format(Tools::ps_round($amount *= $currency_to['conversion_rate'], 2), 2, '.', '');
		
			$total = number_format($cart->getOrderTotal(true, 3), 2, '.', '');
	

		
		$compare_string = $secretkey . $id . $order_number;
		$compare_hash1 = strtoupper(md5($compare_string));
		$compare_hash2 = $_REQUEST['key']; 

		if ($compare_hash1 == $compare_hash2) {
			$customer = new Customer($cart->id_customer);
			$total = (float)$cart->getOrderTotal(true, Cart::BOTH);
			$wspay->validateOrder($cart_id, _PS_OS_PAYMENT___, $total, $wspay->displayName,  '', array(), NULL, false, $customer->secure_key);
			$order = new Order($wspay->currentOrder);
			Tools::redirect('index.php?controller=return&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
			
		} else {
			echo 'Total: '.$total.'</br>';
			echo 'Total: '.$_REQUEST['total'];
		}
	}
}



?>