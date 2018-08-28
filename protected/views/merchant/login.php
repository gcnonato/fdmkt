<div class="login_wrap">
<div class="login_logo" style="height:130px;"></div>
<div class="uk-panel uk-panel-box uk-width-medium-1-3">

   <?php $name=Yii::app()->functions->getOptionAdmin('website_title');?>
   
   <h3 class="uk-h3"><?php echo Yii::t("default","Administración de tu Puesto de Comidas")?> </h3>   
     
   <form id="forms" class="uk-form forms" onsubmit="return false;" method="POST">   
   <?php echo CHtml::hiddenField("action",'merchantLogin')?>
   <?php echo CHtml::hiddenField("redirect",Yii::app()->request->baseUrl."/merchant")?>
   
   
   <?php if (isset($_GET['message'])):?>
   <p class="uk-text-danger"><?php echo $_GET['message']?></p>
   <?php endif;?>
   
   <div class="uk-form-row">
      <div class="uk-form-icon uk-width-1">
        <i class="uk-icon-user"></i>
       <?php echo CHtml::textField('username','',array('class'=>"uk-width-1",'placeholder'=>Yii::t("default","Usuario"),
       'data-validation'=>"required"
       ));?>
      </div>
   </div>   
   <div class="uk-form-row">     
       <div class="uk-form-icon uk-width-1">
        <i class="uk-icon-lock"></i>
        <?php echo CHtml::passwordField('password','',array('class'=>"uk-width-1",'placeholder'=>Yii::t("default","Contraseña"),
        'data-validation'=>"required"
        ));?>
       </div>     
   </div>        
   
   <?php if (getOptionA('captcha_merchant_login')==2):?>
   <?php GoogleCaptcha::displayCaptcha()?>
   <?php endif;?>
   
   <div class="uk-form-row">   
   <button class="uk-button uk-width-1"><?php echo Yii::t("default","Iniciar sesión")?> <i class="uk-icon-chevron-circle-right"></i></button>
   </div>
   
   <p><a href="javascript:;" class="mt-fp-link"><?php echo Yii::t("default","Olvidaste tu contraseña")?>?</a></p>
   
   </form>
   
   <form id="mt-frm" class="uk-form mt-frm" onsubmit="return false;" method="POST">   
   <?php echo CHtml::hiddenField("action",'merchantForgotPass')?>
   <h4><?php echo Yii::t("default","Recuperar contraseña")?></h4>
   
   <div class="uk-form-row">
      <div class="uk-form-icon uk-width-1">
        <i class="uk-icon-envelope"></i>
       <?php echo CHtml::textField('email_address','',array('class'=>"uk-width-1",'placeholder'=>Yii::t("default","Correo electrónico"),
       'data-validation'=>"required"
       ));?>
      </div>
   </div>   
      
   <div class="uk-form-row">   
   <button class="uk-button uk-width-1"><?php echo Yii::t("default","Enviar")?> <i class="uk-icon-chevron-circle-right"></i></button>
   </div>
   
   <p><a href="javascript:;" class="mt-login-link"><?php echo Yii::t("default","Volver a inicio de sesión")?></a></p>
   
   </form>
   
   
   <form id="mt-frm-activation" class="uk-form mt-frm-activation" onsubmit="return false;" method="POST">   
   <?php echo CHtml::hiddenField("action",'merchantChangePassword')?>
   <?php echo CHtml::hiddenField("email",'')?>
   <h4><?php echo Yii::t("default","Ingresá el código de verificación & tu nueva contraseña")?></h4>
   
   <div class="uk-form-row">
      <div class="uk-form-icon uk-width-1">
        <i class="uk-icon-unlock"></i>
       <?php echo CHtml::textField('lost_password_code','',array('class'=>"uk-width-1",'placeholder'=>Yii::t("default","Código"),
       'data-validation'=>"required"
       ));?>
      </div>
   </div>   
   
   <div class="uk-form-row">  
      <div class="uk-form-icon uk-width-1">
        <i class="uk-icon-lock"></i>
       <?php echo CHtml::passwordField('new_password','',array('class'=>"uk-width-1",'placeholder'=>Yii::t("default","Nueva contraseña"),
       'data-validation'=>"required"
       ));?>
      </div>
   </div>   
    
   <div class="uk-form-row">   
   <button class="uk-button uk-width-1"><?php echo Yii::t("default","Enviar")?> <i class="uk-icon-chevron-circle-right"></i></button>
   </div>
    
   <p><a href="javascript:;" class="mt-login-link"><?php echo Yii::t("default","Volver a inicio de sesión")?></a></p>
   
   </form>
   
   
</div>
</div> <!--END login_wrap-->