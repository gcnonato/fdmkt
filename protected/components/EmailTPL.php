<?php
class EmailTPL extends CApplicationComponent
{
	
	public static function forgotPass($data='',$token='')
	{
      $website_title=Yii ::app()->functions->getOptionAdmin('website_title');
      $url=Yii::app()->getBaseUrl(true)."/store/forgotPassword/?token=".$token;      
	  return <<<HTML
	  <p>Hola $data[first_name]</p>
	  <br/>
	  <p>Hacé clic en el link de abajo para cambiar tu contraseña.</p>
	  <p><a href="$url">$url</a></p>
	  <p>¡Saludos!.</p>
	  <p>- $website_title</p>
HTML;
	}
	
	public static function merchantActivationCode($data='')
	{
	$website_url=Yii::app()->getBaseUrl(true)."/merchant";
    $website_title=Yii::app()->functions->getOptionAdmin('website_title');
    
    $email_tpl_activation=Yii::app()->functions->getOptionAdmin('email_tpl_activation');    
    if (!empty($email_tpl_activation)){
    	$email_tpl_activation=Yii::app()->functions->smarty("restaurant_name",$data['restaurant_name'],$email_tpl_activation); 
    	$email_tpl_activation=Yii::app()->functions->smarty("activation_key",$data['activation_key'],$email_tpl_activation); 
    	$email_tpl_activation=Yii::app()->functions->smarty("website_title",$website_title,$email_tpl_activation); 
    	$email_tpl_activation=Yii::app()->functions->smarty("website_url",$website_url,$email_tpl_activation); 
    	return $email_tpl_activation;
    }
    
	return <<<HTML
	<p>Hola $data[restaurant_name]<br/></p>
	<p>¡Gracias por formar parte de esta gran comunidad de emprendedores Artesanos de la Comida!</p>
	<p>En caso de que lo requieras, el código de activación asignado para vos es: $data[activation_key]</p>
	
	<p>Ingresá <a href="$website_url">aquí</a> para iniciar sesión</p>
	
	<p>Mucha suerte.</p>
	<p>- $website_title</p>
HTML;
	}
	
	public static function merchantActivationCodePlain()
	{		
	return <<<HTML
	<p>Hola {restaurant_name}<br/></p>
	<p>¡Gracias por formar parte de esta gran comunidad de emprendedores Artesanos de la Comida!</p>
	<p>El código de activación asignado para vos es: {activation_key}</p>
	
	<p>Ingresá <a href="{website_url}">aquí</a> para iniciar sesión</p>
	
	<p>Mucha suerte.</p>
	<p>- {website_title}</p>
HTML;
	}	
	
	public static function merchantForgotPass($data='',$code='')
	{
	  $website_title=Yii::app()->functions->getOptionAdmin('website_title');
	  
	  $email_tpl_forgot=Yii::app()->functions->getOptionAdmin('email_tpl_forgot');	
	  if (!empty($email_tpl_forgot)){
	  	  $email_tpl_forgot=Yii::app()->functions->smarty("restaurant_name",$data['restaurant_name'],$email_tpl_forgot); 
	  	  $email_tpl_forgot=Yii::app()->functions->smarty("verification_code",$code,$email_tpl_forgot); 
	  	  $email_tpl_forgot=Yii::app()->functions->smarty("website_title",$website_title,$email_tpl_forgot); 	  	  
	  	  return $email_tpl_forgot;	  	  
	  }
			  
	  return <<<HTML
	  <p>Hola $data[restaurant_name]<br/></p>
	  <p>Tu código de verificación es: $code</p>
	  <p>Saludos y mucha suerte.</p>
	<p>- $website_title</p>
HTML;
	}
	
	public static function merchantForgotPassPlain()
	{	 
	  return <<<HTML
	  <p>Hola {restaurant_name}<br/></p>
	  <p>Tu código de verificación es {verification_code}</p>
	  <p>Saludos y mucha suerte.</p>
	<p>- {website_title}</p>
HTML;
	}	
	
	public static function salesReceipt($data='',$item_details='')
	{				
		$tr="";
		if (is_array($data) && count($data)>=1){
			foreach ($data as $val) {				
				$tr.="<tr>";
				$tr.="<td>".$val['label']."</td>";
				$tr.="<td>".$val['value']."</td>";
				$tr.="</tr>";
			}
		}
		
		$mid=isset($item_details['total']['mid'])?$item_details['total']['mid']:'';
		//dump($mid);
		
		$tr.="<tr>";
		$tr.="<td colspan=\"2\">&nbsp;</td>";
		$tr.="</tr>";
		if (isset($item_details['item'])){
			if (is_array($item_details['item']) && count($item_details['item'])>=1){
				foreach ($item_details['item'] as $item) {
					//dump($item);
					$notes='';
					$item_total=$item['qty']*$item['discounted_price'];
					if (!empty($item['order_notes'])){
					    $notes="<p>".$item['order_notes']."</p>";
					}
					$cookref='';
					if (!empty($item['cooking_ref'])){
					    $cookref="<p>".$item['cooking_ref']."</p>";
					}
					$size='';
					if (!empty($item['size_words'])){
					    $size="<p>".$item['size_words']."</p>";
					}
					
					$ingredients='';
					if (isset($item['ingredients'])){
						if (is_array($item['ingredients']) && count($item['ingredients'])>=1){
							$ingredients.="<p>".t("Ingredients")."</p>";
							$ingredients.="<p>";
							foreach ($item['ingredients'] as $ingredients_val) {
								$ingredients.="- $ingredients_val<br/>";
							}
							$ingredients.="</p>";
						}
					}
					
					$tr.="<tr>";
				    $tr.="<td>".$item['qty']." ".$item['item_name'].$size.$notes.$cookref.$ingredients."</td>";
				    $tr.="<td>".prettyFormat($item_total,$mid)."</td>";
				    $tr.="</tr>";
				    
				    if (isset($item['sub_item'])){
				    	if (is_array($item['sub_item']) && count($item['sub_item'])>=1){
					    	foreach ($item['sub_item'] as $itemsub) {				    		
					    		$subitem_total=$itemsub['addon_qty']*$itemsub['addon_price'];				    		
					    		$tr.="<tr>";
					            $tr.="<td style=\"text-indent:10px;\">".$itemsub['addon_name']."</td>";
					            $tr.="<td>".prettyFormat($subitem_total,$mid)."</td>";
					            $tr.="</tr>";
					    	}
				    	}
				    }
				    
				}
			}
		}
		$tr.="<tr>";
		$tr.="<td colspan=\"2\">&nbsp;</td>";
		$tr.="</tr>";
		//dump($item_details['total']);	
		//dump($item_details['total']);
		
		if (isset($item_details['total'])){
			
			if ($item_details['total']['less_voucher']>0.001){
				$tr.="<tr>";
				$tr.="<td>".Yii::t("default","Less Voucher")." " .$item_details['total']['voucher_type'] . ":</td>";
				$tr.="<td>(".prettyFormat($item_details['total']['less_voucher'],$mid).")</td>";
				$tr.="</tr>";
			}
			
			if ($item_details['total']['pts_redeem_amt']>0.001){
				$tr.="<tr>";
				$tr.="<td>".Yii::t("default","Points discount").":</td>";
				$tr.="<td>(".prettyFormat($item_details['total']['pts_redeem_amt'],$mid).")</td>";
				$tr.="</tr>";
			}
			
			$tr.="<tr>";
			$tr.="<td>".Yii::t("default","Subtotal").":</td>";
			$tr.="<td>".prettyFormat($item_details['total']['subtotal'],$mid)."</td>";
			$tr.="</tr>";
			
			if (!empty($item_details['total']['delivery_charges'])):
			$tr.="<tr>";
			$tr.="<td>".Yii::t("default","Delivery Fee").":</td>";
			$tr.="<td>".prettyFormat($item_details['total']['delivery_charges'],$mid)."</td>";
			$tr.="</tr>";
			endif;
			
			if (!empty($item_details['total']['packaging'])):
			$tr.="<tr>";
			$tr.="<td>".Yii::t("default","Packaging").":</td>";
			$tr.="<td>".prettyFormat($item_details['total']['packaging'],$mid)."</td>";
			$tr.="</tr>";
			endif;
			
			$tr.="<tr>";
			$tr.="<td>".Yii::t("default","Tax")." ".$item_details['total']['tax_amt']."%</td>";
			$tr.="<td>".prettyFormat($item_details['total']['taxable_total'],$mid)."</td>";
			$tr.="</tr>";
			
			if (!isset($item_details['total']['card_fee'])){
				$item_details['total']['card_fee']='';
			}
			
			if ($item_details['total']['card_fee']>0):
			$tr.="<tr>";
			$tr.="<td>".Yii::t("default","Card Fee").":</td>";
			$tr.="<td>".prettyFormat($item_details['total']['card_fee'],$mid)."</td>";
			$tr.="</tr>";
			endif;
			
			if ($item_details['total']['tips']>0.001){
				$tr.="<tr>";
				$tr.="<td>".Yii::t("default","Tips")." " .$item_details['total']['tips_percent'] . ":</td>";
				$tr.="<td>".prettyFormat($item_details['total']['tips'],$mid)."</td>";
				$tr.="</tr>";
			}
			
			$tr.="<tr>";
			$tr.="<td>".Yii::t("default","Total").":</td>";
			$tr.="<td>".$item_details['total']['curr'].prettyFormat($item_details['total']['total'],$mid)."</td>";
			$tr.="</tr>";
		}
		ob_start();
		?>
		<div style="display: block;max-height: 70px;max-width: 200px;">
		<?php echo Widgets::receiptLogo();?>
		</div>
		<h3><?php echo Yii::t("default","Order Details")?></h3>		
		<table border="0">
		<?php echo $tr;?>		
		</table>
		<?php	
		$receipt = ob_get_contents();
        ob_end_clean();
        return $receipt;
	}
	
	public static function receiptTPL()
	{
		return <<<HTML
<p>Estimado {customer-name},</p>
<br/><br/>
<p> Gracias por comprar en Food Market y apoyar al artesano local, esperamos disfrutés al máximo tu comida! Tu número de recibo es {receipt-number}. Hemos incluido tu recibo de orden y detalles del envío abajo:
</p> <br/>
 {receipt}	
	
<br/><br/>
<p>¡Buen provecho!</p>
HTML;
	}
	
	public function bookingApproved()
	{
		return <<<HTML
<p>Estimado {customer-name},</p>
<br/><br/>
<p> Gracias. Tu reservacion ha sido aprobada</p>
<p>{booking-information}</p>
<br/>
	
<br/><br/>
<p>¡Buen provecho!</p>
HTML;
	}	
	
	public function bookingDenied()
	{
		return <<<HTML
<p>Dear {customer-name},</p>
<br/><br/>
<p> We regret to inform you that your table booking has been denied.</p>
<p>{booking-information}</p>
<br/>
	
<br/><br/>
<p> Kind Regards</p>
HTML;
	}		
	
	public function bookingTPL()
	{
		return <<<HTML
<p>Dear admin,</p>
<br/>
<p> New table booking has been receive.</p>
<p>{booking-information}</p>
<br/>
	
<br/><br/>
<p> Kind Regards</p>
HTML;
	}			
	

	public function bankDepositTPL()
	{
		return <<<HTML
<p><strong>Deposit Instructions</strong></p>
<br/>
<p>
Please deposit {amount} to :
</p>

<p>
Bank : Your bank name<br/>
Account Number : Your bank account number<br/>
Account Name : Your bank account name<br/>
</p>

<p>When deposit is completed {verify-payment-link}</p>

<br/><br/>
<p> Kind Regards</p>
HTML;
	}	
	
	public function bankDepositedReceive()
	{
		return <<<HTML
<p>Hola Admin,</p>
<br/><br/>
<p>There is new submitted offline bank deposited. you can check this via admin panel</p>
<br/>
	
<br/><br/>
HTML;
	}			
	
	public static function adminForgotPassword($newpass='')
	{	 
	  return <<<HTML
	  <p>Hola <br/></p>
	  <p>Tu contraseña ha sido cambiada a : $newpass</p>
	  <p>Saludos.</p>	
HTML;
	}	
	
	public static function merchantChangeStatus()
	{	 
	  return <<<HTML
  	<p style="text-align: center;"><img src="http://www.fmarket.org/assets/images/franja-logo.png"><br></p><p style="text-align: center;">Hola {owner_name},<br style="text-align: center;"></p>  <p style="text-align: center;">Tu Puesto de Comidas {restaurant_name} ha cambiado el estado <br>a <b style="text-align: center;">Activo.&nbsp;Para iniciar la personalización de tu puesto de comidas:</b></p><p style="text-align: center;"></p><ol><li>Iniciá sesión en&nbsp;<a href="https://www.fmarket.org/merchant">https://www.fmarket.org/merchant</a> con tu usuario y contraseña. Si olvidaste estos datos, podés recuperarlos en la opción "Recuperar contraseña".</li><li>Seguí los pasos que se explican en el siguiente video:<br><b><a href="https://youtu.be/9X2umONtqdk"><img src="http://www.fmarket.org/assets/images/youtube-link.png"></a></b></li><br><li>En caso de dudas, escribinos a antojo@fmarket.org</li></ol><b><b><p>{website_title}</p>  <p>Saludos.</p></b></b>	
HTML;
	}		
	
	public static function receiptMerchantTPL()
	{
		return <<<HTML
<p>Saludos querido Artesano de la comida, </p>
<br/>
<p>¡Has vendido! Hay una nueva orden de pedido con el número de referencia {receipt-number} del cliente {customer-name}</p>
<br/>
 {receipt}	
	
<br/><br/>
<p><a href="{confirmation-link}">Clic aquí</a> para aceptar la orden<br/>
o simplemente visitá este link {confirmation-link}
</p>
<br/>
<p>¡Saludos!</p>
HTML;
	}	
	
	public function payoutRequest()
	{
		return <<<HTML
<p>Hola {merchant-name},</p>
<br/>
<p>Te comunicamos que hemos recibido tu solicitud de retiro por {payout-amount} a través de depósuto bancario a la cuenta {account}</p>
<br/> 
	
<p>
Si crees que esta es incorrecto, podrés cancelar esta solicitud antes de {cancel-date} aquí:<br/>
{cancel-link}
</p>

<p>
Completaremos esta solicitud el {process-date} (o el siguiente día hábil), pero puede tomar hasta 7 días en que esto suceda. Recuerda que SINPE requiere un tiempo en procesar la transación.
</p>

<br/>
<p> Saludos.</p>
HTML;
	}
	
	public function payoutProcess()
	{
return <<<HTML
<p>Hola {merchant-name},</p>
<br/>
<p>Te comunicamos que hemos recibido tu solicitud de retiro por {payout-amount} a través de depósuto bancario a la cuenta {account}</p>
<br/> 

<p>Gracias por utilizar Food Market!</p>

<br/>
<p> Saludos</p>
HTML;
	}
	
	public static function faxNotification()
	{
return <<<HTML
<p>Hi admin,</p>
<br/>
<p>You have a new fax payment from <b>{merchant-name}</b> with the total amount of {amount}</p>
<p>Payment method : {payment-method}</p>
<p>Package Name : {package-name}</p>
<br/> 

<br/>
<p> Kind Regards</p>
HTML;
	}	
	
	public static function bankDepositedReceiveMerchant()
	{
		return <<<HTML
<p>Hola Artesano,</p>
<br/><br/>
<p>Alguien te ha hecho un nuevo depósito bancario. Podés revisar esto a través del panel administrativo en www.fmarket.org/merchant</p>
<br/>
	
<br/><br/>
HTML;
	}			
	
	public static function signupEmailVerification()
	{
		return <<<HTML
<p>Hola {firstname},</p>
<br/><br/>
<p>Tu código de verificación es: {code}</p>
<br/>
	
<br/><br/>
<p>Saludos.</p>
HTML;
	}				
		
} /*END CLASS*/