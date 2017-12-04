<?php


class ReturnController extends FrontController{
	public $php_self = 'return';
	public $page_name = 'return';
	public $ssl = true;
	
	public function initContent()
	{

		$servername = "localhost";
		$username = "root";
		$password = "root";
		$dbname = "prestashop";

		// Create connection
		$conn = new mysqli($servername, $username, $password, $dbname);
		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}
		
		
		
		// upis u glavni order u adminu
		$cart = new Cart($_GET['ShoppingCartID']);
		$cartProducts = $cart->getProducts();
		
			
		do
			$reference = Order::generateReference();
		while (Order::getByReference($reference)->count());
		
		$this->currentOrderReference = $reference;
							
				$order=new Order();
				
			$order->total_products =$cart->getOrderTotal(false, Cart::ONLY_PRODUCTS);
			$order->id_carrier = (int)($cart->id_carrier);
			$order->id_customer = (int)($cart->id_customer);
			$order->id_address_invoice = (int)($cart->id_address_invoice);
			$order->id_address_delivery = (int)($cart->id_address_delivery);
			$order->id_currency = ($currency_special ? (int)($currency_special) : (int)($cart->id_currency));
			$order->id_lang = (int)($cart->id_lang);
			$order->id_cart = (int)($cart->id);
			$order->secure_key = $cart->secure_key;
			$order->conversion_rate = 1;
			$order->payment = 'WSPay';
			$amount_paid = !$dont_touch_amount ? Tools::ps_round((float)$amount_paid, 2) : $amount_paid;
			$order->module = 'wspay';
			$order->recyclable = $this->context->cart->recyclable;
			$order->gift = (int)($cart->gift);
			$order->gift_message = $cart->gift_message;
		//	$order->conversion_rate = $currency->conversion_rate;
			$order->total_products_wt = (float)($cart->getOrderTotal(true, Cart::ONLY_PRODUCTS));
			$order->total_discounts = (float)(abs($cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS)));
			$order->total_shipping = (float)($cart->getOrderShippingCost());
			$order->total_shipping_tax_excl = (float)($cart->getOrderShippingCost(null, false));
			$order->total_shipping_tax_incl = (float)($cart->getOrderShippingCost(null, true));
			$order->carrier_tax_rate = (float)Tax::getCarrierTaxRate($cart->id_carrier, (int)$cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
			$order->total_wrapping = (float)(abs($cart->getOrderTotal(true, Cart::ONLY_WRAPPING)));
			$order->total_paid = (float)($cart->getOrderTotal(true, Cart::ONLY_PRODUCTS)) + (float)($cart->getOrderShippingCost()) -  (float)(abs($cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS))) ;
			$order->id_shop_group=(int)($cart->id_shop_group);;
			$order->id_shop=(int)($cart->id_shop);
			$order->id_currency=1;
			$order->total_paid_tax_incl = (float)($cart->getOrderTotal(true, Cart::ONLY_PRODUCTS)) + (float)($cart->getOrderShippingCost()) -  (float)(abs($cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS))) ;
			$order->total_paid_tax_excl = (float)($cart->getOrderTotal(false, Cart::ONLY_PRODUCTS)) + (float)($cart->getOrderShippingCost(null, false)) -  (float)(abs($cart->getOrderTotal(false, Cart::ONLY_DISCOUNTS))) ;
			$order->total_paid_real = (float)($cart->getOrderTotal(true, Cart::ONLY_PRODUCTS)) + (float)($cart->getOrderShippingCost()) -  (float)(abs($cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS))) ;
			$order->current_state=2;
			$order->id_shop_group=1;
			$order->mobile_theme=0;
			$order->shipping_number=0;
			$order->total_discounts_tax_incl=(float)(abs($cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS)));
			$order->total_discounts_tax_excl=(float)(abs($cart->getOrderTotal(false, Cart::ONLY_DISCOUNTS)));;
			$order->total_products=(float)($cart->getOrderTotal(true, Cart::ONLY_PRODUCTS));
			$order->total_products_wt=(float)($cart->getOrderTotal(true, Cart::ONLY_PRODUCTS));
			$order->total_wrapping=0;
			$order->total_wrapping_tax_incl=0;
			$order->total_wrapping_tax_excl=0;
			$order->inovice_number=0;
			$order->invoice_date = '0000-00-00 00:00:00';
			$order->delivery_date = '0000-00-00 00:00:00';
			$order->date_add=$cart->date_add;
			$order->date_upd=$cart->date_add;
			$order->reference=($reference ? pSQL($reference) : pSQL($customer->reference));
			
			
			
				
		//insert u ordere		
		$sql="INSERT INTO `ps_orders`(`reference`, 
									  `id_shop_group`,
									  `id_shop`, 
									  `id_carrier`, 
									  `id_lang`, 
									  `id_customer`,
									  `id_cart`, 
									  `id_currency`,
								 	  `id_address_delivery`,
							  		  `id_address_invoice`, 
		  							  `current_state`,
		  					 		  `secure_key`,
		   			 				  `payment`, 
		    						  `conversion_rate`,
		    			 			  `module`, 
		     						  `recyclable`, 
		     						  `gift`, 
		     						  `gift_message`, 
		     						  `mobile_theme`, 
		     		 				  `shipping_number`, 
		     						  `total_discounts`, 
		     						  `total_discounts_tax_incl`, 
		     		 				  `total_discounts_tax_excl`, 
		     		 				  `total_paid`,
		      						  `total_paid_tax_incl`, 
		      						  `total_paid_tax_excl`, 
		      						  `total_paid_real`,
		      		 				  `total_products`,
		      				  		  `total_products_wt`, 
		        		 			  `total_shipping`, 
		       		  				  `total_shipping_tax_incl`,
		        	 				  `total_shipping_tax_excl`,
		        	  				  `carrier_tax_rate`, 
		       	   					  `total_wrapping`,
		            				  `total_wrapping_tax_incl`, 
		           					  `total_wrapping_tax_excl`, 
		           					  `invoice_number`, 
		          					  `delivery_number`, 
		           					  `invoice_date`, 
		           					  `delivery_date`,
		            				  `valid`, 
		             				  `date_add`, 
		            				  `date_upd`) 
							VALUES ('{$order->reference}',	
								'{$order->id_shop_group}',
								'{$order->id_shop}',
								'{$order->id_carrier}',
								'{$order->id_lang}',
								'{$order->id_customer}',
								'{$order->id_cart}',
								'{$order->id_currency}',
								'{$order->id_address_delivery}',
								'{$order->id_address_inovice}',
								'{$order->current_state}',
								'{$order->secure_key}',
								'{$order->payment}',
								'{$order->conversion_rate}',
								'{$order->module}',
								'{$order->recycable}',
								'{$order->gift}',
								'{$order->gift_message}',
								'{$order->mobile_theme}',
								'{$order->shipping_number}',
								'{$order->total_discounts}',
								'{$order->total_discounts_tax_incl}',
								'{$order->total_discounts_tax_excl}',
								'{$order->total_paid}',
								'{$order->total_paid_tax_incl}',
								'{$order->total_paid_tax_excl}',
								'{$order->total_paid_real}',
								'{$order->total_products}',
								'{$order->total_products_wt}',
								'{$order->total_shipping}',
								'{$order->total_shipping_tax_incl}',
								'{$order->total_shipping_tax_excl}',
								'{$order->carrier_tax_rate}',
								'{$order->total_wrapping}',
								'{$order->total_wrapping_tax_incl}',
								'{$order->total_wrapping_tax_excl}',
								'{$order->inovice_number}',
								'{$order->delivery_number}',
								'{$order->inovice_date}',
								'{$order->delivery_date}',
								'{$order->valid}',
								'{$order->date_add}',
								'{$order->date_upd}')";
		
		
	//	echo "<pre>";var_dump($cart);echo"</pre>";
		
		
		if (mysqli_query($conn, $sql)) {
			//echo "Radi!!!!";
		} else {
			//echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
		
		$id_order=mysqli_insert_id($conn);
		
		
		// za svaki product i order pravi se order sa detaljima
		foreach ($cart->getProducts() as $product){
			
			//echo "$product->id_product\n";
			//echo "<pre>";var_dump($product);echo"</pre>";
		 $order_detail = new OrderDetailCore();
		 $order_detail->id_order = $id_order;
		 $order_detail->id_order_inovice = 0;
		 $order_detail->id_warehouse = 0;
		 $order_detail->id_shop = $cart->id_shop;
		 $order_detail->product_id = $product['id_product'];
		 $order_detail->product_attribute_id = $product['id_product_attribute'];
		 $order_detail->product_name = $product['name'];
		 $order_detail->product_quantity = $product['quantity'];
		 $order_detail->product_quantity_in_stock = $product['stock_quantity'];
		 $order_detail->product_quantity_refunded = 0;
		 $order_detail->product_quantity_return = 0;
		 $order_detail->product_quantity_reinjected = 0;
		 $order_detail->product_price = $product['price'];
		 $order_detail->reduction_amount = 0.00;
		 $order_detail->reduction_percent = 0.00;
		 $order_detail->reduction_amount_tax_excl = 0.00;
		 $order_detail->reduction_amount_tax_incl = 0.00;
		 $order_detail->group_reduction = 0;
		 $order_detail->product_quantity_discount = 0;
		 $order_detail->product_ean13 = $product['ean13'];
		 $order_detail->product_upc = $product[upc] ;
		 $order_detail->product_reference = $product[reference];
		 $order_detail->product_supplier_reference = 0;
		 $order_detail->product_weight = $product[weight_attribute];
		 $order_detail->tax_computation_method = 0;
		 $order_detail->tax_name = 0;
		 $order_detail->tax_rate = 0;
		 $order_detail->ecotax = $product[ecotax];
		 $order_detail->ecotax_tax_rate = $product[ecotax_atrr];
		 $order_detail->discount_quantity_applied = 0;
		 $order_detail->download_hash = 0;
		 $order_detail->download_nb = 0;
		 $order_detail->download_deadline = '0000-00-00 00:00:00'; 
		 $order_detail->total_price_tax_incl = $product['total_wt'];
		 $order_detail->total_price_tax_excl =  $product['total'];
		 $order_detail->unit_price_tax_incl = $product['price_wt'];
		 $order_detail->unit_price_tax_excl = $product['price'];
		 $order_detail->total_shipping_price_tax_excl = null;
		 $order_detail->total_shipping_price_tax_incl = null;
		 $order_detail->purchase_supplier_price = 0;
		 $order_detail->original_product_price = $product['price']; 
		 
		
		// echo "<pre>";var_dump($order_detail);echo"</pre>";
		
		//insert u order detail 
		  $sql2="INSERT INTO `ps_order_detail`(`id_order`,
												  `id_order_invoice`,
		  										  `id_warehouse`,
		   										  `id_shop`,
		   										  `product_id`,
		    									  `product_attribute_id`,
		     									  `product_name`,
		      									  `product_quantity`,
		     								      `product_quantity_in_stock`,
		        								  `product_quantity_refunded`,
		 										  `product_quantity_return`, 
		 										  `product_quantity_reinjected`,
		 										  `product_price`, 
		 										  `reduction_percent`,
		  										  `reduction_amount`,
												  `reduction_amount_tax_incl`,
		  										  `reduction_amount_tax_excl`,
		  										  `group_reduction`,
		    									  `product_quantity_discount`,
		    									  `product_ean13`,
												  `product_upc`, 
												  `product_reference`,
		 										  `product_supplier_reference`,
		   										  `product_weight`,
		    									  `tax_computation_method`, 
		   										  `tax_name`,
												  `tax_rate`,
		  										  `ecotax`,
		 										  `ecotax_tax_rate`, 
		 										  `discount_quantity_applied`, 
		 										  `download_hash`, `download_nb`,
		 										  `download_deadline`,
												  `total_price_tax_incl`,
		  										  `total_price_tax_excl`,
		  										  `unit_price_tax_incl`, 
		   										  `unit_price_tax_excl`,
		   										  `total_shipping_price_tax_incl`,
												  `total_shipping_price_tax_excl`,
											      `purchase_supplier_price`,
		  										  `original_product_price`)
								 VALUES
												  ('{$id_order}',
												 '{$order_detail->id_order_inovice}',
												 '{$order_detail->id_warehouse}',
												 '{$order_detail->id_shop}',
												 '{$order_detail->product_id}',
												 '{$order_detail->product_attribute_id}',
												 '{$order_detail->product_name}',
												 '{$order_detail->product_quantity }',
												 '{$order_detail->product_quantity_in_stock}',
												 '{$order_detail->product_quantity_refunded}',
												 '{$order_detail->product_quantity_return}',
												 '{$order_detail->product_quantity_reinjected}',
												 '{$order_detail->product_price}',
												 '{$order_detail->reduction_percent}',
												 '{$order_detail->reduction_amount}',
												 '{$order_detail->reduction_amount_tax_incl}',
												 '{$order_detail->reduction_amount_tax_excl}',
												 '{$order_detail->group_reduction}',
												 '{$order_detail->product_quantity_discount}',
												 '{$order_detail->product_ean13}',
												 '{$order_detail->product_upc}',
												 '{$order_detail->product_reference }',
												 '{$order_detail->product_supplier_reference}',
												 '{$order_detail->product_weight}',										
												 '{$order_detail->tax_computation_method }',
												 '{$order_detail->tax_name}',
												 '{$order_detail->tax_rate }',
												 '{$order_detail->ecotax}',
												 '{$order_detail->ecotax_tax_rate}',
												 '{$order_detail->discount_quantity_applied}',
												 '{$order_detail->download_hash}',
												 '{$order_detail->download_nb}',
												 '{$order_detail->download_deadline}',
												 '{$order_detail->total_price_tax_incl}',
												 '{$order_detail->total_price_tax_excl}',
												 '{$order_detail->unit_price_tax_incl}',
												 '{$order_detail->unit_price_tax_excl}',
												 '{$order_detail->total_shipping_price_tax_incl}',
												 '{$order_detail->total_shipping_price_tax_excl}',
												 '{$order_detail->purchase_supplier_price}',
												 '{$order_detail->original_product_price}')"; 
		
		if (mysqli_query($conn, $sql2)) {
			//echo "Radi!!!!";
		} else {
			//echo "Error: " . $sql2 . "<br>" . mysqli_error($conn);
		}
		
			}
		
		
		 
		
		 
		//insert u history koji se prikazuje kod kupca u history-u 
		$sql3 = "INSERT INTO `ps_order_history`(`id_employee`, `id_order`, `id_order_state`, `date_add`) 
				VALUES ('0','{$id_order}','2','{$order->date_add}')"; 
		
		if (mysqli_query($conn, $sql3)) {
			//echo "Radi!!!!";
		} else {
			//echo "Error: " . $sql3 . "<br>" . mysqli_error($conn);
		}
		
		// insert da se prikaze order carrier u tablici sa shippingom
			$sql4 = "INSERT INTO `ps_order_carrier`(
		`id_order`,
		`id_carrier`,
		`id_order_invoice`,
		`weight`,
		`shipping_cost_tax_excl`,
		`shipping_cost_tax_incl`,
		`tracking_number`,
		`date_add`)
		VALUES ('{$id_order}', 3, 0, 0, '{$order->total_shipping_tax_excl}', '{$order->total_shipping_tax_incl}', 0, '{$order->date_add}')";
		
		if (mysqli_query($conn, $sql4)) {
		//echo "Radi!!!!";
		} else {
			echo "Error: " . $sql4 . "<br>" . mysqli_error($conn);
		}
		
		

//$conn->close();


parent::initContent();

//dodavanje na stranicu
$this->context->smarty->assign(array('HOOK_HOME' => Hook::exec('displayHome'),
		'HOOK_HOME_TAB' => Hook::exec('displayHomeTab'),
		'HOOK_HOME_TAB_CONTENT' => Hook::exec('displayHomeTabContent')
));



//postavljanje template-a za url
$this->setTemplate(_PS_THEME_DIR_.'return.tpl');


}
	
}
	
	?>
	
	
	


