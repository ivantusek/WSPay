<?php
// postavljanje modula
if(!defined('_PS_VERSION_'))
	exit;

class wspay extends PaymentModule
{
	//metode
	private $_html = '';
	private $_postErrors = array();
	
	// konstruktor funkcija
	public function __construct()				
	{
		// funkicije koje su postavljene u config-u
		$this->name = 'wspay';
		$this->tab = 'payments_gateways';
		$this->version = '1.0.0';
		$this->author = '';
		$this->need_instance = 1;
		$this->controllers = array('payment', 'validation');
		$config = Configuration::getMultiple(array('WSPAY_TITLE','WSPAY_DESCRIPTION','WSPAY_ID','WSPAY_KEY'));
		
		if(isset($config['WSPAY_TITLE']))
			$this->TITLE = $config['WSPAY_TITLE'];
		if(isset($config['WSPAY_DESCRIPTION']))
			$this->DESCRIPTION = $config['WSPAY_DESCRIPTION'];
		if(isset($config['WSPAY_ID']))
			$this->ID = $config['WSPAY_ID'];
		if(isset($config['WSPAY_KEY']))
			$this->KEY = $config['WSPAY_KEY'];
		
		
	
		// parent construktor
		parent::__construct();
		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('WSPay™');
		$this->description = $this->l('WSPay™ credit card payment');
		$this->confirmUninstall = $this->l('Are you sure about removing these details?');
		$this->_checkContent();
		$this->context->smarty->assign('module_name', $this->name);
		if(!isset($this->ID) OR !isset($this->KEY))
			$this->warning=$this->l('ShopID and Secret key must be configured before using this module.'); 

		
		
		
	}
		// funkcija za instalaciju kada se nalazi u adminu
		public function install()
		{
			if(!parent::install() ||
				
				 !$this->registerHook('payment') ||
				 !$this->registerHook('paymentReturn')||
				 !$this->_createContent())
				
				
				
				
					return false;
				return true;
				 
			
				 
				 $currencies = Currency::getCurrencies();
				 $authorized_currencies = array();
				 foreach ($currencies as $currency)
				 $authorized_currencies[] = $currency['id_currency']; 
			
				 
		}
		
		//funkcija za deinstalaciju
		public function uninstall()
		{
			if(!parent::uninstall() ||
			!Configuration::deleteByName('WSPAY_TITLE') ||
			!Configuration::deleteByName('WSPAY_DESCRIPTION') ||
			!Configuration::deleteByName('WSPAY_ID') ||
			!Configuration::deleteByName('WSPAY_KEY') || 
			!$this->_deleteContent())		
				return false;
			return true;
			
								
		}
		
		
		//kontent funkcija
		public function getContent()
		{
			if(!empty($_POST))
			{
				$this->_postValidation();
				if(!sizeof($this->_postErrors))
					$this->_postProcess();
			
			else
			
				foreach ($this->_postErrors as $err)
				$this->_html.="<div class = 'alert error'>($err)</div>";
			}
			else
			{
				$this->_html .="</br>";
			}
			
			$message = '';
			
			if (Tools::isSubmit('submit_' .$this->name))
			{
				$message = $this->_saveContent();
				$this->_displayContent($message);
				
			}

			$this->_displayForm();
			return $this->_html;
		}
		
		//kada je add cart
		public function checkTotal($cart)
		{
			global $cookie, $smarty;
			$check_total = 0;
			$cart_details = $cart->getSummaryDetails(null, true);
			$products = $cart->getProducts();
			foreach ($products as $product)
			{
				$check_total += $product['price']*$product['quantity'];		
			}
			
			if (_PS_VERSION_ < '1.6')
				$shipping_cost = $cart_details['total_shipping_tax_exc'];
			else 
				$shipping = $this->context->cart->getTotalShippingCost();
			$check_total += $shipping;
			$check_total += $cart_details['total_tax'];
			$check_total -= $cart_details['total_discounts_tax_exc'];
			return $check_total;
				
		}
		
		

		// prikaz u izboru plaćanja
		 public function hookPayment($params)
		{	
			if(!$this->active)
				return;
				
			global $smarty;
			$smarty->assign(array(
					'this_path' => $this->_path,
					'this_path_ssl' => Configuration::get('PS_FO_PROTOCOL').$_SERVER['HTTP_HOST'].__PS_BASE_URI__."modules/{$this->name}/"));
				
			return $this->display(__FILE__, 'payment.tpl'); //poziva se tamplate kod checkouta
		}
		

		
		//kada se sve izvrši
		public function hookPaymentReturn($params)
		{
			if(!$this->active)
				return;
			
			global $smarty;
			$state= $params['objOrder']->getCurrentState();
				if($state == PS_OS_OUTOFSTOCK_ or $state == PS_OS_PAYMENT_)
					$smarty->assign(array(
						'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
						'status' => 'ok',
						'id_order'	=>	$params['objOrder']->id	
					));
				else
					$smarty->assign('status','failed');	
			//prikaz na frontu
			return $this->display(__FILE__, 'payment_return.tpl'); //poziva se kada je kupnja izvrsena
		}
		
		//postavljanje za formu kod redirect-a
		public function execPayment($cart)
		{
			
		$delivery = new Address(intval($cart->id_address_delivery));
        $invoice = new Address(intval($cart->id_address_invoice));
        $customer = new Customer(intval($cart->id_customer));

        global $cookie, $smarty;

        //postavljanje valute u formi za redirect
        $cart_details = $cart->getSummaryDetails(null, true);
        $currencies = Currency::getCurrencies();
        $authorized_currencies = array_flip(explode(',', $this->currencies));
        $currencies_used = array();
        foreach ($currencies as $key => $currency)
            if (isset($authorized_currencies[$currency['id_currency']]))
                $currencies_used[] = $currencies[$key];

        $smarty->assign('currencies_used',$currencies_used);

        $order_currency = '';

        foreach ($currencies_used as $key => $currency) {
            if ($currency['id_currency'] == $cart->id_currency) {
                $order_currency = $currency['iso_code'];
            }
        }

        $products = $cart->getProducts();
        foreach ($products as $key => $product)
        {
                $products[$key]['name'] = str_replace('"', '\'', $product['name']);
                $products[$key]['name'] = htmlentities(utf8_decode($product['name']));
        }
        //
        $discounts = $cart_details['discounts'];

        $carrier = $cart_details['carrier'];

    
        
        $shopid				= Configuration::get('WSPAY_ID');
        $secretkey          = Configuration::get('WSPAY_KEY');
        $amount				= number_format($cart->getOrderTotal(true, 3), 2, '.', '');
        $cart_order_id		= $cart->id;
        $email				= $customer->email;
        $secure_key			= $customer->secure_key;
      

        //inovice parametri iz prestashopa
        $card_holder_name		= $invoice->firstname . ' ' . $invoice->lastname;
        $street_address			= $invoice->address1;      
        $phone		    		= $invoice->phone;
        $city 	    			= $invoice->city;
        $state		    		= (Validate::isLoadedObject($invoice) AND $invoice->id_state) ? new State(intval($invoice->id_state)) : false;
        $zip			    	= $invoice->postcode;
        $country		    	= $invoice->country;

        // shipping parametri
        $ship_name	    		= $delivery->firstname;
        $ship_lastname			= $delivery->lastname;
        $ship_street_address	= $delivery->address1;   
        $ship_city 		    	= $delivery->city;
        $ship_state	    		= (Validate::isLoadedObject($delivery) AND $delivery->id_state) ? new State(intval($delivery->id_state)) : false;
        $ship_zip   			= $delivery->postcode;
        $ship_country			= $delivery->country;

        $check_total = $this->checkTotal($cart);
        
        $currency_from = Currency::getCurrency($cart->id_currency);
        $amount = Tools::ps_round($amount / $currency_from['conversion_rate'], 2);
        $total = Tools::ps_round($amount *= $currency_to['conversion_rate'], 2);
        $order_currency = $currency_to['iso_code'];
        $override_currency = $currency_to;
        
        $total = number_format($cart->getOrderTotal(true, 3), 2, '.', '');
        $override_currency = 0;
        
        //generiranje md5 potpisa ($shopid, $secretkey, $amountWithoutDot)
        $amountWithoutDot = str_replace('.' ,'', $total);
        $signature = md5($shopid.$secretkey.$cart_order_id.$secretkey.$amountWithoutDot.$secretkey);
        
       
        
		//postavljanje u formi za redirect
        $smarty->assign(array(
           
            'check_total' 		=> str_replace('.', ',', $total),
            'shopid' 	    	=> $shopid,
            'key'               => $secretkey,
        	'signature'			=> $signature,
           	'total'			    => $total,
            'cart_order_id'		=> $cart_order_id,
            'email'	    		=> $email,
            'secure_key'		=> $secure_key,
            'card_holder_name'	=> $card_holder_name,
            'street_address'	=> $street_address,     
            'phone'			    => $phone,
            'city' 			    => $city,
            'state' 			=> $state,
            'zip'	    		=> $zip,
            'country'			=> $country,
            'ship_name'			=> $ship_name,
        	'ship_lastname'		=> $ship_lastname,
            'ship_street_address'	=> $ship_street_address,
            'ship_city' 		=> $ship_city,
            'ship_state' 		=> $ship_state,
            'ship_zip'			=> $ship_zip,
            'ship_country'		=> $ship_country,
            'products' 			=> $products,
            'currency_code'     => $order_currency,
            'override_currency' => $override_currency,
            'this_path' 		=> $this->_path,
            'this_path_ssl' 	=> Configuration::get('PS_FO_PROTOCOL').$_SERVER['HTTP_HOST'].__PS_BASE_URI__."modules/{$this->name}/"));

    
		//postavljanje na ispis kada se pojavi za cart
        $cart=new Cart($cookie->id_cart);
        $address=new Address($cart->id_address_delivery,intval($cookie->id_lang));
        $state=State::getNameById($address->id_state);
        $state=($state?'('.$state.')':'');
        $str_address=($address->company?$address->company.'<br>':'').
        $address->firstname.' '.$address->lastname.'<br>'.
        $address->address1.'<br>'.
        $address->postcode.' '.$address->city.'<br>'.
        $address->country.$state;
        $smarty->assign('address',$str_address);
        $carrier=Carrier::getCarriers(intval($cookie->id_lang));

        if($carrier){
            foreach ($carrier as $c){
                if($cart->id_carrier==$c['id_carrier']){
                    $smarty->assign('carrier',$c['name']);
                    break;
                }
            }
        }
        return $this->display(__FILE__, 'payment_wspay.tpl'); //prije redirect-a poziva se template sa podacima
    }
			
		
		//Validacija za shop id
		public function _postValidation()
		{
			if (isset($_POST['submit']))
			{
				if(empty($_POST['ID']))
					$this->_postErrors[] = $this->l('Your shop ID number is required.');	
			}
			elseif(empty($_POST['KEY']))	
			{
					
					$this->_postErrors = $this->l('Your Security key is required');
			} 
				
		}
		
		
		
		
	
		//post forma u adminu kod konfiguracije
		public function _postProcess()
		{
			if(isset($_POST['submit']))
			{
				Configuration::updateValue('WSPAY_TITLE', $_POST['TITLE']);
				Configuration::updateValue('WSPAY_DESCRIPTION', $_POST['DESCRIPTION']);
				Configuration::updateValue('WSPAY_ID', $_POST['ID']);
				Configuration::updateValue('WSPAY_KEY', $_POST['KEY']);	
				
			}
			
				$ok = $this->l('Ok');
		        $updated = $this->l('Settings Updated');
		        $this->_html .= "<div class='conf confirm'><img src='../img/admin/ok.gif' alt='{$ok}' />{$updated}</div>";
			
			}

		//konfiguracija modula sa porukom
		public function _saveContent()
		{
			
			$message = '';
			if (Configuration::updateValue('WSPAY_TITLE', Tools::getValue('WSPAY_TITLE')) &&
				Configuration::updateValue('WSPAY_DESCRIPTION', Tools::getValue('WSPAY_DESCRIPTION')) &&
				Configuration::updateValue('WSPAY_ID', Tools::getValue('WSPAY_ID')) &&
				Configuration::updateValue('WSPAY_KEY', Tools::getValue('WSPAY_KEY')))
					
						 
				$message = $this->displayConfirmation($this->l('Settings saved'));		
			else 
				
				return $message;
		}
		//kada se instalira modul ispsuje se poruka za konfiguraciju
		public function _checkContent()
		{
			
			if(!Configuration::get('WSPAY_TITLE') &&
			  !Configuration::get('WSPAY_DESCRIPTION') &&
			  !Configuration::get('WSPAY_ID') &&
			  !Configuration::get('WSPAY_KEY'))
			 
			$this->warning = $this->l('Need to configure module');	 
		}
		
		//prikaz poruke 
		public function _displayContent()
		{
			$this->context->smarty->assign(array(
				'message' => $message,
				'WSPAY_TITLE'	=> Configuration::get('WSPAY_TITLE'),
				'WSPAY_DESCRIPTION' => Configuration::get('WSPAY_DESCRIPTION'),
				'WSPAY_ID' => Configuration::get('WSPAY_ID'),
				'WSPAY_KEY' => Configuration::get('WSPAY_KEY')
							
					
				));
		}
		//kod instalacije se pravi content
		public function _createContent()
		{
			if (!Configuration::updateValue('WSPAY_TITLE', '') ||
				!Configuration::updateValue('WSPAY_DESCRIPTION', '') ||
				!Configuration::updateValue('WSPAY_ID', '') ||
				!Configuration::updateValue('WSPAY_KEY'))
								
				return false;
			return true;
			
		}
		
		
		//kada se obrise onda se brise iz modula
		public function _deleteContent()
		{
			if (!Configuration::deleteByName('WSPAY_TITLE', '') ||
				!Configuration::deleteByName('WSPAY_DESCRIPTION', '') ||
				!Configuration::deleteByName('WSPAY_ID') ||
				!Configuration::deleteByName('WSPAY_KEY'))
				
				return false;
			return true;	
		}
		
		
		//funkcija prikaz forme u adminu
		public function _displayForm()
		{
			//postavljanje valuea u admin formi i sprema se	
			$wspaytitle = $this->TITLE;
			$wspaydescription = $this->DESCRIPTION;
			$shopid = $this->ID;
			$secretkey = $this->KEY;
			
			$wspayup = $this->l('Save settings');
				
			//prikaz
			$this->_html .=
			"
			{$message}
	<fieldset>
			<legend>WSPay™</legend>
			<h2> WSPay™ Payment Gateway </h2>
			<img src = 'http://www.shop.hr/wp-content/uploads/2013/09/wspay.png'>
			<p> WSPay™ sustav za internet autorizaciju i naplatu kreditnih kartica. </p> 
			<p>	WSPay™ je sustav koji vlasnicima web trgovina omogućuje autorizaciju i naplatu usluga u realnom vremenu. </p>
	</fieldset>
	<fieldset>
			<legend> Settings </legend>
		<form action='{$_SERVER['REQUEST_URI']}' method='POST'>
			<label> Title </label>
				<input  type='text' name='TITLE' value='{$wspaytitle}'/><br>
				<br>
			<label> Description: </label>
				<input  type='text' name='DESCRIPTION' value='{$wspaydescription}' style='width:380px; height:40px'  /><br>
				<br>
			<label> ShopID: </label>
				<input  type='text' name='ID' value='{$shopid}'/><br>
				<br>
			<label> Secret key: </label>
				<input  type='text' name='KEY' value='{$secretkey}'/><br>
				<br>		
			<p>
			<label>&nbsp;</label>
				<input  name='submit' type='submit' value='{$wspayup}' class='button'/>	
		</form>
	</fieldset>";
				
		}
}	

	
?>
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	



	
	
