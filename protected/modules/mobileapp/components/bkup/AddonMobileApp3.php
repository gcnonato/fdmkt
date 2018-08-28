<?php
class AddonMobileApp
{
	public static function prettyCuisineList($cuisine_json='')
	{		
		if (!empty($cuisine_json)){
			$cuisine_json=!empty($cuisine_json)?json_decode($cuisine_json):false;
			if($cuisine_json!=False){
				$cuisine_list=Yii::app()->functions->Cuisine(true);					
				$cuisine='';
				foreach ($cuisine_json as $cuisine_id) {					
					if(array_key_exists($cuisine_id,(array)$cuisine_list)){
						$cuisine.=$cuisine_list[$cuisine_id].", ";
					}
				}
				return substr($cuisine,0,-2);
			}
		}
		return false;
	}
				
	public static function q($data)
	{
		return Yii::app()->db->quoteValue($data);
	}
	
    public static function t($message='')
	{
		return Yii::t("default",$message);
	}	
	
	public static function isMerchantOpen($merchant_id='')
	{
		$open = Yii::app()->functions->isMerchantOpen($merchant_id); 			 			
	    $preorder= Yii::app()->functions->getOption("merchant_preorder",$merchant_id);
			
		$now=date('Y-m-d');				 			
	    if ( $m_holiday=Yii::app()->functions->getMerchantHoliday($merchant_id)){  
      	   if (in_array($now,(array)$m_holiday)){
      	   	  $open=false;
      	   }
        }
        
        if (!$open){
        	if($preorder){
        		$open=true;
        	}
        }
               
        return $open;
	}
	
	public static function isCashAvailable($merchant_id='')
	{
		$cod=self::t("Cash on delivery available");
        if ( Yii::app()->functions->isMerchantCommission($merchant_id)){
        	$paymentgateway=getOptionA('paymentgateway');
        	$paymentgateway=!empty($paymentgateway)?json_decode($paymentgateway,true):false;
        	if($paymentgateway!=false){
        		if(!in_array('cod',(array)$paymentgateway)){
        			$cod='';
        		}
        	}
        } else {
        	if (getOption($merchant_id,'merchant_disabled_cod')!=""){
        		$cod='';
        	}
        }
        return $cod;
	}
	
	public static function getMerchantLogo($merchant_id='')
	{		
		if ( !$logo = getOption($merchant_id,'merchant_photo') ){
			$logo="mobile-default-logo.png";
		}
		return Yii::app()->getBaseUrl(true)."/upload/$logo";
	}
	
	public static function getImage($image='')
	{		
		$default="mobile-default-logo.png";
		$path_to_upload=Yii::getPathOfAlias('webroot')."/upload";		
		if (!empty($image)){
			if (file_exists($path_to_upload."/$image")){			
				$default=$image;
			}				
		}
		return Yii::app()->getBaseUrl(true)."/upload/$default";
	}
	
	public static function merchantInformation($merchant_id='')
	{
		$data='';
		
		if ($merchantinfo=Yii::app()->functions->getMerchant($merchant_id)){
					
			$data['merchant_id']=$merchant_id;
			$data['restaurant_name']=$merchantinfo['restaurant_name'];
			$data['country']=Yii::app()->functions->countryCodeToFull($merchantinfo['country_code']);
			$data['address']=$merchantinfo['street']." ".$merchantinfo['city']." ".
			$merchantinfo['state']." ".$merchantinfo['post_code']." ".$data['country'];			
						
			/*check if mechant is open*/			
			$data['open']=AddonMobileApp::isMerchantOpen($merchant_id);
			$data['merchant_close_store']=getOption($merchant_id,'merchant_close_store')=="yes"?true:false;
			
			$minimum_order=getOption($merchant_id,'merchant_minimum_order');
			$minimum_order_raw=$minimum_order;
 			if(!empty($minimum_order)){
	 			$minimum_order=displayPrice(getCurrencyCode(),prettyFormat($minimum_order));
 			}
 			$data['minimum_order']=$minimum_order;
 			$data['minimum_order_raw']=$minimum_order_raw;
 			
 			$data['logo']=AddonMobileApp::getMerchantLogo($merchant_id);
 			
 			$delivery_fee=getOption($merchant_id,'merchant_delivery_charges');
 			$delivery_fee_raw=$delivery_fee;
 			if (!empty($delivery_fee)){
 				$delivery_fee=displayPrice(getCurrencyCode(),prettyFormat($delivery_fee));
 			}
 			$data['delivery_fee']=$delivery_fee;
 			$data['delivery_fee_raw']=$delivery_fee_raw;
			 			
 			$data['ratings']=Yii::app()->functions->getRatings($merchant_id);
 			
 			if ( $res_offers=Yii::app()->functions->getMerchantOffersActive($merchant_id)){ 				
 				unset($res_offers['date_created']);
 				unset($res_offers['date_modified']);
 				unset($res_offers['ip_address']);
 				$res_offers['message']=number_format($res_offers['offer_percentage'],0)."% ".
 				self::t("off today on orders over")." ".
 				displayPrice(getCurrencyCode(),prettyFormat($res_offers['offer_price']));
 				
 				$data['offers_found']=2;
 				$data['offers']=$res_offers;
 			} else $data['offers_found']=1;
 			
 			$data['free_delivery']=1;
 			$price_above=Yii::app()->functions->getOption("free_delivery_above_price",$merchant_id); 			
 			if(is_numeric($price_above) && $price_above>=1){
 				$data['free_delivery']=2;
 				$data['free_price']=$price_above;
 				$data['free_price_pretty']=displayPrice(getCurrencyCode(),prettyFormat($price_above));
 				$data['free_message']=self::t("Free Delivery On Orders Over")." ".
 				displayPrice(getCurrencyCode(),prettyFormat($price_above));
 			}
 			 			
 			$resto_cuisine='';
 			$cuisine_list=Yii::app()->functions->Cuisine(true);	  			
 			$cuisine=!empty($merchantinfo['cuisine'])?(array)json_decode($merchantinfo['cuisine']):false;  
 			if($cuisine!=false){
 				foreach ($cuisine as $valc) {	    						
					if ( array_key_exists($valc,(array)$cuisine_list)){
						$resto_cuisine.=$cuisine_list[$valc].", ";
					}				
				}
				$resto_cuisine=!empty($resto_cuisine)?substr($resto_cuisine,0,-2):'';
 			} 		 		
 			$data['cuisine']=$resto_cuisine;
 			
 			return $data; 	
		}
		return false;
	}
	
	public static function prettyPrice($amount='')
	{
		if(!empty($amount)){
			return displayPrice(getCurrencyCode(),prettyFormat($amount));
		}
		return false;
	}
	
	public static function isArray($data='')
	{
		if (is_array($data) && count($data)>=1){
			return true;
		}
		return false;
	}	
	
	public static function getDeliveryCharges($merchant_id='',$unit='',$distance='')
	{				
		$delivery_fee=0;
		
		$default_delivery_charges=getOption($merchant_id,'merchant_delivery_charges');
				
		if ($default_delivery_charges<0){
			return false;
		}

		$FunctionsK=new FunctionsK();
		$delivery_fee=$FunctionsK->getDeliveryChargesByDistance(
    	$merchant_id,
    	$distance,
    	$unit,
    	$default_delivery_charges); 			
    	return array(
    	  'delivery_fee'=>$delivery_fee,
    	  'unit'=>$unit,
    	  'use_distance'=>$distance
    	);	
		
		return false;		
	}
	
	public static function getDistance($merchant_id='',$customer_address='')
	{
		$merchant_distance_type=Yii::app()->functions->getOption("merchant_distance_type",$merchant_id);
		
		$DbExt=new DbExt; 
		$stmt="SELECT concat(street,' ',city,' ',state,' ',post_code,' ',country_code) as merchant_address
		FROM
		{{merchant}}
		WHERE
		merchant_id=".self::q($merchant_id)."
		LIMIT 0,1
		";
		$merchant_address='';
		if($res=$DbExt->rst($stmt)){
			$merchant_address=$res[0]['merchant_address'];
		}
		
		$miles=getDeliveryDistance2($customer_address,$merchant_address); 
		if (self::isArray($miles)){			
			if ( $merchant_distance_type=="km"){
				$unit="km";
				$use_distance=$miles['km'];
			} else {
				$unit='miles';
				$use_distance=$miles['mi'];
			}
			if (preg_match("/ft/i",$miles['mi'])) {
				$unit='ft';
			}
			
			return array(
			  'unit'=>$unit,
			  'distance'=>$use_distance
			);
		}	
		return false;
	}
	
	public static function parseValidatorError($error='')
	{
		$error_string='';
		if (is_array($error) && count($error)>=1){
			foreach ($error as $val) {
				$error_string.="$val\n";
			}
		}
		return $error_string;		
	}		
		
	public static function generateUniqueToken($length,$unique_text=''){	
		$key = '';
	    $keys = array_merge(range(0, 9), range('a', 'z'));	
	    for ($i = 0; $i < $length; $i++) {
	        $key .= $keys[array_rand($keys)];
	    }	
	    return $key.md5($unique_text);
	}
			
	public static function computeCart($data='')
	{
		
		if ($data['transaction_type']=="null" || empty($data['transaction_type'])){
			$data['transaction_type']="delivery";
		}
		
		if ($data['delivery_date']=="null" || empty($data['delivery_date'])){
			$data['delivery_date']=date("Y-m-d");
		}
				
		$mtid=$data['merchant_id'];
		
		$cart_content='';
		$subtotal=0;
		$taxable_total=0;
		$item_total=0;
		
		Yii::app()->functions->data="list";
		$subcat_list=Yii::app()->functions->getSubcategory2($mtid);		
		
		$cart=json_decode($data['cart'],true);
		
		if (is_array($cart) && count($cart)>=1){
			foreach ($cart as $val) {
			    				    	
		    	/*group sub item*/
		    	$new_sub='';
		    	if (AddonMobileApp::isArray($val['sub_item'])){
		    		foreach ($val['sub_item'] as $valsubs) {			    			
		    			$new_sub[$valsubs['subcat_id']][]=array( 
		    			  'value'=>$valsubs['value'],
		    			  'qty'=>$valsubs['qty']
		    			);
		    		}
		    		$val['sub_item']=$new_sub;
		    	}		
		    				    				    				   
		    	$item_price=0;
		    	$item_size='';
		    	$temp_price=explode("|",$val['price']);			    	
		    	if (AddonMobileApp::isArray($temp_price)){
		    		$item_price=isset($temp_price[0])?$temp_price[0]:'';
		    		$item_size=isset($temp_price[1])?$temp_price[1]:'';
		    	}			    
		    		    	
		    	$food=Yii::app()->functions->getFoodItem($val['item_id']);			    	
		    	
		    	/*check if item qty is less than 1*/
		    	if($val['qty']<1){
		    		$val['qty']=1;
		    	}			    
		    	
		    	$discounted_price=0;
		    	if ($val['discount']>0){
		    		$discounted_price=$item_price-$val['discount'];
		    		$subtotal+=($val['qty']*$discounted_price);
		    	} else {
		    		$subtotal+=($val['qty']*$item_price);
		    	}			    
		    				    	
		    	//$subtotal+=($val['qty']*$item_price);
		    	$taxable_total=$subtotal;
		    	
		    	$item_total+=$val['qty'];
		    				    	
		    	$sub_item='';
		    	if(is_array($val['sub_item']) && count($val['sub_item'])>=1){
		    		foreach ($val['sub_item'] as $sub_cat_id=> $valsub0) {			    			
		    			foreach ($valsub0 as $valsub) {				    				
			    			if(!empty($valsub['value'])){
			    				$sub=explode("|",$valsub['value']);
			    				
			    				if ( $valsub['qty']=="itemqty"){
			    				   $qty=$val['qty'];
			    				} else {
			    					$qty=$valsub['qty'];
			    					if ($qty<1){
			    						$qty=1;
			    						$valsub['qty']=1;
			    					}				    				
			    				}				    			
			    				
			    				$subitem_total=($qty*$sub[1]);
			    				$subtotal+=$subitem_total;
			    				$taxable_total+=$subitem_total;
			    								    				
			    				$category_name='';
			    				if(array_key_exists($sub_cat_id,(array)$subcat_list)){
			    					$category_name=$subcat_list[$sub_cat_id];
			    				}			    			
			    				
			    				$sub_item[$category_name][]=array(
			    				  'subcat_id'=>$sub_cat_id,
			    				  'category_name'=>$category_name,
			    				  'sub_item_id'=>$sub[0],
			    				  'price'=>$sub[1],
			    				  'price_pretty'=>AddonMobileApp::prettyPrice($sub[1]),
			    				  'qty'=>$valsub['qty'],
			    				  'total'=>$subitem_total,
			    				  'total_pretty'=>AddonMobileApp::prettyPrice($subitem_total),
			    				  'sub_item_name'=>$sub[2]				    				  
			    				);
			    			}
		    			}
		    		}
		    	}
		    	
		    	$cooking_ref='';
		    	if (AddonMobileApp::isArray($val['cooking_ref'])){
		    		foreach ($val['cooking_ref'] as $valcook) {
		    			$cooking_ref[]=$valcook['value'];
		    		}
		    	}
		    	
		    	$ingredients='';
		    	if (AddonMobileApp::isArray($val['ingredients'])){
		    		foreach ($val['ingredients'] as $valing) {
		    			$ingredients[]=$valing['ingredients'];
		    		}
		    	}
		    	
		    	$cooking_ref='';
		    	if(AddonMobileApp::isArray($val['cooking_ref'])){
		    		$cooking_ref=$val['cooking_ref'][0]['value'];
		    	}
		    	$ingredients='';
		    	if(AddonMobileApp::isArray($val['ingredients'])){
		    		foreach ($val['ingredients'] as $val_ing) {
		    			$ingredients[]=$val_ing['value'];
		    		}
		    	}			    

		    	$discount_amt=0;
		    	if (isset($val['discount'])){
		    		$discount_amt=$val['discount'];
		    	}
			    	  			   
		    	$cart_content[]=array(
		    	  'item_id'=>$val['item_id'],
		    	  'item_name'=>$food['item_name'],
		    	  'item_description'=>$food['item_description'],
		    	  'qty'=>$val['qty'],
		    	  'price'=>$item_price,
		    	  'price_pretty'=>AddonMobileApp::prettyPrice($item_price),
		    	  'total'=>$val['qty']*($item_price-$discount_amt),
		    	  'total_pretty'=>AddonMobileApp::prettyPrice($val['qty']* ($item_price-$discount_amt) ),
		    	  'size'=>$item_size,		
		    	  'discount'=>isset($val['discount'])?$val['discount']:'',
			      'discounted_price'=>$discounted_price,
			      'discounted_price_pretty'=>AddonMobileApp::prettyPrice($discounted_price),	
		    	  'cooking_ref'=>$cooking_ref,
		    	  'ingredients'=>$ingredients,
		    	  'order_notes'=>$val['order_notes'],
		    	  'sub_item'=>$sub_item
		    	);
		    	
		    } /*end foreach*/
		    
            $ok_distance=2;
		    $delivery_charges=0;
		    $distance='';
		    
		    if ( $data['transaction_type']=="delivery"){					    			    	
		    	if($distance=AddonMobileApp::getDistance($mtid,$data['search_address'])){
		    	  $mt_delivery_miles=Yii::app()->functions->getOption("merchant_delivery_miles",$mtid); 	
		    	  if($mt_delivery_miles>0){
		    	  	 if ($mt_delivery_miles<=$distance['distance']){
		    	  	 	$ok_distance=1;
		    	  	 }
		    	  }
		    	  			    		
				  if($res_delivery=AddonMobileApp::getDeliveryCharges($mtid,$distance['unit'],$distance['distance'])){
					 $delivery_charges=$res_delivery['delivery_fee'];										
				  }
		    	}
		    }
			
			$merchant_tax_percent=0;
			$merchant_tax=getOption($mtid,'merchant_tax');			
			
            /*get merchant offers*/
	    	$discount='';
	    	if ( $offer=Yii::app()->functions->getMerchantOffersActive($mtid)){			    		
	    		$merchant_spend_amount=$offer['offer_price'];
	        	$merchant_discount_amount=number_format($offer['offer_percentage'],0);			        	
	        	if ( $subtotal>=$merchant_spend_amount){
	        		$merchant_discount_amount1=$merchant_discount_amount/100;
	        		$discounted_amount=$subtotal*$merchant_discount_amount1;
	        		
	        		$subtotal-=$discounted_amount;
	        		if ( $food['non_taxable']==1){
	        		    $taxable_total-=$discounted_amount;
	        		}		        		
	        		$discount=array(
	        		  'discount'=>$merchant_discount_amount,
	        		  'amount'=>$discounted_amount,
	        		  'amount_pretty'=>AddonMobileApp::prettyPrice($discounted_amount),
	        		  'display'=>self::t("Discount")." ".number_format($offer['offer_percentage'],0)."%"
	        		);
	        	}
	    	}			
						    	
	        /*check if has offer for free delivery*/
	    	$free_delivery_above_price=getOption($mtid,'free_delivery_above_price');
	        if(is_numeric($free_delivery_above_price)){
	        	if ($subtotal>$free_delivery_above_price){
	        		$delivery_charges=0;
	        	}			        
	        }
	        
	        /*packaging*/		        
	        $merchant_packaging_charge=getOption($mtid,'merchant_packaging_charge');
	        if ($merchant_packaging_charge>0){
	        	if ( getOption($mtid,'merchant_packaging_increment')==2){		 		      		        		
	        		$merchant_packaging_charge=$merchant_packaging_charge*$item_total;
	        	}
	        } else $merchant_packaging_charge=0;	

           /*get the tax*/
	        $tax=0;
	        if ( $merchant_tax>0){
	        	$merchant_tax_charges=getOption($mtid,'merchant_tax_charges');
	        	if ( $merchant_tax_charges==2){
	        		$tax=($taxable_total+$merchant_packaging_charge)*($merchant_tax/100);
	        	} else $tax=($taxable_total+$delivery_charges+$merchant_packaging_charge)*($merchant_tax/100);
	        }			    
	        		        			    
			$cart_final_content=array(
			  'cart'=>$cart_content,
			  'sub_total'=>array(
			    'amount'=>$subtotal,
			    'amount_pretty'=>AddonMobileApp::prettyPrice($subtotal)
			  )			      
			);				
							
			if (AddonMobileApp::isArray($discount)){
				$cart_final_content['discount']=$discount;
			}

			if ($delivery_charges>0){
				$cart_final_content['delivery_charges']=array(
				  'amount'=>$delivery_charges,
				  'amount_pretty'=>AddonMobileApp::prettyPrice($delivery_charges)
				);
			}
			if ($merchant_packaging_charge>0){
				$cart_final_content['packaging']=array(
				  'amount'=>$merchant_packaging_charge,
				  'amount_pretty'=>AddonMobileApp::prettyPrice($merchant_packaging_charge)
				);					
			}
			if ($tax>0){
				$cart_final_content['tax']=array(
				  'tax'=>$merchant_tax>0?$merchant_tax/100:0,
				  'amount_raw'=>$tax,
				  'amount'=>AddonMobileApp::prettyPrice($tax),
				  'tax_pretty'=>self::t("Tax")." ".$merchant_tax."%"
				);					
			}
			
			$grand_total=$subtotal+$delivery_charges+$merchant_packaging_charge+$tax;
			$cart_final_content['grand_total']=array(
			  'amount'=>$grand_total,
			  'amount_pretty'=>AddonMobileApp::prettyPrice($grand_total)
			);
			
			/*validation*/																
			$validation_msg='';
							
			if ( $data['transaction_type']=="delivery"){
			if ($ok_distance==1){
				$distanceOption=Yii::app()->functions->distanceOption();
				$validation_msg=t("Sorry but this merchant delivers only with in ").
				getOption($mtid,'merchant_delivery_miles')." ".$distanceOption[getOption($mtid,'merchant_distance_type')];
			}
			}
			
			if ( $data['transaction_type']=="delivery"){
				/*delivery*/
				$minimum_order=getOption($mtid,'merchant_minimum_order');
			    $maximum_order=getOption($mtid,'merchant_maximum_order');
			    if(is_numeric($minimum_order)){				    	
			    	if ($subtotal<=$minimum_order){
			    		$validation_msg=self::t("Sorry but Minimum order is")." ".AddonMobileApp::prettyPrice($minimum_order);
			    	}				    
			    }
			    if(is_numeric($maximum_order)){				    	
			    	if ($subtotal>=$maximum_order){
			    		$validation_msg=self::t("Maximum Order is")." ".AddonMobileApp::prettyPrice($maximum_order);
			    	}				    
			    }				    				    
			} else {
				/*pickup*/
				$minimum_order_pickup=getOption($mtid,'merchant_minimum_order_pickup');
			    $maximum_order_pickup=getOption($mtid,'merchant_maximum_order_pickup');
			    if(is_numeric($minimum_order_pickup)){				    	
			    	if ($subtotal<=$minimum_order_pickup){
			    		$validation_msg=self::t("sorry but the minimum pickup order is")." ".
			    		AddonMobileApp::prettyPrice($minimum_order_pickup);
			    	}				    
			    }
			    if(is_numeric($maximum_order_pickup)){				    	
			    	if ($subtotal>=$maximum_order_pickup){
			    		$validation_msg=self::t("sorry but the maximum pickup order is")." ".
			    		AddonMobileApp::prettyPrice($maximum_order_pickup);
			    	}				    
			    }
			}			
			
			return array(			  
			  'cart'=>$cart_final_content,
			  'validation_msg'=>$validation_msg,
			  'distance'=>$distance
			);
				           	    
		} /*end is array*/
		
		return false;
	}
	
	public static function cartMobile2WebFormat($data='',$post_data='')
	{
		//dump($post_data);
		$json_data='';
		if (self::isArray($data['cart']['cart'])){
			foreach ($data['cart']['cart'] as $val) {				
				$sub_item=''; $addon_qty=''; $addon_ids='';
				if (self::isArray($val['sub_item'])){
					foreach ($val['sub_item'] as $key=>$val2) {
						//foreach ($val['sub_item'][$key] as $val2) {						
						foreach ($val2 as $val3) {
							$sub_item[$val3['subcat_id']]=array(
							$val3['sub_item_id']."|".$val3['price']."|".$val3['sub_item_name']
							);			
							$addon_qty[]=$val3['qty']=="itemqty"?$val['qty']:$val3['qty'];
							$addon_ids[]=$val3['sub_item_id'];
						}
					}
				}
															
				$json_data[]=array(
				  'item_id'=>$val['item_id'],
				  'merchant_id'=>$post_data['merchant_id'],
				  'discount'=>$val['discount'],
				  'price'=>$val['price']."|".$val['size'],
				  'qty'=>$val['qty'],
				  'cooking_ref'=>$val['cooking_ref'],
				  'ingredients'=>$val['ingredients'],
				  'order_notes'=>$val['order_notes'],
				  'sub_item'=>$sub_item,
				  'addon_qty'=>array($addon_qty),
				  'addon_ids'=>$addon_ids
				);
			}
			//dump($json_data);
			return $json_data;
		}
		return json_encode(array());
	}
	
	public static function getClientTokenInfo($token='')
	{
		$DbExt=new DbExt; 
		$stmt="
		SELECT * FROM
		{{client}}
		WHERE
		token=".self::q($token)."
		LIMIT 0,1
		";				
		if ($res=$DbExt->rst($stmt)){			
			return $res[0];
		}
		return false;
	}
	
	public static function getMerchantPaymentMethod($mtid='')
	{
		$merchant_payment_list='';
				
		$mobile_payment=array('cod','paypal','pyr');
			
		$payment_list=getOptionA('paymentgateway');
		$payment_list=!empty($payment_list)?json_decode($payment_list,true):false;		
		
		$pay_on_delivery_flag=false;
		
		if (AddonMobileApp::isArray($payment_list)){			
			foreach ($mobile_payment as $val) {
				if(in_array($val,(array)$payment_list)){					
					switch ($val) {
						case "cod":			
						    if (Yii::app()->functions->isMerchantCommission($mtid)){
						    	$merchant_payment_list[]=array(
								  'icon'=>'fa-usd',
								  'value'=>$val,
								  'label'=>self::t("Cash On delivery")
								);
						    	continue;
						    }
							if ( getOption($mtid,'merchant_disabled_cod')!="yes"){
								$merchant_payment_list[]=array(
								  'icon'=>'fa-usd',
								  'value'=>$val,
								  'label'=>self::t("Cash On delivery")
								);
							}
							break;
					
						case "paypal":	
						  
						  if (Yii::app()->functions->isMerchantCommission($mtid)){
						  	  if ( getOptionA('adm_paypal_mobile_enabled')=="yes"){
						  	  	$merchant_payment_list[]=array(
							       'icon'=>'fa-paypal',
							        'value'=>$val,
							        'label'=>self::t("Paypal")
							    );
						  	  }						  
						  	  continue;
						  }
						  
						  if (getOption($mtid,'mt_paypal_mobile_enabled') =="yes"){
						      $merchant_payment_list[]=array(
							     'icon'=>'fa-paypal',
							     'value'=>$val,
							     'label'=>self::t("Paypal")
							  );
						   }
						   break;
						
						case "pyr":	
						    $pay_on_delivery_flag=true;
						   if (Yii::app()->functions->isMerchantCommission($mtid)){
						   	   $merchant_payment_list[]=array(
							    'icon'=>'fa-cc-visa',
							    'value'=>$val,
							    'label'=>self::t("Pay On Delivery")
							   );
						   	   continue;
						   }
						   if ( getOption($mtid,'merchant_payondeliver_enabled')=="yes"){
						      $merchant_payment_list[]=array(
							    'icon'=>'fa-cc-visa',
							    'value'=>$val,
							    'label'=>self::t("Pay On Delivery")
							  );
						   }
						   break;
						         
						default:
							break;
					}					
				}			
			}
			
			if (AddonMobileApp::isArray($merchant_payment_list)){				
				return $merchant_payment_list;
			}	
		} 
		return false;
	}
	
	public static function getOperationalHours($merchant_id='')
	{
        $stores_open_day=Yii::app()->functions->getOption("stores_open_day",$merchant_id);
		$stores_open_starts=Yii::app()->functions->getOption("stores_open_starts",$merchant_id);
		$stores_open_ends=Yii::app()->functions->getOption("stores_open_ends",$merchant_id);
		$stores_open_custom_text=Yii::app()->functions->getOption("stores_open_custom_text",$merchant_id);
		
		$stores_open_day=!empty($stores_open_day)?(array)json_decode($stores_open_day):false;
		$stores_open_starts=!empty($stores_open_starts)?(array)json_decode($stores_open_starts):false;
		$stores_open_ends=!empty($stores_open_ends)?(array)json_decode($stores_open_ends):false;
		$stores_open_custom_text=!empty($stores_open_custom_text)?(array)json_decode($stores_open_custom_text):false;
		
		
		$stores_open_pm_start=Yii::app()->functions->getOption("stores_open_pm_start",$merchant_id);
		$stores_open_pm_start=!empty($stores_open_pm_start)?(array)json_decode($stores_open_pm_start):false;
		
		$stores_open_pm_ends=Yii::app()->functions->getOption("stores_open_pm_ends",$merchant_id);
		$stores_open_pm_ends=!empty($stores_open_pm_ends)?(array)json_decode($stores_open_pm_ends):false;		
						
		$tip='';						
		$open_starts='';
		$open_ends='';
		$open_text='';		
		if (is_array($stores_open_day) && count($stores_open_day)>=1){
			foreach ($stores_open_day as $val_open) {	
				if (array_key_exists($val_open,(array)$stores_open_starts)){
					$open_starts=timeFormat($stores_open_starts[$val_open],true);
				}							
				if (array_key_exists($val_open,(array)$stores_open_ends)){
					$open_ends=timeFormat($stores_open_ends[$val_open],true);
				}							
				if (array_key_exists($val_open,(array)$stores_open_custom_text)){
					$open_text=$stores_open_custom_text[$val_open];
				}					
				
				$pm_starts=''; $pm_ends=''; $pm_opens='';
				if (array_key_exists($val_open,(array)$stores_open_pm_start)){
					$pm_starts=timeFormat($stores_open_pm_start[$val_open],true);
				}											
				if (array_key_exists($val_open,(array)$stores_open_pm_ends)){
					$pm_ends=timeFormat($stores_open_pm_ends[$val_open],true);
				}								
							
				$full_time='';
				if (!empty($open_starts) && !empty($open_ends)){					
					$full_time=$open_starts." - ".$open_ends."&nbsp;&nbsp;";
				}			
				if (!empty($pm_starts) && !empty($pm_ends)){
					if ( !empty($full_time)){
						$full_time.=" / ";
					}				
					$full_time.="$pm_starts - $pm_ends";
				}
																				
				$tip.= ucwords(self::t($val_open))." ".$full_time." ".$open_text."<br/>";
				
				$open_starts='';
		        $open_ends='';
		        $open_text='';
			}
		} else $tip=self::t("Not available.");
		return $tip;
	}	
	
	public static function previewMerchantReview($mtid='')
	{
		$DbExt=new DbExt; 
		$stmt="
		SELECT a.client_id,
		a.date_created,
		(
		  select first_name
		  from {{client}}
		  where
		  client_id=a.client_id
		) as client_name
		,
		(
		  select count(*) as total_review
		  from {{review}}
		  where
		  merchant_id=".self::q($mtid)."
		) as total_review
		 FROM
		{{review}} a
		WHERE
		merchant_id=".self::q($mtid)."
		AND status IN ('publish','published')
		ORDER BY date_created ASC
		LIMIT 0,1
		";
		if ( $res=$DbExt->rst($stmt)){
			return $res[0];
		}
		return false;
	}
	
	public static function getOrderDetails($order_id='')
	{
		$DbExt=new DbExt; 
		$stmt="
		SELECT a.item_name,
		a.qty,
		(
		  select total_w_tax 
		  from {{order}}
		  where
		  order_id= a.order_id
		) as total_w_tax
		FROM
		{{order_details}} a
		WHERE
		order_id=".self::q($order_id)."
		ORDER BY id ASC
		";
		if ($res=$DbExt->rst($stmt)){
			return $res;
		}
		return false;
	}
	
    public static function getAddressBook($client_id='')
    {
    	$db_ext=new DbExt;    	
    	$stmt="SELECT  
    	       concat(street,' ',city,' ',state,' ',zipcode) as address,
    	       id,location_name,country_code,as_default,
    	       street,city,state,zipcode,location_name
    	       FROM
    	       {{address_book}}
    	       WHERE
    	       client_id =".self::q($client_id)."
    	       ORDER BY id DESC    	       
    	";    	    	
    	if ($res=$db_ext->rst($stmt)){    		
    		return $res;
    	}
    	return false;
    } 	        	
    
    public static function hasAddressBook($client_id='')
    {
    	$db_ext=new DbExt;    	
    	$stmt="SELECT * FROM
    	{{address_book}}
    	WHERE client_id =".self::q($client_id)."
    	LIMIT 0,1
    	";    	    	
    	if ($res=$db_ext->rst($stmt)){    		
    		return $res;
    	}
    	return false;
    }
    
    public static function checkifEmailExists($email='')
    {
    	$db_ext=new DbExt;    	
    	$stmt="SELECT client_id,email_address,first_name,last_name,
    	password FROM
    	{{client}}
    	WHERE email_address =".self::q($email)."
    	LIMIT 0,1
    	";    	    	
    	if ($res=$db_ext->rst($stmt)){    		
    		return $res[0];
    	}
    	return false;
    }
    
    public static function getDeviceID($device_id='')
    {
    	$db_ext=new DbExt;    	
    	$stmt="SELECT * FROM
    	{{mobile_registered}}
    	WHERE
    	device_id=".self::q($device_id)."
    	LIMIT 0,1
    	";    	    	
    	if ($res=$db_ext->rst($stmt)){    		
    		return $res[0];
    	}
    	return false;
    }
    
   public static function getMerchantInfo($merchant_id='')
    {
    	$db_ext=new DbExt;    	
    	$stmt="SELECT * FROM
    	{{merchant}}
    	WHERE
    	merchant_id=".self::q($merchant_id)."
    	LIMIT 0,1
    	";    	    	
    	if ($res=$db_ext->rst($stmt)){    		
    		return $res[0];
    	}
    	return false;
    }    
	
}/* end class*/