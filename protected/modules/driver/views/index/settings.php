
<div id="layout_1">
<?php 
$this->renderPartial('/tpl/layout1_top',array(   
));
?> 
</div> <!--layout_1-->

<div class="parent-wrapper">

 <div class="content_1 white">   
   <?php 
   $this->renderPartial('/tpl/menu',array(   
   ));
   ?>
 </div> <!--content_1-->
 
 <div class="content_main settings-page">

   <div class="nav_option">
      <div class="row">
        <div class="col-md-6 ">
         <b><?php echo t("Settings")?></b>
        </div> <!--col-->
        <div class="col-md-6  text-right">
            
         <!--  <a class="green-button left rounded" href="javascript:;"><?php echo t("Add Task")?></a>
           <a class="orange-button left rounded" href="javascript:;"><?php echo t("Refresh")?></a>-->
         
        </div> <!--col-->
      </div> <!--row-->
   </div> <!--nav_option-->
  
   <div class="inner">
   
   <ul id="tabs">
	 <li class="active"><?php echo t("General Settings")?></li>
	 <li><?php echo t("iOS Settings")?></li>	 
	 <li><?php echo t("Cron Jobs")?></li>	 
	 <li><?php echo t("Update Database")?></li>	 
	</ul>
	
   <ul id="tab">  	
	
   <li class="active top30">
   
    <form id="frm" class="frm form-horizontal">
	 <?php echo CHtml::hiddenField('action','generalSettings')?>
	 
	 <div class="form-group">
	    <label class="col-sm-2 control-label"><?php echo Driver::t("Website title")?></label>
	    <div class="col-sm-6">
	     <?php echo CHtml::textField('driver_website_title',
	      getOptionA('driver_website_title')
	      ,array(
	       'class'=>"form-control"	       
	      ))?>	    
	    </div>
	  </div>
	 
	  <div class="form-group">
	    <label class="col-sm-2 control-label"><?php echo Driver::t("Your mobile API URL")?></label>
	    <div class="col-sm-6">
	     <span class="tag rounded"><?php echo websiteUrl()."/driver/api" ?></span>
	     <p class="text-muted">
	     <?php echo t("Set this url on your mobile app config files on")?> www/js/config.js
	     </p>
	    </div>
	  </div>
	 
	  <div class="form-group">	    
	  
	   <div class="form-group">
	    <label class="col-sm-2 control-label"><?php echo Driver::t("API Hash Key")?></label>
	    <div class="col-sm-6">
	      <?php echo CHtml::textField('driver_api_hash_key',
	      getOptionA('driver_api_hash_key')
	      ,array(
	       'class'=>"form-control"	       
	      ))?>	    
	    
	    <p class="top5 text-muted">
	    <?php echo t("Make your mobile api secure by putting hash key it can be a unique string without space")?>.<br/>
	    <?php echo t("Make sure you put the same key in your www/js/config.js")?>
	    </p>
	    </div>
	  </div>
	  
	  <hr/>
	  
	  <label class="col-sm-2 control-label"><?php echo Driver::t("Google Api Key")?></label>
	    <div class="col-sm-6">
	      <?php echo CHtml::textField('drv_google_api',
	      getOptionA('drv_google_api')
	      ,array(
	       'class'=>"form-control"	       
	      ))?>
	    <p class="top5 text-muted"><?php echo Driver::t("Enabled Google Maps Distance Matrix API, Google Maps Geocoding API and Google Maps JavaScript API in your google developer account")?>.</p>
	    <p class="top5 text-muted">
	     <?php echo t("When creating api key make sure its server key")?>.
	    </p>
	    </div>
	  </div>
	    
     <div class="form-group">
	    <label class="col-sm-2 control-label"><?php echo Driver::t("Push Android Key")?></label>
	    <div class="col-sm-6">
	      <?php
	      /*echo CHtml::textArea('driver_push_api_key',getOptionA('driver_push_api_key'),array(
	         'class'=>"form-control",
	         'style'=>"height:50px;"
	      ))*/
	      echo CHtml::textField('driver_push_api_key',getOptionA('driver_push_api_key'),array(
	        'class'=>"form-control",
	      ))
	      ?>	      
	    </div>
	  </div>	  	 
	  
	  <div class="form-group">
	    <label class="col-sm-2 control-label"><?php echo Driver::t("Send Push only to online driver")?></label>
	    <div class="col-sm-6">
	      <?php
	      echo CHtml::checkBox('driver_send_push_to_online',
	      getOptionA('driver_send_push_to_online')==1?true:false,array(
	        'class'=>"switch-boostrap"
	      ))
	      ?>	
	      <p class="text-muted top5">
	      <?php echo Driver::t("Send push notification only to online drivers when assigning task")?>.
	      </p>      
	    </div>
	  </div>	  	 
	   
	   <div class="form-group">
	    <label class="col-sm-2 control-label"><?php echo Driver::t("Include offline driver on map")?></label>
	    <div class="col-sm-6">
	      <?php
	      echo CHtml::checkBox('driver_include_offline_driver_map',
	      getOptionA('driver_include_offline_driver_map')==1?true:false,array(
	        'class'=>"switch-boostrap"
	      ))
	      ?>	      
	    </div>
	  </div>	  
	  
	  <div class="form-group">
	    <label class="col-sm-2 control-label"><?php echo Driver::t("Disabled Map Auto Refresh")?></label>
	    <div class="col-sm-6">
	      <?php
	      echo CHtml::checkBox('driver_disabled_auto_refresh',
	      getOptionA('driver_disabled_auto_refresh')==1?true:false,array(
	        'class'=>"switch-boostrap"
	      ))
	      ?>	      
	    </div>
	  </div>	  
	  

	  <hr/>  
	  
	   <div class="form-group">
	    <label class="col-sm-2 control-label"><?php echo Driver::t("Default Map Country")?></label>
	    <div class="col-sm-6">	      
	      <?php
	      $drv_default_location=getOptionA('drv_default_location');
	      echo CHtml::dropDownList('drv_default_location',
	      !empty($drv_default_location)?$drv_default_location:"US",
	      (array)$country_list,array(
	        'class'=>"form-control"
	      ))
	      ?>
	      <p class="text-muted top5">
	      <?php echo Driver::t("Set the default country to your map")?>
	      </p>
	    </div>
	  </div>	  
	  
	  <div class="form-group">
	    <label class="col-sm-2 control-label"><?php echo Driver::t("Order Status Accepted")?></label>
	    <div class="col-sm-6">
	      <?php
	      echo CHtml::dropDownList('drv_order_status',getOptionA('drv_order_status'),
	      (array)$order_status_list,array(
	        'class'=>"form-control"
	      ))
	      ?>
	      <p class="text-muted top5">
	      <?php echo Driver::t("The order status that will based to insert the order as task")?>
	      </p>
	    </div>
	  </div>	  
	  
	  <div class="form-group">
	    <label class="col-sm-2 control-label"><?php echo Driver::t("Order Status Cancel")?></label>
	    <div class="col-sm-6">
	      <?php
	      echo CHtml::dropDownList('drv_order_cancel',getOptionA('drv_order_cancel'),
	      (array)$order_status_list,array(
	        'class'=>"form-control"
	      ))
	      ?>
	      <p class="text-muted top5">
	      <?php echo Driver::t("The order status when merchant cancel the order")?>
	      </p>
	    </div>
	  </div>	  
	  
	  <div class="form-group">
	    <label class="col-sm-2 control-label"><?php echo Driver::t("Task Owner")?></label>
	    <div class="col-sm-6">
	      <?php
	      echo CHtml::dropDownList('driver_owner_task',
	      getOptionA('driver_owner_task')
	      ,array(
	        'default'=>Driver::t("Respective owner of task - default"),
	        'admin'=>Driver::t("admin"),
	        //'merchant'=>Driver::t("merchant"),
	      ),array(
	        'class'=>"form-control"
	      ))
	      ?>
	      <p class="text-muted top5">
	      <?php echo Driver::t("The owner of the task when merchant accept the order")?>
	      </p>
	    </div>
	  </div>	  
	  	 
	   <!--<div class="form-group">
	    <label class="col-sm-2 control-label"><?php echo Driver::t("Auto Add Order to task")?></label>
	    <div class="col-sm-6">
	     <?php 
	    /* echo CHtml::checkBox('ORDER_AUTO_ADD_TASK',
	     getOptionA('ORDER_AUTO_ADD_TASK')==1?true:false
	     ,array(
	      'class'=>"switch-boostrap"
	     ));*/
	     ?>
	     <p class="text-muted top5"><?php echo t("if set to yes once there is new order it will auto add to task list")?>.</p>	     
	    </div>
	  </div>	 -->
	  
	   <div class="form-group">
	    <label class="col-sm-2 control-label"><?php echo Driver::t("Delivery Time")?></label>
	    <div class="col-sm-6">
	      <?php
	      echo CHtml::dropDownList('drv_delivery_time',
	      getOptionA('drv_delivery_time'),	      
	      Driver::deliveryTimeOption()
	      ,array(
	        'class'=>"form-control"
	      ))
	      ?>
	      <p class="text-muted top5">	      
	      </p>
	    </div>
	  </div>	  
	  
	    <div class="form-group">
	    <label class="col-sm-2 control-label"><?php echo Driver::t("Map Style")?></label>
	    <div class="col-sm-6">
	      <?php
	      echo CHtml::textArea('drv_map_style',getOptionA('drv_map_style'),array(
	         'class'=>"form-control",
	         'style'=>"height:250px;"
	      ))
	      ?>
	      <p class="text-muted top5">
	      <?php echo Driver::t("Set the style of your map")?>.
	      <?php echo Driver::t("get it on")?> <a target="_blank" href="https://snazzymaps.com">https://snazzymaps.com</a>
	      <br/>
	      <?php echo Driver::t("leave it empty if if you are unsure")?>.
	      </p>
	    </div>
	  </div>	  
	  	
	  
	  <div class="form-group">
	    <label class="col-sm-2 control-label"></label>
	    <div class="col-sm-6">
		  <button type="submit" class="orange-button medium rounded">
		  <?php echo Driver::t("Save")?>
		  </button>
	    </div>	 
	  </div>
	  
     </form>		 
    </li> 
    
    <li> <!--START IOS-->
      <div class="inner">
      
      <form id="frm-ios" class="frm-ios form-horizontal" onsubmit="return false;">
	 <?php echo CHtml::hiddenField('action','saveIOSSettings')?>
	 <?php echo CHtml::hiddenField('driver_ios_push_dev_cer',$ios_push_dev_cer)?>
	 <?php echo CHtml::hiddenField('driver_ios_push_prod_cer',$ios_push_prod_cer)?>
      
      <div class="form-group">
	    <label class="col-sm-2 control-label"><?php echo Driver::t("IOS Push Mode")?></label>
	    <div class="col-sm-6">
	     <?php
	     echo CHtml::dropDownList('driver_ios_push_mode',getOptionA('driver_ios_push_mode'),array(
	       'development'=>Driver::t("Development"),
	       'production'=>Driver::t("Production"),
	     ),array(
	      'class'=>"form-control"
	     ))
	     ?>	    
	    </div>
	  </div>	  
	  
	  <div class="form-group">
	    <label class="col-sm-2 control-label"><?php echo Driver::t("IOS Push Certificate PassPhrase")?></label>
	    <div class="col-sm-6">
	     <?php
	     echo CHtml::textField('driver_ios_pass_phrase', getOptionA('driver_ios_pass_phrase'),array(
	       'class'=>"form-control",
	       'data-validation'=>"required"
	     ))
	     ?>	    
	    </div>
	  </div>
	  
	  <div class="form-group">
	    <label  class="col-sm-3 control-label" ><?php echo t("IOS Push Development Certificate")?></label>
	    <a id="upload-certificate-dev" href="javascript:;" class="btn btn-default"><?php echo t("Browse")?></a>        
	    <?php if (!empty($ios_push_dev_cer)):?>
	    <span><?php echo $ios_push_dev_cer?>...</span>
	    <?php endif;?>
	  </div>
	  
	   <div class="form-group">
	    <label  class="col-sm-3 control-label" ><?php echo t("IOS Push Production Certificate")?></label>
	    <a id="upload-certificate-prod" href="javascript:;" class="btn btn-default"><?php echo t("Browse")?></a> 
	    <?php if (!empty($ios_push_prod_cer)):?>
	    <span><?php echo $ios_push_prod_cer?>...</span>
	    <?php endif;?>
	  </div>
	  
	  
	   <div class="form-group">
	    <label class="col-sm-2 control-label"></label>
	    <div class="col-sm-6">
		  <button type="submit" class="orange-button medium rounded">
		  <?php echo Driver::t("Save")?>
		  </button>
	    </div>	 
	  </div>
	  
	  </form>  
      
      </div> <!--inner-->
    </li> <!--END IOS-->
    
    <li>
     <div class="inner">
     <h4><?php echo t("Run the following cron jobs link in your cpanel")?></h4>     
     <p>
     <a href="<?php echo Yii::app()->getBaseUrl(true)."/driver/cron/processpush"?>" target="_blank">
     <?php echo Yii::app()->getBaseUrl(true)."/driver/cron/processpush"?>
     </a>
     </p>
     
     <p>
     <a href="<?php echo Yii::app()->getBaseUrl(true)."/driver/cron/autoassign"?>" target="_blank">
     <?php echo Yii::app()->getBaseUrl(true)."/driver/cron/autoassign"?>
     </a>
     </p>
     
     <p>
     <a href="<?php echo Yii::app()->getBaseUrl(true)."/driver/cron/checkautoassign"?>" target="_blank">
     <?php echo Yii::app()->getBaseUrl(true)."/driver/cron/checkautoassign"?>
     </a>
     </p>
     
     <p>
     <a href="<?php echo Yii::app()->getBaseUrl(true)."/driver/cron/processbulk"?>" target="_blank">
     <?php echo Yii::app()->getBaseUrl(true)."/driver/cron/processbulk"?>
     </a>
     </p>
     
     <p>
      <b><?php echo t("example")?>: curl <?php echo Yii::app()->getBaseUrl(true)."/driver/cron/processpush"?></b>
     </p>
     </div>
    </li>
    
    <li>
    <div class="inner">
    <h4><?php echo t("Click below to update your database")?></h4>     
    
    <a href="<?php echo Yii::app()->getBaseUrl(true)."/driver/update"?>" target="_blank">
    <?php echo Yii::app()->getBaseUrl(true)."/driver/update"?>
    </a>
    
    </div>
    </li>
   
   </div> <!--inner-->
 
 </div> <!--content_2-->

</div> <!--parent-wrapper-->