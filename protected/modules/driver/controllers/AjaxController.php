<?php
if (!isset($_SESSION)) { session_start(); }

class AjaxController extends CController
{
	public $code=2;
	public $msg;
	public $details;
	public $data;
	static $db;
	
	public function __construct()
	{
		$this->data=$_POST;	
		self::$db=new DbExt;
	}
	
	public function init()
	{			
		 // set website timezone
		 $website_timezone=Yii::app()->functions->getOptionAdmin("website_timezone");	 		 
		 if (!empty($website_timezone)){		 	
		 	Yii::app()->timeZone=$website_timezone;
		 }		 				 
	}
	
	private function jsonResponse()
	{
		$resp=array('code'=>$this->code,'msg'=>$this->msg,'details'=>$this->details);
		echo CJSON::encode($resp);
		Yii::app()->end();
	}
	
	private function otableNodata()
	{
		if (isset($_GET['sEcho'])){
			$feed_data['sEcho']=$_GET['sEcho'];
		} else $feed_data['sEcho']=1;	   
		     
        $feed_data['iTotalRecords']=0;
        $feed_data['iTotalDisplayRecords']=0;
        $feed_data['aaData']=array();		
        echo json_encode($feed_data);
    	die();
	}

	private function otableOutput($feed_data='')
	{
	  echo json_encode($feed_data);
	  die();
    }    
    
	public function actionLogin()
	{		
		$req=array(
		  'username'=>Driver::t("username is required"),
		  'password'=>Driver::t("password is required"),
		);
		$Validator=new Validator;
		$Validator->required($req,$this->data);
		if($Validator->validate()){
			switch ($this->data['user_type']) {
				case 1:
					//admin
					if($res=Driver::adminLogin($this->data['username'],$this->data['password'])){						
						$this->code=1;
						$_SESSION['driver']['user_type']="admin";
						$_SESSION['driver']['info']=$res;	
						$this->msg=Driver::t("Login ok");
					} else $this->msg=Driver::t("Login failed");
					break;
			
				default:
					//merchant
					if($res=Driver::merchantLogin($this->data['username'],$this->data['password'])){						
						$this->code=1;
						$_SESSION['driver']['user_type']="merchant";
						$_SESSION['driver']['info']=$res;	
						$this->msg=Driver::t("Login ok");
					} else $this->msg=Driver::t("Login failed");
					break;
			}
		} else $this->msg=$Validator->getErrorAsHTML();
		$this->jsonResponse();
	}
	
	public function actionCreateTeam()
	{
		$params=array(
		  'team_name'=>$this->data['team_name'],
		  'location_accuracy'=>$this->data['location_accuracy'],
		  //'team_member'=>isset($this->data['team_member'])?json_encode($this->data['team_member']):'',
		  'status'=>$this->data['status'],
		  'date_created'=>date('Y-m-d H:i:s'),
		  'ip_address'=>$_SERVER['REMOTE_ADDR']
		);		
		if(!isset($this->data['id'])){
			$this->data['id']='';
		}
		
		$team_member=isset($this->data['team_member'])?json_encode($this->data['team_member']):'';
		
		$params['user_type']=Driver::getUserType();
		$params['user_id']=Driver::getUserId();
		
		if(!Driver::islogin()){
			$this->msg=Driver::t("Sorry but your session has expired");
			$this->jsonResponse();
			Yii::app()->end();
		}
						
		$db=new DbExt;
		if(!empty($this->data['id'])){
			unset($params['date_created']);
			$params['date_modified']=date('Y-m-d H:i:s');
			if ( $db->updateData("{{driver_team}}",$params,'team_id',$this->data['id'])){
				$this->code=1;
		   	    $this->msg=Driver::t("Successfully updated");
		   	    $this->details='create-team';
		   	    
		   	    // update driver team
		   	    if(!empty($team_member)){
			       Driver::updateDriverTeam($team_member,$this->data['id']);
		        } else {
		           $sql_update="UPDATE {{driver}} SET team_id='0' WHERE team_id=".Driver::q($this->data['id'])." ";
		           $db->qry($sql_update);
		        }
		   	    
			} else $this->msg=Driver::t("failed cannot update record");
		} else {
		   if($db->insertData("{{driver_team}}",$params)){
		   	  $team_id=Yii::app()->db->getLastInsertID();
		   	  $this->code=1;
		   	  $this->msg=Driver::t("Successful");
		   	  $this->details='create-team';
		   	  
		   	  // update driver team
		   	  if(!empty($team_member)){
			     Driver::updateDriverTeam($team_member,$team_id);
		      }
		   	  
		   } else $this->msg=Driver::t("failed cannot insert record");
		}
		$this->jsonResponse();
	}

	public function actionTeamList()
	{
		$aColumns = array(
		  'a.team_id','a.team_name','a.team_name','a.status','a.date_created'
		);
		$t=AjaxDataTables::AjaxData($aColumns);		
		if (isset($_GET['debug'])){
		    dump($t);
		}
		
		if (is_array($t) && count($t)>=1){
			$sWhere=$t['sWhere'];
			$sOrder=$t['sOrder'];
			$sLimit=$t['sLimit'];
		}	
		
		$and='';				
		if ( Driver::getUserType()=="admin"){			
		   $and =" AND user_type=".Driver::q(Driver::getUserType())."";
		} else {
		   $and =" AND user_type=".Driver::q(Driver::getUserType())."";
		   $and.=" AND user_id=".Driver::q(Driver::getUserId())."  ";		
		}
				
		$stmt="SELECT SQL_CALC_FOUND_ROWS a.*,
			(
			select count(*)
			from
			{{driver}}
			where			
			team_id=a.team_id
			) as total_driver
		FROM
		{{driver_team}} a
		WHERE 1
		$and		
		$sWhere
		$sOrder
		$sLimit
		";
		if (isset($_GET['debug'])){
		   dump($stmt);
		}
				
		$DbExt=new DbExt; 
		if ( $res=$DbExt->rst($stmt)){
			
			$iTotalRecords=0;						
			$stmtc="SELECT FOUND_ROWS() as total_records";
			if ( $resc=$DbExt->rst($stmtc)){									
				$iTotalRecords=$resc[0]['total_records'];
			}
			
			$feed_data['sEcho']=intval($_GET['sEcho']);
			$feed_data['iTotalRecords']=$iTotalRecords;
			$feed_data['iTotalDisplayRecords']=$iTotalRecords;										
			
			foreach ($res as $val) {
				$date_created=Yii::app()->functions->prettyDate($val['date_created'],true);
			    $date_created=Yii::app()->functions->translateDate($date_created);			
			    
			    $id=$val['team_id'];
			    $p="id=$id"."&tbl=driver_team&whereid=team_id";

			    $actions="<div class=\"table-action\">";
			    $actions.="<a data-modal=\".create-team\" data-id=\"$id\" 
			    data-action=\"getTeam\"
			    class=\"table-edit\" href=\"javascript:;\">".Driver::t("Edit")."</a>";    
			    
			    $actions.="&nbsp;|&nbsp;";
			    
			    $actions.="<a data-data=\"$p\" class=\"table-delete\" href=\"javascript:;\">".Driver::t("Delete")."</a>";
			    $actions.="</div>";
			    
			    $feed_data['aaData'][]=array(
			      $val['team_id'],
			      $val['team_name'].$actions,
			      $val['total_driver'],
			      t($val['status']),
			      $date_created,
			    );			    
			}
			if (isset($_GET['debug'])){
			   dump($feed_data);
			}
			$this->otableOutput($feed_data);	
		}
		$this->otableNodata();
	}	
	
	public function actiongetTeam()
	{		
		if($res=Driver::getTeam($this->data['id'])){			
			$this->code=1; 
			$this->msg=Driver::t("Successful");			
			/*if(!empty($res['team_member'])){
				$res['team_member']=json_decode($res['team_member'],true);
			}*/
			//dump($res);
			if ($driver=Driver::getDriverByTeam($res['team_id'])){
				foreach ($driver as $val) {					
					$res['team_member'][]=$val['driver_id'];
				}
			} else $res['team_member']='';
			//dump($res);
			$this->details=$res;
		} else $this->msg=Driver::t("Record not found");
		$this->jsonResponse();
	}
	
	public function actionDeleteRecords()
	{		
		if(isset($this->data['tbl']) && isset($this->data['whereid']) ){
			$wherefield=$this->data['whereid'];
			$tbl=$this->data['tbl'];
			$stmt="
			DELETE FROM
			{{{$tbl}}}
			WHERE
			$wherefield=".Driver::q($this->data['id'])."
			";
			//dump($stmt);
			$DbExt=new DbExt; 
			$DbExt->qry($stmt);
			$this->code=1;
			$this->msg=Driver::t("Successful");
		} else $this->msg=Driver::t("Missing parameters");
		$this->jsonResponse();
	}
	
	public function actiondriverList()
	{
		$aColumns = array(
		  'driver_id','username','first_name','email','phone',
		  'team_id','status'
		);
		$t=AjaxDataTables::AjaxData($aColumns);		
		if (isset($_GET['debug'])){
		    dump($t);
		}
		
		if (is_array($t) && count($t)>=1){
			$sWhere=$t['sWhere'];
			$sOrder=$t['sOrder'];
			$sLimit=$t['sLimit'];
		}	
				
        $and='';		
        if ( Driver::getUserType()=="admin"){
           $and=" AND user_type=".Driver::q(Driver::getUserType())."  ";
        } else {
		   $and=" AND user_type=".Driver::q(Driver::getUserType())."";
		   $and.=" AND user_id=".Driver::q(Driver::getUserId())."  ";		
        }
		
		$stmt="SELECT SQL_CALC_FOUND_ROWS a.*,
		(
		select team_name
		from
		{{driver_team}}
		where
		team_id=a.team_id
		limit 0,1
		) as team_name
		FROM
		{{driver}} a
		WHERE 1		
		$and
		$sWhere
		$sOrder
		$sLimit
		";
		if (isset($_GET['debug'])){
		   dump($stmt);
		}
				
		$DbExt=new DbExt; 
		if ( $res=$DbExt->rst($stmt)){
			
			$iTotalRecords=0;						
			$stmtc="SELECT FOUND_ROWS() as total_records";
			if ( $resc=$DbExt->rst($stmtc)){									
				$iTotalRecords=$resc[0]['total_records'];
			}
			
			$feed_data['sEcho']=intval($_GET['sEcho']);
			$feed_data['iTotalRecords']=$iTotalRecords;
			$feed_data['iTotalDisplayRecords']=$iTotalRecords;										
			
			foreach ($res as $val) {
				$date_created=Yii::app()->functions->prettyDate($val['date_created'],true);
			    $date_created=Yii::app()->functions->translateDate($date_created);			
			    
			    $id=$val['driver_id'];
			    $p="id=$id"."&tbl=driver&whereid=driver_id";

			    $actions="<div class=\"table-action\">";
			    $actions.="<a data-modal=\".new-agent\" data-id=\"$id\" 
			    data-action=\"getDriverInfo\"
			    class=\"table-edit\" href=\"javascript:;\">".Driver::t("Edit")."</a>";    
			    
			    $actions.="&nbsp;|&nbsp;";
			    
			    $actions.="<a data-data=\"$p\" class=\"table-delete\" href=\"javascript:;\">".Driver::t("Delete")."</a>";
			    $actions.="</div>";
			    
			    $actions_2="<a data-id=\"$id\" data-fname=\"".$val['first_name']."\" class=\"send-push btn btn-primary\" href=\"javascript:;\">".Driver::t("Send Push")."</a>";
			    
			    $feed_data['aaData'][]=array(
			      $val['driver_id'],
			      $val['username'].$actions,
			      $val['first_name'],
			      $val['email'],
			      $val['phone'],
			      $val['team_name'],
			      $val['device_platform']."<br><span class=\"concat-text\">".$val['device_id']."</span>",
			      $date_created."<br>".t($val['status']),
			      $actions_2
			    );			    
			}
			if (isset($_GET['debug'])){
			   dump($feed_data);
			}
			$this->otableOutput($feed_data);	
		}
		$this->otableNodata();
	}
	
	public function actionaddAgent()
	{
		$DbExt=new DbExt; 	
		$params=array(		  
		  'first_name'=>isset($this->data['first_name'])?$this->data['first_name']:'',
		  'last_name'=>isset($this->data['last_name'])?$this->data['last_name']:'',
		  'email'=>isset($this->data['email'])?$this->data['email']:'',
		  'phone'=>isset($this->data['phone'])?$this->data['phone']:'',
		  'username'=>isset($this->data['username'])?$this->data['username']:'',
		  'password'=>isset($this->data['password'])?md5($this->data['password']):'',
		  'team_id'=>isset($this->data['team_id_driver_new'])?$this->data['team_id_driver_new']:'',
		  'transport_type_id'=>isset($this->data['transport_type_id'])?$this->data['transport_type_id']:'',
		  'transport_description'=>isset($this->data['transport_description'])?$this->data['transport_description']:'',
		  'licence_plate'=>isset($this->data['licence_plate'])?$this->data['licence_plate']:'',
		  'color'=>isset($this->data['color'])?$this->data['color']:'',
		  'status'=>isset($this->data['status'])?$this->data['status']:'',
		  'date_created'=>date('Y-m-d H:i:s'),
		  'ip_address'=>$_SERVER['REMOTE_ADDR']
		);		
		
		$params['user_type']=Driver::getUserType();
		$params['user_id']=Driver::getUserId();
		
		if(!isset($this->data['id'])){
			$this->data['id']='';
		}
		
		if(is_numeric($this->data['id'])){
			unset($params['date_created']);
			$params['date_modified']=date('Y-m-d H:i:s');
			
			if(empty($this->data['password'])){
			   unset($params['password']);
			}
			
			/*dump($params);
			die();
			*/
			if ( $DbExt->updateData("{{driver}}",$params,'driver_id',$this->data['id'])){
				$this->code=1;
			    $this->msg=Driver::t("Successfully updated");
			    $this->details='new-agent';
			    
			    /*update team*/
			    //Driver::updateTeamDriver($this->data['id'],$params['team_id']);
			    
			} else $this->msg=Driver::t("failed cannot update record");
		} else {
			if ( $DbExt->insertData('{{driver}}',$params)){
				$this->code=1;
				$this->msg=Driver::t("Successful");
				$this->details='new-agent';
			} else $this->msg=Driver::t("failed cannot insert record");
		}
		$this->jsonResponse();
	}
	
	public function actiongetDriverInfo()
	{		
		if(isset($this->data['id'])){
			if ( $res=Driver::driverInfo($this->data['id'])){
				 $this->code=1;
				 $this->msg=Driver::t("Successful");
				 $this->details=$res;
			} else $this->msg=Driver::t("Record not found");
		} else $this->msg=Driver::t("Missing parameters");
		$this->jsonResponse();
	}
	
	public function actionAddTask()
	{
				
		//dump($this->data);
		
		$DbExt=new DbExt; 		
		$req=array(
		  'trans_type'=>Driver::t("Transaction type is required"),
		  'customer_name'=>Driver::t("Customer name is required")
		);
				
		$Validator=new Validator;
		$Validator->required($req,$this->data);
		if($Validator->validate()){
			
			$params=array(
			  'task_description'=>isset($this->data['task_description'])?$this->data['task_description']:'',
			  'trans_type'=>isset($this->data['trans_type'])?$this->data['trans_type']:'',
			  'contact_number'=>isset($this->data['contact_number'])?$this->data['contact_number']:'',
			  'email_address'=>isset($this->data['email_address'])?$this->data['email_address']:'',
			  'customer_name'=>isset($this->data['customer_name'])?$this->data['customer_name']:'',
			  'delivery_date'=>isset($this->data['delivery_date'])?$this->data['delivery_date']:'',
			  'delivery_address'=>isset($this->data['delivery_address'])?$this->data['delivery_address']:'',
			  'team_id'=>isset($this->data['team_id'])?$this->data['team_id']:'',
			  'driver_id'=>isset($this->data['driver_id'])?$this->data['driver_id']:'',
			  'task_lat'=>isset($this->data['task_lat'])?$this->data['task_lat']:'',
			  'task_lng'=>isset($this->data['task_lng'])?$this->data['task_lng']:'',
			  'date_created'=>date('Y-m-d H:i:s'),
			  'ip_address'=>$_SERVER['REMOTE_ADDR'],
			  'user_type'=>Driver::getUserType(),
			  'user_id'=>Driver::getUserId()
			);			
						
			if(!empty($params['delivery_date'])){
				$params['delivery_date']= date("Y-m-d G:i",strtotime($params['delivery_date']));
			}
			if($params['driver_id']>0){
				$params['status']='assigned';
			}
			/*dump($params);
			die();*/
			if(is_numeric($this->data['task_id'])){
				
				unset($params['date_created']);
				unset($params['user_type']);
				unset($params['user_id']);
				$params['date_modified']=date('Y-m-d H:i:s');
				
				$task_info=Driver::getTaskId($this->data['task_id']);
				if( $task_info['status']!="unassigned"){
					unset($params['status']);
				}
								
				if ( $DbExt->updateData("{{driver_task}}",$params,'task_id',$this->data['task_id'])){
					$this->code=1;
					$this->msg=Driver::t("Successfully updated");
										
					if (isset($params['status'])){
						if ($params['status']=="assigned"){
							/*add to history*/
							$assigned_task=$params['status'];
							//if ( $res=Driver::getTaskId($this->data['task_id'])){
							if($task_info){
								$status_pretty = Driver::prettyStatus($task_info['status'],$assigned_task);
								$params_history=array(
								  'order_id'=>isset($task_info['order_id'])?$task_info['order_id']:'',
								  'remarks'=>$status_pretty,
								  'status'=>$assigned_task,
								  'date_created'=>date('Y-m-d H:i:s'),
								  'ip_address'=>$_SERVER['REMOTE_ADDR'],
								  'task_id'=>$this->data['task_id']
								);		
								$DbExt->insertData('{{order_history}}',$params_history);	
								
								// send notification to driver								
							    Driver::sendDriverNotification('ASSIGN_TASK',$res);
							    
							}				
						} 
					} else {						
				        Driver::sendDriverNotification('UPDATE_TASK',$task_info);
					}
					
				} else $this->msg=Driver::t("failed cannot update record");
			} else {
				if($DbExt->insertData("{{driver_task}}",$params)){
					$task_id=Yii::app()->db->getLastInsertID();
					$this->code=1;
					$this->msg=Driver::t("Successful");
					
					// send notification to driver
					if ( $info=Driver::getTaskId($task_id)){				
				       Driver::sendDriverNotification('ASSIGN_TASK',$info);
			        }			
					
				} else $this->msg=Driver::t("failed cannot insert record");
			}
		} else $this->msg=$Validator->getErrorAsHTML();
		$this->jsonResponse();
	}
	
	public function actiongetDashboardTask()
	{
		if (isset($this->data['status'])){
			//$status=$this->data['status'];
			$date='';
			if ( isset($this->data['date'])){
				$date=$this->data['date'];
			}
			
			$data=''; $coordinates='';
			$status_list=array('unassigned','assigned','completed');
			foreach ($status_list as $status) {
				if ( $res = Driver::getTaskByStatus($this->userType(),$this->userId(),$status,$date)){
					$total=count($res);
					$html='';
					foreach ($res as $val) {			
						//dump($val);		
						if(!empty($val['task_lat']) && !empty($val['task_lng']) ){
							$coordinates[]=array(
							  'lat'=>$val['task_lat'],
							  'lng'=>$val['task_lng'],
							  'trans_type'=>$val['trans_type'],		
							  'customer_name'=>$val['customer_name'],
							  'address'=>$val['delivery_address'],
							  'task_id'=>$val['task_id'],
							  'status_raw'=>$val['status'],
							  'status'=>Driver::t($val['status']),							  
							  'trans_type'=>Driver::t($val['trans_type']),
							  'map_type'=>'restaurant'
							);
						} else {
							if ( $res_location=Driver::addressToLatLong($val['delivery_address'])){
								//dump($res_location);
								$val['task_lat']=$res_location['lat'];
								$val['task_lng']=$res_location['long'];
								
								$coordinates[]=array(
							      'lat'=>$res_location['lat'],
							      'lng'=>$res_location['long'],
							      'trans_type'=>$val['trans_type'],
							      'customer_name'=>$val['customer_name'],
							      'address'=>$val['delivery_address'],
							      'task_id'=>$val['task_id'],
							      'status'=>Driver::t($val['status']),
							      'trans_type'=>Driver::t($val['trans_type']),
							      'map_type'=>'restaurant'
							    );
							}
						}
						$html.=Driver::formatTask($val);
					}
					
										
					$data[$status]=array(
					  'total'=>$total,
					  'html'=>$html					  
					);								
					$this->details=$data;
				} else {
					$data[$status]='';
					$this->details=$data;
				}
			}
			
			/*get the driver online coordinates*/
			$agent_stats=array('active');			
			$include_offline=getOptionA('driver_include_offline_driver_map');
			if($include_offline==1){
			   $agent_stats=array('active','offline');
			}
			//dump($agent_stats);
						
			$online_agent='';
			foreach ($agent_stats as $agent_stat) {
				$res_agent=Driver::getDriverByStats(
				  Driver::getUserType(),
				  Driver::getUserId(),
				  $agent_stat,
				  isset($this->data['date'])?$this->data['date']:date("Y-m-d"),
				  'active'
				);
				//dump($res_agent);
				if (is_array($res_agent) && count($res_agent)>=1){
				   foreach ($res_agent as $agent_val) {
				   	  $coordinates[]=array(
					   'driver_id'=>$agent_val['driver_id'],
					   'first_name'=>$agent_val['first_name'],
					   'last_name'=>$agent_val['last_name'],
					   'email'=>$agent_val['email'],
					   'phone'=>$agent_val['phone'],
					   'lat'=>$agent_val['location_lat'],
					   'lng'=>$agent_val['location_lng'],
					   'map_type'=>'driver',
					   'is_online'=>$agent_val['is_online']
					  );
				   }
				}
			}
			
		    //dump($coordinates);
			
			$this->code=1;	
			$this->msg=$coordinates;
			
		} else $this->msg=Driver::t("parameter status is missing");
		$this->jsonResponse();
	}
	
	private function userType()
	{
		return Driver::getUserType();
	}
	
	private function userId()
	{
		return Driver::getUserId();
	}
	
	public function actionassignTask()
	{
		$DbExt=new DbExt; 		
		$req=array(
		  'task_id'=>Driver::t("Task id is required"),
		  'team_id'=>Driver::t("Team id is required"),
		  'driver_id'=>Driver::t("Driver id is required"),
		);
		
		$assigned_task='assigned';
				
		
		$Validator=new Validator;
		$Validator->required($req,$this->data);
		if($Validator->validate()){
			$params=array(
			  'team_id'=>$this->data['team_id'],
			  'driver_id'=>$this->data['driver_id'],
			  'status'=>$assigned_task,
			  'date_modified'=>date('Y-m-d H:i:s'),
			  'ip_address'=>$_SERVER['REMOTE_ADDR']
			);
			if ( $DbExt->updateData("{{driver_task}}",$params,'task_id',$this->data['task_id'])){
				$this->code=1;
				$this->msg=Driver::t("Successfully updated");
				$this->details='assign-task';
				
				/*add to history*/
				if ( $res=Driver::getTaskId($this->data['task_id'])){
					$status_pretty = Driver::prettyStatus($res['status'],$assigned_task);
					
					$remarks_args=array(
					  '{from}'=>$res['status'],
					  '{to}'=>$assigned_task
					);
					$params_history=array(
					  'order_id'=>$res['order_id'],
					  'remarks'=>$status_pretty,
					  'status'=>$assigned_task,
					  'date_created'=>date('Y-m-d H:i:s'),
					  'ip_address'=>$_SERVER['REMOTE_ADDR'],
					  'task_id'=>$this->data['task_id'],
					  'remarks2'=>"Status updated from {from} to {to}",
					  'remarks_args'=>json_encode($remarks_args)
					);		
					$DbExt->insertData('{{order_history}}',$params_history);
				}				
				
				/*send notification to driver*/
		         Driver::sendDriverNotification('ASSIGN_TASK',$res=Driver::getTaskId($this->data['task_id']));
				
			} else $this->msg=Driver::t("failed cannot update record");
		} else $this->msg=$Validator->getErrorAsHTML();
		$this->jsonResponse();
	}
	
	public function actionGetTaskDetails()
	{		
		
		if (isset($this->data['id'])){
			if ( $res=Driver::getTaskId($this->data['id'])){
				$res['status_raw']=!empty($res['status'])?$res['status']:'';
				$res['status']=!empty($res['status'])?Driver::t($res['status']):'';				
				$res['driver_name']=!empty($res['driver_name'])?$res['driver_name']:'';
				$res['team_name']=!empty($res['team_name'])?$res['team_name']:'';
				$res['customer_name']=!empty($res['customer_name'])?$res['customer_name']:'';
				$res['contact_number']=!empty($res['contact_number'])?$res['contact_number']:'';
				$res['email_address']=!empty($res['email_address'])?$res['email_address']:'';
				$res['delivery_date']=!empty($res['delivery_date'])?date("Y-m-d g:i a",strtotime($res['delivery_date'])):'-';
				$res['trans_type_raw']=$res['trans_type'];
				$res['trans_type']=!empty($res['trans_type'])?t($res['trans_type']):'';				
																		
				/*get task history*/				
				$history_details=''; $history_data='';
				//if ( $info=Driver::getTaskId($this->data['id'])){								
				if($info=$res){
					if($history_details = Driver::getTaskHistory($this->data['id'],$info['order_id'])){
						foreach ($history_details as $valh) {				
														
							$valh['status_raw']=$valh['status'];
							$valh['status']=Driver::t($valh['status']);
							
							if(!empty($valh['remarks2'])){							
								$args=json_decode($valh['remarks_args'],true);								
								if(is_array($args) && count($args)>=1){
									foreach ($args as $args_key=>$args_val) {
										$args[$args_key]=t($args_val);
									}
								}								
								$new_remarks=$valh['remarks2'];								
								$new_remarks=Yii::t("default",$new_remarks,$args);								
								$valh['remarks']=$new_remarks;
							}
							
							$valh['date_created']=Yii::app()->functions->FormatDateTime($valh['date_created']);
							
							if (!empty($valh['customer_signature'])){
					            $valh['customer_signature_url']=Driver::uploadURL()."/".$valh['customer_signature'];
					            if (!file_exists(Driver::uploadPath()."/".$valh['customer_signature'])){
    					            $valh['customer_signature_url']='';
    				            }
				            }
							$history_data[]=$valh;
						}
					} else {
						$history_data='';
					}
				}
												
				$res['history_data']=$history_data;
				
				// get the order details
				$order_details='';  $order_details_head='';
				if($res['order_id']>0){
					$order_id=$res['order_id'];					
					$_GET['backend']='true';
					if ( $data=Yii::app()->functions->getOrder2($order_id)){						
						$json_details=!empty($data['json_details'])?json_decode($data['json_details'],true):false;
						if ( $json_details !=false){
						    Yii::app()->functions->displayOrderHTML(array(
						       'merchant_id'=>$data['merchant_id'],
						       'order_id'=>$order_id,
						       'delivery_type'=>$data['trans_type'],
						       'delivery_charge'=>$data['delivery_charge'],
						       'packaging'=>$data['packaging'],
						       'cart_tip_value'=>$data['cart_tip_value'],
							   'cart_tip_percentage'=>$data['cart_tip_percentage'],
							   'card_fee'=>$data['card_fee'],
							   'donot_apply_tax_delivery'=>$data['donot_apply_tax_delivery'],
							   'points_discount'=>isset($data['points_discount'])?$data['points_discount']:'' /*POINTS PROGRAM*/
						     ),$json_details,true , $order_id);	
						     $data2=Yii::app()->functions->details;
						     $order_details=$data2['html'];
						     
						     $merchant_info=Yii::app()->functions->getMerchant($data['merchant_id']);
                             $full_merchant_address=$merchant_info['street']." ".$merchant_info['city']. " ".$merchant_info['state']." ".$merchant_info['post_code'];
						     
						     $order_details_head.="<table class=\"table table-striped\">";
						      $order_details_head.="<tbody>";
						      $order_details_head.=Driver::receiptRow("Customer Name",$data['full_name']);
						      $order_details_head.=Driver::receiptRow("Merchant Name",$data['merchant_name']);
						      $order_details_head.=Driver::receiptRow("Telephone",$data['merchant_contact_phone']);
						      $order_details_head.=Driver::receiptRow("Address",$full_merchant_address);
						      $order_details_head.=Driver::receiptRow("TRN Type",$data['trans_type']);
						      $order_details_head.=Driver::receiptRow("Payment Type",strtoupper(t($data['payment_type'])));
						      if ( $data['payment_provider_name']){
						        $order_details_head.=Driver::receiptRow("Card#",$data['payment_provider_name']);
						      }
						      if ( $data['payment_type'] =="pyp"){
						      	$paypal_info=Yii::app()->functions->getPaypalOrderPayment($data['order_id']);	       
						      	$order_details_head.=Driver::receiptRow("Paypal Transaction ID",
						      	isset($paypal_info['TRANSACTIONID'])?$paypal_info['TRANSACTIONID']:'');
						      }
						      
						      $order_details_head.=Driver::receiptRow("Reference #",Yii::app()->functions->formatOrderNumber($data['order_id']));
						      if ( !empty($data['payment_reference'])){
						         $order_details_head.=Driver::receiptRow("Payment Ref",$data['payment_reference']);
						      }
						      
						      if ( $data['payment_type']=="ccr" || $data['payment_type']=="ocr"){
						      	  $order_details_head.=Driver::receiptRow("Card #",
						      	  Yii::app()->functions->maskCardnumber($data['credit_card_number'])
						      	  );
						      }
						      
						      $trn_date=date('M d,Y G:i:s',strtotime($data['date_created']));	                          
						      $order_details_head.=Driver::receiptRow("TRN Date",
						      Yii::app()->functions->translateDate($trn_date));
						      
						      						      
						      if (isset($data['delivery_date'])){
						      	  if(!empty($data['delivery_date'])){
						      	  $delivery_date=prettyDate($data['delivery_date']);
						      	  $delivery_date=Yii::app()->functions->translateDate($delivery_date);
						      	  $order_details_head.=Driver::receiptRow(
						      	  $data['trans_type']=="delivery"?"Delivery Date":"Pickup Date"
						      	  ,$delivery_date);
						      	  }
						      }
						      if (isset($data['delivery_time'])){
						      	  if(!empty($data['delivery_time'])){
						      	  	  $delivery_time=Yii::app()->functions->timeFormat($data['delivery_time'],true);
						      	  	  $order_details_head.=Driver::receiptRow(
						      	        $data['trans_type']=="delivery"?"Delivery Time":"Pickup Time"
						      	      ,$delivery_date);
						      	  }
						      }
						      
						      if(isset($data['delivery_asap'])){
						      	 if(!empty($data['delivery_asap'])){
						      	 	 $order_details_head.=Driver::receiptRow("Deliver ASAP",
						      	 	 $data['delivery_asap']==1?Driver::t("Yes"):""
						      	 	 );
						      	 }
						      }
						      
						      if (!empty($data['client_full_address'])){
		         	             $delivery_address=$data['client_full_address'];
		                      } else $delivery_address=$data['full_address'];	
						      
		                      $order_details_head.=Driver::receiptRow("Deliver to",$delivery_address);
		                      $order_details_head.=Driver::receiptRow("Delivery Instruction",$data['delivery_instruction']);
		                      
		                      if (!empty($data['location_name1'])){
		                      	 $location_name=$data['location_name1'];
		                      } else $location_name=$data['location_name'];
		                      
		                      $order_details_head.=Driver::receiptRow("Location Name",$location_name);
		                      $order_details_head.=Driver::receiptRow("Contact Number",
		                        !empty($data['contact_phone1'])?$data['contact_phone1']:$data['contact_phone']
		                      );
		                      
		                      if($data['order_change']>0.1){
		                      	 $order_details_head.=Driver::receiptRow("Change", 
		                      	  displayPrice( baseCurrency(), normalPrettyPrice($data['order_change']))
		                      	 );
		                      }
		                      
						      $order_details_head.="</tbody>";
						     $order_details_head.="</table>";
						     
						}
					} 
				}
								
				$res['order_details']=$order_details_head.$order_details;
				if(isset($res['merchant_name'])){
				   $res['merchant_name']=Driver::cleanText($res['merchant_name']);
				}
				///dump($res);
				
				$this->code=1;
				$this->msg="OK";
				$this->details=$res;
				//dump($this->details);
				
			} else $this->msg=Driver::t("Cannot find records");
		} else $this->msg=Driver::t("missing parameter id");
		$this->jsonResponse();
	}
	
	public function actiongetTaskInfo()
	{
		$this->actionGetTaskDetails();
	}
	
	public function actiondeleteTask()
	{		
		if(isset($this->data['task_id'])){		

			$task_id=$this->data['task_id'];			
			if ( $res2 = Driver::getUnAssignedDriver3($task_id)){				    		
				foreach ($res2 as $val2) {	  
	    		   $task_info=Driver::getTaskByDriverNTask($val2['task_id'], $val2['driver_id'] );
	    		   Driver::sendDriverNotification('CANCEL_TASK',$task_info);
	    		}
			} else {			
				if ( $info=Driver::getTaskId($this->data['task_id'])){				
					Driver::sendDriverNotification('CANCEL_TASK',$info);
				}		
			}	
			if( Driver::deleteTask($this->data['task_id'])){
				$this->code=1;
				$this->msg="OK";
			} else $this->msg=Driver::t("Failed deleting records");
		} else $this->msg=Driver::t("missing parameter id");
		$this->jsonResponse();
	}
	
	public function actionchangeStatus()
	{
		$req=array(
		  'task_id'=>Driver::t("Task ID is required"),
		  'status'=>Driver::t("Status is required"),
		);
		$Validator=new Validator;
		$Validator->required($req,$this->data);
		if($Validator->validate()){
			if ( $res=Driver::getTaskId($this->data['task_id'])){				
				$status_pretty = Driver::prettyStatus($res['status'],$this->data['status']);
				$params=array(
				  'order_id'=>$res['order_id'],
				  'remarks'=>$status_pretty,
				  'status'=>$this->data['status'],
				  'date_created'=>date('Y-m-d H:i:s'),
				  'ip_address'=>$_SERVER['REMOTE_ADDR'],
				  'task_id'=>$this->data['task_id'],
				  'reason'=>isset($this->data['reason'])?$this->data['reason']:''
				);										
				$DbExt=new DbExt; 
				if ( $DbExt->insertData("{{order_history}}",$params)){
					$this->code=1;
					$this->msg= Driver::t("Task Status Changed Successfully");
					$this->details='task-change-status-modal';
					
					/*update the status*/
					$DbExt->updateData("{{driver_task}}",array(
					 'status'=>$this->data['status']
					),'task_id',$this->data['task_id']);
					
					/*update assigment*/
					$sql_assign="
					UPDATE {{driver_assignment}}
					SET task_status=".Driver::q($this->data['status'])."
					WHERE
					task_id=".Driver::q($this->data['task_id'])."
					";
					$DbExt->qry($sql_assign);
										
					/*send push if status is cancel*/
					$drv_order_cancel=getOptionA('drv_order_cancel');					
					if ( $drv_order_cancel==$this->data['status']){
						Driver::sendDriverNotification('CANCEL_TASK',$res);
					}
					
				} else $this->msg=Driver::t("failed cannot update record");
			} else $this->msg=Driver::t("Task id not found");
		} else $this->msg=$Validator->getErrorAsHTML();
		$this->jsonResponse();
	}
	
	public function actionloadAgentDashboard()
	{		
		$data='';
		$agent_stats=array(
		  'active','offline','total'
		);
		foreach ($agent_stats as $agent_stat) {
			$res=Driver::getDriverByStats(
			  Driver::getUserType(),
			  Driver::getUserId(),
			  $agent_stat,
			  isset($this->data['date'])?$this->data['date']:date("Y-m-d"),
			  'active',
			  isset($this->data['team_id'])?$this->data['team_id']:''
			);
			if($res){
				$data[$agent_stat]=$res;
			} else $data[$agent_stat]='';
		}
		
		//dump($data);
		
		$this->code=1;
		$this->msg="OK";
		$this->details=$data;
		$this->jsonResponse();
	}
	
	public function actiongetDriverDetails()
	{
		if ( isset($this->data['driver_id'])){
			if ( $res= Driver::driverInfo($this->data['driver_id'])){
				$data['driver_id']=$res['driver_id'];
				$data['user_id']=$res['user_id'];
				$data['name']=$res['first_name']." ".$res['last_name'];
				$data['email']=$res['email'];
				$data['phone']=$res['phone'];
				$data['transport_type_id']=$res['transport_type_id'];
				$data['licence_plate']=$res['licence_plate'];
				$data['team_name']=$res['team_name'];
								
				$order_details='';
				
				$transaction_date=isset($this->data['date'])?$this->data['date']:date("Y-m-d");
				if ( !$order=Driver::getTaskByDriverID($this->data['driver_id'],$transaction_date)){
					$order_details='';
				} else {
					foreach ($order as $order_val) {		
						$order_val['status_raw']=$order_val['status'];
						$order_val['status']=Driver::t($order_val['status']);						
						$order_details[]=$order_val;
					}
				}
				
				//dump($order_details);
								
				$this->code=1;
				$this->msg="OK";
				$this->details=array(
				  'info'=>$data,
				  'task'=>$order_details
				);				
				
			} else $this->msg=Driver::t("Driver details not found");
		} else $this->msg=Driver::t("Missing parameters");
		$this->jsonResponse();
	}
	
	public function actiontaskList()
	{
		$aColumns = array(
		  'task_id','order_id','trans_type','task_description',
		  'driver_name','customer_name','delivery_address','delivery_date'
		);
		$t=AjaxDataTables::AjaxData($aColumns);		
		if (isset($_GET['debug'])){
		    dump($t);
		}
		
		if (is_array($t) && count($t)>=1){
			$sWhere=$t['sWhere'];
			$sOrder=$t['sOrder'];
			$sLimit=$t['sLimit'];
		}	
				
        $and='';		
        if ( Driver::getUserType()=="admin"){
           //$and=" AND user_type=".Driver::q(Driver::getUserType())."  ";
        } else {
		   $and=" AND user_type=".Driver::q(Driver::getUserType())."";
		   $and.=" AND user_id=".Driver::q(Driver::getUserId())."  ";		
        }
		
		$stmt="SELECT SQL_CALC_FOUND_ROWS *
		FROM
		{{driver_task_view}}
		WHERE 1		
		$and
		$sWhere
		$sOrder
		$sLimit
		";
		if (isset($_GET['debug'])){
		   dump($stmt);
		}
				
		$DbExt=new DbExt; 
		$DbExt->qry("SET SQL_BIG_SELECTS=1");
		
		if ( $res=$DbExt->rst($stmt)){
			
			$iTotalRecords=0;						
			$stmtc="SELECT FOUND_ROWS() as total_records";
			if ( $resc=$DbExt->rst($stmtc)){									
				$iTotalRecords=$resc[0]['total_records'];
			}
			
			$feed_data['sEcho']=intval($_GET['sEcho']);
			$feed_data['iTotalRecords']=$iTotalRecords;
			$feed_data['iTotalDisplayRecords']=$iTotalRecords;										
			
			foreach ($res as $val) {
				$date_created=Yii::app()->functions->prettyDate($val['date_created'],true);
			    $date_created=Yii::app()->functions->translateDate($date_created);		
			    
			    $status="<span class=\"tag ".$val['status']." \">".Driver::t($val['status'])."</span>";	
			    
			    $action="<a class=\"btn btn-primary task-details\"
			    	data-id=\"".$val['task_id']."\" href=\"javascript:;\">".Driver::t("Details")."</a>";
			    
			    if ( $val['status']=="unassigned"){
			    	$action="<a class=\"btn btn-default assign-agent\"
			    	data-id=\"".$val['task_id']."\" href=\"javascript:;\">".Driver::t("Assigned")."</a>";
			    }
			    
			    $feed_data['aaData'][]=array(
			      $val['task_id'],
			      $val['order_id']>0?$val['order_id']:'',
			      Driver::t($val['trans_type']),
			      $val['task_description'],
			      $val['driver_name'],
			      $val['customer_name'],
			      $val['delivery_address'],
			      $date_created,
			      $status,
			      $action
			    );			    
			}
			if (isset($_GET['debug'])){
			   dump($feed_data);
			}
			$this->otableOutput($feed_data);	
		}
		$this->otableNodata();
	}
	
	public function actiongeneralSettings()
	{		
		Yii::app()->functions->updateOptionAdmin("drv_google_api",
	    isset($this->data['drv_google_api'])?$this->data['drv_google_api']:'');
	    
	    Yii::app()->functions->updateOptionAdmin("drv_default_location",
	    isset($this->data['drv_default_location'])?$this->data['drv_default_location']:'');
	    
	    Yii::app()->functions->updateOptionAdmin("drv_order_status",
	    isset($this->data['drv_order_status'])?$this->data['drv_order_status']:'');
	    
	    Yii::app()->functions->updateOptionAdmin("drv_map_style",
	    isset($this->data['drv_map_style'])?$this->data['drv_map_style']:'');
	    
	    Yii::app()->functions->updateOptionAdmin("drv_delivery_time",
	    isset($this->data['drv_delivery_time'])?$this->data['drv_delivery_time']:'');
	    
	    //dump($this->data);
	    if(!empty($this->data['drv_default_location'])){
	       $country_list=require_once('CountryCode.php');	
	       $country_name='';
	       if(array_key_exists($this->data['drv_default_location'],(array)$country_list)){
	           $country_name=$country_list[$this->data['drv_default_location']];	   
	       } else $country_name=$this->data['drv_default_location'];	       
	       if ( $res=Driver::addressToLatLong($country_name))	{	       	
	       	   Yii::app()->functions->updateOptionAdmin("drv_default_location_lat",$res['lat']); 
	       	   Yii::app()->functions->updateOptionAdmin("drv_default_location_lng",$res['long']); 	       	
	       } 
	    }
	    
	    /*Yii::app()->functions->updateOptionAdmin("ORDER_AUTO_ADD_TASK",
	    isset($this->data['ORDER_AUTO_ADD_TASK'])?$this->data['ORDER_AUTO_ADD_TASK']:'');*/
	    
	    Yii::app()->functions->updateOptionAdmin("driver_api_hash_key",
	    isset($this->data['driver_api_hash_key'])?$this->data['driver_api_hash_key']:'');
	    
	    Yii::app()->functions->updateOptionAdmin("driver_push_api_key",
	    isset($this->data['driver_push_api_key'])?$this->data['driver_push_api_key']:'');
		
	    Yii::app()->functions->updateOptionAdmin("driver_website_title",
	    isset($this->data['driver_website_title'])?$this->data['driver_website_title']:'');
	    
	    Yii::app()->functions->updateOptionAdmin("driver_send_push_to_online",
	    isset($this->data['driver_send_push_to_online'])?$this->data['driver_send_push_to_online']:'');
	    
	    Yii::app()->functions->updateOptionAdmin("driver_owner_task",
	    isset($this->data['driver_owner_task'])?$this->data['driver_owner_task']:'');
	    
	    Yii::app()->functions->updateOptionAdmin("driver_include_offline_driver_map",
	    isset($this->data['driver_include_offline_driver_map'])?$this->data['driver_include_offline_driver_map']:'');
	    
	    Yii::app()->functions->updateOptionAdmin("driver_disabled_auto_refresh",
	    isset($this->data['driver_disabled_auto_refresh'])?$this->data['driver_disabled_auto_refresh']:'');
	    
	    Yii::app()->functions->updateOptionAdmin("drv_order_cancel",
	    isset($this->data['drv_order_cancel'])?$this->data['drv_order_cancel']:'');
	    
	    $this->code=1;
	    $this->msg=Yii::t("default","Setting saved");	
	    $this->jsonResponse();
	}
	
	public function actionSaveTranslation()
	{		
		$mobile_dictionary='';
		if (is_array($this->data) && count($this->data)>=1){
			//$version=str_replace(".",'',phpversion());					
			$mobile_dictionary=json_encode($this->data);			
			$unicode=3;
		}				
		Yii::app()->functions->updateOptionAdmin('driver_mobile_dictionary',$mobile_dictionary);
		$this->code=1;
		$this->msg=Driver::t("translation saved");
		$this->details=$unicode;
		$this->jsonResponse();
	}		
	
	public function actionSaveNotification()
	{		
		$user_type=Driver::getLoginType();
	
		if ( $user_type=="admin"){
			
			$delivery=Driver::notificationListDelivery();
			$key="DELIVERY_";
			foreach ($delivery['DELIVERY'] as $val){
				foreach ($val as $val2) {
					$_key=$key.$val2;					
					Yii::app()->functions->updateOptionAdmin(
					   $_key,isset($this->data[$_key])?$this->data[$_key]:''
					);
				}
			}
			
			$delivery=Driver::notificationListPickup();
			$key="PICKUP_";
			foreach ($delivery['PICKUP'] as $val){
				foreach ($val as $val2) {
					$_key=$key.$val2;					
					Yii::app()->functions->updateOptionAdmin(
					   $_key,isset($this->data[$_key])?$this->data[$_key]:''
					);
				}
			}
			
			Yii::app()->functions->updateOptionAdmin("ASSIGN_TASK_PUSH",
	        isset($this->data['ASSIGN_TASK_PUSH'])?$this->data['ASSIGN_TASK_PUSH']:'');
	        
	        Yii::app()->functions->updateOptionAdmin("ASSIGN_TASK_SMS",
	        isset($this->data['ASSIGN_TASK_SMS'])?$this->data['ASSIGN_TASK_SMS']:'');
	        
	        Yii::app()->functions->updateOptionAdmin("ASSIGN_TASK_EMAIL",
	        isset($this->data['ASSIGN_TASK_EMAIL'])?$this->data['ASSIGN_TASK_EMAIL']:'');
	        
	        Yii::app()->functions->updateOptionAdmin("CANCEL_TASK_PUSH",
	        isset($this->data['CANCEL_TASK_PUSH'])?$this->data['CANCEL_TASK_PUSH']:'');
	        
	        Yii::app()->functions->updateOptionAdmin("CANCEL_TASK_SMS",
	        isset($this->data['CANCEL_TASK_SMS'])?$this->data['CANCEL_TASK_SMS']:'');
	        
	        Yii::app()->functions->updateOptionAdmin("CANCEL_TASK_EMAIL",
	        isset($this->data['CANCEL_TASK_EMAIL'])?$this->data['CANCEL_TASK_EMAIL']:'');
	        
	        Yii::app()->functions->updateOptionAdmin("UPDATE_TASK_PUSH",
	        isset($this->data['UPDATE_TASK_PUSH'])?$this->data['UPDATE_TASK_PUSH']:'');
	        
	        Yii::app()->functions->updateOptionAdmin("UPDATE_TASK_SMS",
	        isset($this->data['UPDATE_TASK_SMS'])?$this->data['UPDATE_TASK_SMS']:'');
	        
	        Yii::app()->functions->updateOptionAdmin("UPDATE_TASK_EMAIL",
	        isset($this->data['UPDATE_TASK_EMAIL'])?$this->data['UPDATE_TASK_EMAIL']:'');
	        
	        Yii::app()->functions->updateOptionAdmin("FAILED_AUTO_ASSIGN_EMAIL",
	        isset($this->data['FAILED_AUTO_ASSIGN_EMAIL'])?$this->data['FAILED_AUTO_ASSIGN_EMAIL']:'');
	        
	        Yii::app()->functions->updateOptionAdmin("AUTO_ASSIGN_ACCEPTED_PUSH",
	        isset($this->data['AUTO_ASSIGN_ACCEPTED_PUSH'])?$this->data['AUTO_ASSIGN_ACCEPTED_PUSH']:'');
			
		} else {
			
		}
		$this->code=1; $this->msg=Driver::t("Setting saved");
		$this->jsonResponse();
	}
	
	public function actionSaveNotificationTemplate()
	{
		//dump($this->data);
		$key=array('PUSH','SMS','EMAIL');
		
		$user_type=Driver::getLoginType();
		if ( $user_type=="admin"){
						
			foreach ($key as $val) {
				$key=$this->data['option_name']."_$val"."_TPL";						
				Yii::app()->functions->updateOptionAdmin($key,
				  isset($this->data[$val])?$this->data[$val]:''
				);
			}
			
		} else {
			
			$merchant_id=Driver::getUserId();				
			foreach ($key as $val) {
				$key=$this->data['option_name']."_$val"."_TPL";						
				Yii::app()->functions->updateOption($key,
				  isset($this->data[$val])?$this->data[$val]:'',
				  $merchant_id
				);
			}
			
		}
		$this->code=1; $this->msg=Driver::t("Template saved");
		$this->jsonResponse();
	}
	
	public function actionGetNotificationTPL()
	{
		$key=array('PUSH','SMS','EMAIL');
		$user_type=Driver::getLoginType();
		if ( $user_type=="admin"){
			
			$data='';			
			foreach ($key as $val) {
				$key=$this->data['option_name']."_$val"."_TPL";						
			    $data[$val]=getOptionA($key);
			}
			
		} else {
			
			$merchant_id=Driver::getUserId();			
			foreach ($key as $val) {
				$key=$this->data['option_name']."_$val"."_TPL";						
			    $data[$val]=getOption($merchant_id,$key);
			}
			
		}		
		$this->details=$data;
		$this->code=1; $this->msg=Driver::t("OK");
		$this->jsonResponse();
	}
	
	public function actionGetNotifications()
	{		
		$data=''; 
		$db_ext=new DbExt; 
		if ( $res=Driver::getNotifications( Driver::getUserType(),Driver::getUserId() ) ){
			foreach ($res as $val) {
								
				if(!empty($val['remarks2'])){							
					$args=json_decode($val['remarks_args'],true);								
					if(is_array($args) && count($args)>=1){
						foreach ($args as $args_key=>$args_val) {
							$args[$args_key]=t($args_val);
						}
					}								
					$new_remarks=$val['remarks2'];								
					$new_remarks=Yii::t("default",$new_remarks,$args);								
					$val['remarks']=$new_remarks;
				}
				
				$data[]=array(
				  'title'=>$val['status']." ".Driver::t("Task ID").":".$val['task_id'],
				  'message'=>$val['remarks'],
				  'task_id'=>$val['task_id'],
				  'status'=>Driver::t($val['status'])
				);
				$db_ext->updateData('{{order_history}}',array(
				  'notification_viewed'=>1
				),'id',$val['id']);
			}
			$this->code=1;
			$this->details=$data;
		} else $this->msg="No notifications";
		$this->jsonResponse();
	}
	
	public function actiongetInitialNotifications()
	{
		$data=''; 
		$db_ext=new DbExt; 
		if ( $res=Driver::getNotifications( Driver::getUserType(),Driver::getUserId() , 1 ) ){
			foreach ($res as $val) {
				$data[]=array(
				  'title'=>$val['status']." ".Driver::t("Task ID").":".$val['task_id'],
				  'message'=>$val['remarks'],
				  'task_id'=>$val['task_id'],
				  'status'=>Driver::t($val['status'])
				);
				$db_ext->updateData('{{order_history}}',array(
				  'notification_viewed'=>1
				),'id',$val['id']);
			}
			$this->code=1;
			$this->details=$data;
		} else $this->msg="No notifications";
		$this->jsonResponse();
	}
	
	public function actionPushLogList()
	{
		$aColumns = array(
		  'push_id',
		  'push_title',
		  'push_message',
		  'push_type',
		  'device_platform',
		  'status'
		);
		$t=AjaxDataTables::AjaxData($aColumns);		
		if (isset($_GET['debug'])){
		    dump($t);
		}
		
		if (is_array($t) && count($t)>=1){
			$sWhere=$t['sWhere'];
			$sOrder=$t['sOrder'];
			$sLimit=$t['sLimit'];
		}	
		
		$and='';						
				
		$stmt="SELECT SQL_CALC_FOUND_ROWS a.*			
		FROM
		{{driver_pushlog}} a
		WHERE 1
		$and		
		$sWhere
		$sOrder
		$sLimit
		";
		if (isset($_GET['debug'])){
		   dump($stmt);
		}
				
		$DbExt=new DbExt; 
		if ( $res=$DbExt->rst($stmt)){
			
			$iTotalRecords=0;						
			$stmtc="SELECT FOUND_ROWS() as total_records";
			if ( $resc=$DbExt->rst($stmtc)){									
				$iTotalRecords=$resc[0]['total_records'];
			}
			
			$feed_data['sEcho']=intval($_GET['sEcho']);
			$feed_data['iTotalRecords']=$iTotalRecords;
			$feed_data['iTotalDisplayRecords']=$iTotalRecords;										
			
			foreach ($res as $val) {
				$date_created=Yii::app()->functions->prettyDate($val['date_created'],true);
			    $date_created=Yii::app()->functions->translateDate($date_created);			
			    			    
			    if ($val['status']=="process"){
			    	$status="<span class=\"btn btn-primary\">".Driver::t($val['status'])."</span>";
			    } elseif ( $val['status']=="pending"){
			    	$status="<span class=\"btn btn-default\">".Driver::t($val['status'])."</span>";
			    } else $status="<span class=\"btn btn-danger\">".Driver::t($val['status'])."</span>";
			    
			    $feed_data['aaData'][]=array(
			      $val['push_id'],
			      $val['driver_id'],
			      $val['push_title'],
			      $val['push_message'],
			      $val['push_type'],
			      $val['device_platform']."<br><span class=\"concat-text\">".$val['device_id']."</span>",
			      $status."<br>".$date_created,
			    );			    
			}
			if (isset($_GET['debug'])){
			   dump($feed_data);
			}
			$this->otableOutput($feed_data);	
		}
		$this->otableNodata();
	}
	
	public function actionChartReports()
	{	
		//dump($this->data);
		$data='';
		if ( $data=Driver::generateReports($this->data['chart_type'], $this->data['time_selection'],
		   $this->data['team_selection'], $this->data['driver_selection'],
		   $this->data['chart_type_option'],
		   $this->data['start_date'],
		   $this->data['end_date']
		    )){		    	
		}		
						
		$new_data='';
			
		if (is_array($data) && count($data)>=1){
			
			$first_date=date("Y-m-d",strtotime($data[0]['delivery_date']."-1 day"));
				$new_data[]=array(
				   'date'=>$first_date,
				   'successful'=>0,
				   'cancelled'=>0,
				   'failed'=>0
		    );
			
			foreach ($data as $val) {
				//dump($val);
				switch ($val['status']) {
					
					case "successful":	
					$new_data[]=array(
					  'date'=>$val['delivery_date'],
					  'successful'=>$val['total'],
					  'driver_name'=>isset($val['driver_name'])?$val['driver_name']:''
					);
					break;
						
					case "cancelled":	
					$new_data[]=array(
					  'date'=>$val['delivery_date'],
					  'cancelled'=>$val['total'],
					  'driver_name'=>isset($val['driver_name'])?$val['driver_name']:''
					);
					break;
					
					case "failed":	
					$new_data[]=array(
					  'date'=>$val['delivery_date'],
					  'failed'=>$val['total'],
					  'driver_name'=>isset($val['driver_name'])?$val['driver_name']:''
					);
					break;
				
					default:
						break;
				}
			}
		} else {
			/*$new_data[]=array(
			  'date'=>date("Y-m-d"),
			  'failed'=>0,
			  'driver_name'=>''
			);*/
		}
		
		$table='';
		
				
		if ( $this->data['chart_type_option']=="agent"){
		
			ob_start();
			require_once('charts-bar.php');
			$charts = ob_get_contents();
            ob_end_clean();
            
            ob_start();
            require_once('chart-bar-table.php');
            $table = ob_get_contents();
            ob_end_clean();
            
		} else {						        
            ob_start();
		    require_once('charts.php');		   
		    $charts = ob_get_contents();
            ob_end_clean();
            
            ob_start();
			require_once('chart-table.php');			
			$table = ob_get_contents();
            ob_end_clean();
		}		
		$this->code=1;
		$this->msg="OK";
		$this->details=array(
		  'charts'=>$charts,
		  'table'=>$table
		);
		$this->jsonResponse();
	}
	
	public function actionsaveAssigmentSettings()
	{		
		$this->code=1;
		Driver::updateOption('driver_auto_assign_type', 
		isset($this->data['driver_auto_assign_type'])?$this->data['driver_auto_assign_type']:'' );
		
		Driver::updateOption('driver_assign_request_expire', 
		isset($this->data['driver_assign_request_expire'])?$this->data['driver_assign_request_expire']:'' );
		
		Driver::updateOption('driver_enabled_auto_assign', 
		isset($this->data['driver_enabled_auto_assign'])?$this->data['driver_enabled_auto_assign']:'' );
		
		Driver::updateOption('driver_include_offline_driver', 
		isset($this->data['driver_include_offline_driver'])?$this->data['driver_include_offline_driver']:'' );
		
		Driver::updateOption('driver_autoassign_notify_email', 
		isset($this->data['driver_autoassign_notify_email'])?$this->data['driver_autoassign_notify_email']:'' );
		
		Driver::updateOption('driver_request_expire', 
		isset($this->data['driver_request_expire'])?$this->data['driver_request_expire']:'' );
		
		$this->msg= Driver::t("Setting saved");
		$this->jsonResponse();
	}
	
	public function actionUploadCertificate()
	{
		require_once('Uploader.php');
		$path_to_upload=Driver::certificatePath();
        $valid_extensions = array('pem'); 
        if(!file_exists($path_to_upload)) {	
           if (!@mkdir($path_to_upload,0777)){           	               	
           	    $this->msg=Driver::t("Error has occured cannot create upload directory");
                $this->jsonResponse();
           }		    
	    }
	    
        $Upload = new FileUpload('uploadfile');
        $ext = $Upload->getExtension(); 
        //$Upload->newFileName = mktime().".".$ext;
        $result = $Upload->handleUpload($path_to_upload, $valid_extensions);                
        if (!$result) {                    	
            $this->msg=$Upload->getErrorMsg();            
        } else {         	
        	$this->code=1;
        	$this->msg=Driver::t("upload done");        	        
			$this->details=Yii::app()->getBaseUrl(true)."/upload/".$_GET['uploadfile'];			
        }
        $this->jsonResponse();
	}	
	
	public function actionsaveIOSSettings()
	{
		
		Yii::app()->functions->updateOptionAdmin("driver_ios_push_dev_cer",
	    isset($this->data['driver_ios_push_dev_cer'])?$this->data['driver_ios_push_dev_cer']:'');
	    
	    Yii::app()->functions->updateOptionAdmin("driver_ios_push_prod_cer",
	    isset($this->data['driver_ios_push_prod_cer'])?$this->data['driver_ios_push_prod_cer']:'');
	    
	    Yii::app()->functions->updateOptionAdmin("driver_ios_push_mode",
	    isset($this->data['driver_ios_push_mode'])?$this->data['driver_ios_push_mode']:'');
	    
	    Yii::app()->functions->updateOptionAdmin("driver_ios_pass_phrase",
	    isset($this->data['driver_ios_pass_phrase'])?$this->data['driver_ios_pass_phrase']:'');
		
	    $this->code=1;
		$this->msg= Driver::t("Setting saved");
		$this->jsonResponse();
	}
	
	public function actionRetryAutoAssign()
	{		
		if ( isset($this->data['task_id'])){
			$task_id=$this->data['task_id'];
			$this->code=1;
			$this->msg="OK";
						
			$less="-1";
						
			$params=array(			  
			  'assignment_status'=>'waiting for driver acknowledgement',
			  'assign_started'=>date('c',strtotime("$less min")),
			  'auto_assign_type'=>''
			);
						
			$db=new DbExt;
			$db->updateData("{{driver_task}}",$params,'task_id',$task_id);
			
			/*$stmt="UPDATE 
			{{driver_assignment}}
			SET status='pending',
			task_status='unassigned'
			WHERE
			task_id=".Driver::q($task_id)."
			";*/			
			$stmt="DELETE FROM
			{{driver_assignment}}
			WHERE
			task_id=".Driver::q($task_id)."
			";
			$db->qry($stmt);
						
			//re process
			//$url=Yii::app()->getBaseUrl(true)."/driver/cron/processautoassign";
			
			$url=Yii::app()->getBaseUrl(true)."/driver/cron/autoassign";
			@file_get_contents($url);
			
		} else $this->msg=Driver::t("Missing task id");
		$this->jsonResponse();
	}
	
	public function actionSendPushToDriver()
	{		
		$driver_id=$this->data['push_form_driver_id'];
		if ($info=Driver::driverInfo($driver_id)){			
			$params=array(
			  'driver_id'=>$this->data['push_form_driver_id'],
			  'push_title'=>$this->data['push_title'],
			  'push_message'=>$this->data['push_message'],
			  'date_created'=>date('Y-m-d H:i:s'),
			  'ip_address'=>$_SERVER['REMOTE_ADDR'],
			  'push_type'=>"private",
			  'actions'=>"private",
			  'device_platform'=>$info['device_platform'],
			  'device_id'=>$info['device_id']
			);					
			if ( self::$db->insertData("{{driver_pushlog}}", $params)){
				$push_id=Yii::app()->db->getLastInsertID();
				$this->code=1;
				$this->msg=Driver::t("Push has been saved please wait until the cron process the push");				
			    Driver::RunPush( $push_id ); 
			} else $this->msg=Driver::t("failed cannot insert record");
		} else $this->msg=Driver::t("Record not found");
		$this->jsonResponse();
	}
	
	public function actionSendPushBulk()
	{		
		$params=array(
		  'push_title'=>$this->data['push_title2'],
		  'push_message'=>$this->data['push_message2'],
		  'date_created'=>date('Y-m-d H:i:s'),
		  'ip_address'=>$_SERVER['REMOTE_ADDR']
		);		
		if ( self::$db->insertData("{{driver_bulk_push}}", $params)){
			$push_id=Yii::app()->db->getLastInsertID();
			$this->code=1;
			$this->msg=Driver::t("Push has been saved please wait until the cron process the push");		    
		} else $this->msg=Driver::t("failed cannot insert record");
	    $this->jsonResponse();
	}
		
}/* end class*/