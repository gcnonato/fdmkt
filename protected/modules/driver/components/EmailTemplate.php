<?php
class EmailTemplate
{	
	public static function forgotPasswordRequest()
	{
      $website_title=Yii ::app()->functions->getOptionAdmin('website_title');      
	  return <<<HTML
	  <p>Hi {first_name}</p>
	  <br/>
	  <p>Your password change code is : {code}</p>	  
	  <p>Thank you.</p>
	  <p>- $website_title</p>
HTML;
	}

    public static function unAbletoAssignTemplate($task_id='')
	{     
	  return <<<HTML
	  <p>Hi</p>
	  <br/>
	  <p>Task $task_id could not be Auto-Assigned by the system due to unavailability of agents.<br/>
	  Please review and take necessary action.
	  </p>
HTML;
	}		
		
} /*end class*/