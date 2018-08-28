<?php
$this->renderPartial('/front/banner-receipt',array(
   'h1'=>t("Restaurant Signup"),
   'sub_text'=>t("step 2 of 4")
));

/*PROGRESS ORDER BAR*/
$this->renderPartial('/front/progress-merchantsignup',array(
   'step'=>2,
   'show_bar'=>true
));

?>

<link href="/assets/vendor/uikit/css/uikit.almost-flat.min.css" rel="stylesheet" />

<div class="sections section-grey2">

  <div class="container">
  
  <div class="row">  
  <div class="col-md-8 ">
    <div class="box-grey round top-line-green">
                  
     
       <form class="forms" id="forms" onsubmit="return false;">
	  <?php echo CHtml::hiddenField('action','merchantSignUp2')?>
	  <?php echo CHtml::hiddenField('currentController','store')?>	 
      
      <div class="row top10">
        <div class="col-md-3 "><?php echo t("Restaurant name")?></div>
        <div class="col-md-8 ">
             <?php echo CHtml::textField('restaurant_name',
			  isset($data['restaurant_name'])?$data['restaurant_name']:""
			  ,array(
			  'class'=>'grey-fields full-width',
			  'data-validation'=>"required"
			  ))?>
        </div>
      </div>
      
    <?php if ( getOptionA('merchant_reg_abn')=="yes"):?>
     <div class="row top10">
        <div class="col-md-3 "><?php echo t("ABN")?></div>
        <div class="col-md-8 ">
              <?php echo CHtml::textField('abn',
			  isset($data['restaurant_name'])?$data['abn']:""
			  ,array(
			  'class'=>'grey-fields full-width',
			  'data-validation'=>"required"
			  ))?>
        </div>
      </div>
     <?php endif;?>      
      
     <div class="row top10">
        <div class="col-md-3"><?php echo t("Restaurant phone")?></div>
        <div class="col-md-8">
         <?php echo CHtml::textField('restaurant_phone',
		  isset($data['restaurant_phone'])?$data['restaurant_phone']:""
		  ,array(
		  'class'=>'grey-fields full-width',
		  ))?>    
        </div>
      </div>
      
      <div class="row top10">
              <div class="col-md-3">Carnet de manipulación de alimentos</div>
        <div class="col-md-8">
          <select id="food_handling_card" required name="food_handling_card" class="grey-fields full-width">
            <option value="">Selecciona una opción </option>
            <option value="Al día">Al día</option>
            <option value="En proceso">En proceso</option>
            <option value="No cuento con permiso">No cuento con permiso</option>
          </select>
          <?php //echo CHtml::textField('restaurant_phone',
          // isset($data['restaurant_phone'])?$data['restaurant_phone']:""
          // ,array(
          // 'class'=>'grey-fields full-width',
          // ))?>    
        </div>
      </div>
 
    <div class="row top10" id="food_handling_card_pic_row" >
        <div class="col-md-3"></div>
        <div class="col-md-8">
           <!-- <input type="file" name="food_handling_card_pic" id="food_handling_card_pic"> -->
          <div class="uk-form-row"> 
          <!-- <label class="uk-form-label"><?php //echo Yii::t('default',"test")?></label> -->
            <div style="display:inline-table;margin-left:1px;" class="button uk-button" id="photo">
              <?php echo t('default',"Browse")?>
             
            </div>      
             <span style="color:red">Required*</span>
            <DIV  style="display:none;" class="photo_chart_status" >
              <div id="percent_bar" class="photo_percent_bar"></div>
              <div id="progress_bar" class="photo_progress_bar">
                <div id="status_bar" class="photo_status_bar"></div>
              </div>
            </DIV>          
          </div>
 
          <div class="image_preview">
        <?php if (!empty($data['photo'])):?>
        <input type="hidden" name="photo" id="hdn_photo" value="<?php echo $data['photo'];?>">
        <img class="uk-thumbnail uk-thumbnail-small" src="<?php echo Yii::app()->request->baseUrl."/upload/".$data['photo'];?>?>" alt="" title="">
        <?php endif;?>
        </div>
           
        </div>
 
   </div>

    <div class="row top10" id="message1" style="display:none !important;">
      <div class="col-md-3"> Permiso de manipulación de alimentos </div>
      <div class="col-md-8">
        <?php echo CHtml::textField('merchant_food_message',
        isset($data['merchant_food_message'])?$data['merchant_food_message']:""
        ,array(
        'class'=>'grey-fields full-width'
        //'data-validation'=>"required"
        ))?>           
      </div>
    </div>
 
      <div class="row top10">
        <div class="col-md-3"><?php echo t("Contact name")?></div>
        <div class="col-md-8">
		  <?php echo CHtml::textField('contact_name',
		  isset($data['contact_name'])?$data['contact_name']:""
		  ,array(
		  'class'=>'grey-fields full-width',
		  'data-validation'=>"required"
		  ))?>           
        </div>
      </div>
      
      <div class="row top10">
        <div class="col-md-3"><?php echo t("Contact phone")?></div>
        <div class="col-md-8">
		  <?php echo CHtml::textField('contact_phone',
		  isset($data['contact_phone'])?$data['contact_phone']:""
		  ,array(
		  'class'=>'grey-fields full-width',
		  'data-validation'=>"required"
		  ))?>           
        </div>
      </div>
      
      <div class="row top10">
        <div class="col-md-3"><?php echo t("Contact email")?></div>
        <div class="col-md-8">
		  <?php echo CHtml::textField('contact_email',
		  isset($data['contact_email'])?$data['contact_email']:""
		  ,array(
		  'class'=>'grey-fields full-width',
		  'data-validation'=>"email"
		  ))?>           
        </div>
      </div> 
      
      <div class="row top10">
        <div class="col-md-3"></div>
        <div class="col-md-8">
        <p class="text-muted text-small"><?php echo t("Important: Please enter your correct email. we will sent an activation code to your email")?></p>
        </div>
      </div>   
      
      <div class="row top10">
        <div class="col-md-3"><?php echo t("Country")?></div>
        <div class="col-md-8">
      <?php echo CHtml::dropDownList('country_code',
      getOptionA('merchant_default_country'),
      (array)Yii::app()->functions->CountryListMerchant(),          
      array(
      'class'=>'grey-fields full-width',
      'data-validation'=>"required"
      ))?>

        </div>
      </div>
      
      <div class="row top10">
        <div class="col-md-3"><?php echo t("State/Region")?></div>
        <div class="col-md-8">
        <?php echo CHtml::dropDownList('state', isset($data['state'])?$data['state']:"", array('San José' => 'San José', 'Alajuela' => 'Alajuela', 'Heredia' => 'Heredia', 'Cartago' => 'Cartago', 'Puntarenas' => 'Puntarenas', 'Guanacaste' => 'Guanacaste', 'Limón' => 'Limón'), array('empty' => '(Seleccioná tu provincia)','class'=>'grey-fields full-width','data-validation'=>"required"))?>
       
        </div>
      </div>
      
      <div class="row top10">
        <div class="col-md-3"><?php echo t("City")?></div>
        <div class="col-md-8">
      <?php echo CHtml::textField('city',
      isset($data['city'])?$data['city']:""
      ,array(
      'class'=>'grey-fields full-width',
      'data-validation'=>"required"
      ))?>           
        </div>
      </div>

      <div class="row top10">
        <div class="col-md-3"><?php echo t("Street address")?></div>
        <div class="col-md-8">
		  <?php echo CHtml::textField('street',
		  isset($data['street'])?$data['street']:""
		  ,array(
		  'class'=>'grey-fields full-width',
		  'data-validation'=>"required"
		  ))?>           
        </div>
      </div>
      
      <?php echo CHtml::hiddenField('post_code' , '0000', array('id' => 'hiddenInput'))?>  
      
      <div class="row top10">
        <div class="col-md-3"><?php echo t("Cuisine")?></div>
        <div class="col-md-8">
		  <?php echo CHtml::dropDownList('cuisine[]',
		  isset($data['cuisine'])?(array)json_decode($data['cuisine']):"",
		  (array)Yii::app()->functions->Cuisine(true),          
		  array(
		  'class'=>'full-width chosen',
		  'multiple'=>true,
		  'data-validation'=>"required"  
		  ))?>           
        </div>
      </div>
      
      <div class="row top10">
        <div class="col-md-3"><?php echo t("Services Pick Up or Delivery?")?></div>
        <div class="col-md-8">
		  <?php echo CHtml::dropDownList('service',
		  isset($data['service'])?$data['service']:"",
		  (array)Yii::app()->functions->Services(),          
		  array(
		  'class'=>'grey-fields full-width',
		  'data-validation'=>"required"
		  ))?>           
        </div>
      </div>
      
      <div class="top15">
      <?php FunctionsV3::sectionHeader('Login Information');?>
      </div>
      
      <div class="row top10">
        <div class="col-md-3"><?php echo t("Username")?></div>
        <div class="col-md-8">
		<?php echo CHtml::textField('username',
		  ''
		  ,array(
		  'class'=>'grey-fields full-width',
		  'data-validation'=>"required"
		  ))?>
        </div>
      </div>
      
      <div class="row top10">
        <div class="col-md-3"><?php echo t("Password")?></div>
        <div class="col-md-8">
		  <?php echo CHtml::passwordField('password',
		  ''
		  ,array(
		  'class'=>'grey-fields full-width',
		  'data-validation'=>"required"
		  ))?>           
        </div>
      </div>
      
      <div class="row top10">
        <div class="col-md-3"><?php echo t("Confirm Password")?></div>
        <div class="col-md-8">
		  <?php echo CHtml::passwordField('cpassword',
		  ''
		  ,array(
		  'class'=>'grey-fields full-width',
		  'data-validation'=>"required"
		  ))?>           
        </div>
      </div>
      
      <?php if ( $terms_merchant=="yes"):?>
      <?php $terms_link=Yii::app()->functions->prettyLink($terms_merchant_url);?>
      <div class="row top10">
        <div class="col-md-3"></div>
        <div class="col-md-8">
          <?php 
		  echo CHtml::checkBox('terms_n_condition',false,array(
		   'value'=>2,
		   'class'=>"",
		   'data-validation'=>"required"
		  ));
		  echo " ". t("I Agree To")." <a href=\"$terms_link\" target=\"_blank\">".t("The Terms & Conditions")."</a>";
		  ?>  
        </div>
      </div>
      <?php endif;?>
      
     
      <?php if ($kapcha_enabled==2):?>      
      <div class="top10 capcha-wrapper">        
        <div id="kapcha-1"></div>
      </div>
      <?php endif;?>
      
      <div class="row top10">
        <div class="col-md-3"></div>
        <div class="col-md-8">
          <input type="submit" value="<?php echo t("Next")?>" class="orange-button inline medium">
        </div>
      </div>
      
      </form>
                   
    </div> <!--box-grey-->
    
   </div> <!--col-->
    
   </div> <!--row--> 
  </div> <!--container-->  
</div> <!--sections-->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script>
$(document).ready(function(){
   $("#food_handling_card_pic_row").css("display","none");
   $("#message").css("display","none");
   $( "#food_handling_card" ).change(function(){
     var checkTxt=$("#food_handling_card" ).val();
     if(checkTxt!='')
     {
       if(checkTxt=='Al día')
       {
         $("input[type=file]").prop('required',true);
         $( "#food_handling_card_pic_row" ).css("display","block");
         $("#message").css("display","none");
       }
       else
       {
         $("input[type=file]").prop('required',false);
         $( "#food_handling_card_pic_row" ).css("display","none");
         $("#message").css("display","none");
         $( "#hdn_photo" ).val('');
         rm_preview();
       }
     }
     else{
         $("input[type=file]").prop('required',false);
         $( "#food_handling_card_pic_row" ).css("display","none");
         if(checkTxt!='')
         $("#message").css("display","none");
         $( "#hdn_photo" ).val('');
         rm_preview();
     }
   });

});
</script>