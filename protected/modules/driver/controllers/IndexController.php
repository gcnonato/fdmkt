<?php
if (!isset($_SESSION)) { session_start(); }

class IndexController extends CController
{
	public $layout='layout';	
	public $body_class='';
	
	public function init()
	{			
		 // set website timezone
		 $website_timezone=Yii::app()->functions->getOptionAdmin("website_timezone");		 		 
		 if (!empty($website_timezone)){		 	
		 	Yii::app()->timeZone=$website_timezone;
		 }		 				 
	}
	
	public function beforeAction($action)
	{		
		/*if (Yii::app()->controller->module->require_login){
			if(! DriverModule::islogin() ){
			   $this->redirect(Yii::app()->createUrl('/admin/noaccess'));
			   Yii::app()->end();		
			}
		}*/
		$action_name= $action->id ;
		$accept_controller=array('login','ajax');
		if(!Driver::islogin()){			
			if(!in_array($action_name,$accept_controller)){
				$this->redirect(Yii::app()->createUrl('/driver/index/login'));
			}
		}
		
		$cs = Yii::app()->getClientScript();
		$jslang=json_encode(Driver::jsLang());
		$cs->registerScript(
		  'jslang',
		 "var jslang=$jslang",
		  CClientScript::POS_HEAD
		);
				
		
		$js_lang_validator=Yii::app()->functions->jsLanguageValidator();
		$js_lang=Yii::app()->functions->jsLanguageAdmin();
		$cs->registerScript(
		  'jsLanguageValidator',
		  'var jsLanguageValidator = '.json_encode($js_lang_validator).'
		  ',
		  CClientScript::POS_HEAD
		);				
		$cs->registerScript(
		  'js_lang',
		  'var js_lang = '.json_encode($js_lang).'
		  ',
		  CClientScript::POS_HEAD
		);
				
		$website_title=getOptionA('website_title');
		$website_title_1=getOptionA('driver_website_title');
		if(!empty($website_title)){
		   $this->setPageTitle("$website_title -" .ucfirst($action->getId()));
		}
		if(!empty($website_title_1)){
		   $this->setPageTitle("$website_title_1 -" .ucfirst($action->getId()));
		}
		
		// 
		$driver_enabled_auto_assign=getOptionA('driver_enabled_auto_assign');
		if($driver_enabled_auto_assign>0){
			$cs->registerScript(
			  'driver_enabled_auto_assign',
			 "var driver_enabled_auto_assign=$driver_enabled_auto_assign",
			  CClientScript::POS_HEAD
			);
		}
		
		return true;				
	}
	
	public function actionLogin()
	{
		$this->body_class='login-body';
		$this->render('login');
	}
	
	public function actionLogout()
	{
		unset($_SESSION['driver']);
		$this->redirect(Yii::app()->createUrl('/driver/index/login'));
	}
	
	public function actionIndex(){
		$this->body_class="dashboard";		
		$this->render('dashboard');
	}	

	public function actionAgents()
	{
		$this->render('agents-list');
	}
	
	public function actionTasks()
	{
		$this->render('task-list');
	}
	
	public function actionSettings()
	{		
		
		if(!$order_status_list=Yii::app()->functions->orderStatusList()){           
			
        }   
        $country_list=require_once('CountryCode.php');
                
        if(is_array($order_status_list) && count($order_status_list)>=1){
        	foreach ($order_status_list as $key=>$val) {        		
        		$order_status_list[$key]=t($val);
        	}
        }
                     
        if ( Driver::getUserType()=="merchant"){
        	$this->render('error',array(
        	  'msg'=>Driver::t("Sorry but you don't have access to this page")
        	));
        } else {
			$this->render('settings',array(
			  'order_status_list'=>$order_status_list,
			  'country_list'=>$country_list,
			  'ios_push_dev_cer'=>getOptionA('driver_ios_push_dev_cer'),
			  'ios_push_prod_cer'=>getOptionA('driver_ios_push_prod_cer'),
			));
        }
	}
	
	public function actionTeams()
	{
		$this->render('teams');
	}
	
	public function actionlanguage()
	{
		$lang=Driver::availableLanguages();
		$dictionary=require_once('MobileTranslation.php');		
		
		$mobile_dictionary=getOptionA('driver_mobile_dictionary');
        if (!empty($mobile_dictionary)){
	       $mobile_dictionary=json_decode($mobile_dictionary,true);
        } else $mobile_dictionary=false;
		
		$this->render('language',array(
		  'lang'=>$lang,
		  'dictionary'=>$dictionary,
		  'mobile_dictionary'=>$mobile_dictionary
		));
	}
	
	public function actionNotifications()
	{
		$this->render('notifications');
	}
	
	public function actionPushlogs()
	{
		$this->render('push-logs');
	}
	
	public function actionReports()
	{
		$cs = Yii::app()->getClientScript(); 
		
		Yii::app()->clientScript->registerScriptFile(
        "//amcharts.com/lib/3/amcharts.js",CClientScript::POS_END);		
        
        Yii::app()->clientScript->registerScriptFile(
        "//amcharts.com/lib/3/serial.js",CClientScript::POS_END);		
        
        Yii::app()->clientScript->registerScriptFile(
        "//amcharts.com/lib/3/themes/light.js",CClientScript::POS_END);		
		
        $team_list=Driver::teamList( Driver::getUserType(),Driver::getUserId());
		if($team_list){
			 $team_list=Driver::toList($team_list,'team_id','team_name',
			   Driver::t("All Team")
			 );
		}
		
		$all_driver=Driver::getAllDriver(
           Driver::getUserType(),Driver::getUserId()
        );   

        $start= date('Y-m-d', strtotime("-7 day") );
	    $end=date("Y-m-d", strtotime("+1 day")); 
        
		$this->render('reports',array(
		  'team_list'=>$team_list,
		  'all_driver'=>$all_driver,
		  'start_date'=>$start,
		  'end_date'=>$end
		));
	}
	
	public function actionAssignment()
	{
		$this->render('assignment');
	}
		
}/* end class*/