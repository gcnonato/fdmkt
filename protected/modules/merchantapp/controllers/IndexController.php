<?php
if (!isset($_SESSION)) { session_start(); }

class IndexController extends CController
{
	public $layout='layout';	
	
	public function beforeAction(CAction $action)
	{		
		if (Yii::app()->controller->module->require_login){
			if(!Yii::app()->functions->isAdminLogin()){
			   $this->redirect(Yii::app()->createUrl('/admin/noaccess'));
			   Yii::app()->end();		
			}
		}
		return true;
	}
	
	public function actionIndex(){		
		$lang_params='';
        if(isset($_COOKIE['kr_admin_lang_id'])){	
           if($_COOKIE['kr_admin_lang_id']!="-9999"){
	         $lang_params="/?lang_id=".$_COOKIE['kr_admin_lang_id'];
           }
        }
		$this->redirect(Yii::app()->createUrl('/merchantapp/index/settings'.$lang_params));
	}		
	
	public function actionSettings()
	{
		$this->pageTitle = merchantApp::moduleName()." - ".Yii::t("default","Settings");
		$this->render('settings');
	}
	
	public function actiontranslation()
	{
		$this->render('translation',array(		  
		));
	}
	
	public function actionRegisteredDevice()
	{
		$this->render('registered-device',array(		  
		));
	}

	public function actionCronJobs()
	{
		$this->render('cron-jobs',array(		  
		));
	}

	public function actionPushLogs()
	{
		$this->render('push-logs',array(		  
		));
	}
	
	public function actionPush()
	{
		if ( $res=merchantApp::getDeviceByID($_GET['id'])){
	    $this->render('push_form',array(		  
	      'data'=>$res
		));
		} else $this->render('error',array(
		  'msg'=>t("cannot find records")
		));
	}
	
} /*end*/