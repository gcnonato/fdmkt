
<?php
$visible=true;
if ( Driver::getUserType()=="merchant"){
	$visible=false;
}

$menu =  array(  		    		    
    'activeCssClass'=>'active', 
    'encodeLabel'=>false,
    'items'=>array(
    
        array('visible'=>true,'label'=>'<i class="ion-grid"></i>&nbsp; '.t('Dashboard'),
        'url'=>array('/driver'),'linkOptions'=>array()),               
        
        array('visible'=>true,'label'=>'<i class="ion-android-contacts"></i>&nbsp; '.t("Teams"),
        'url'=>array('/driver/index/teams'),'linkOptions'=>array()),       
        
        array('visible'=>true,'label'=>'<i class="ion-android-contact"></i>&nbsp; '.t("Driver"),
        'url'=>array('/driver/index/agents'),'linkOptions'=>array()),       
        
        array('visible'=>true,'label'=>'<i class="ion-ios-checkmark"></i>&nbsp; '.t("Tasks"),
        'url'=>array('/driver/index/tasks'),'linkOptions'=>array()),       
        
        array('visible'=>$visible,'label'=>'<i class="ion-gear-b"></i>&nbsp; '.t("Settings"),
        'url'=>array('/driver/index/settings'),'linkOptions'=>array()),       
        
        array('visible'=>$visible,'label'=>'<i class="ion-flag"></i>&nbsp; '.t("Language"),
        'url'=>array('/driver/index/language'),'linkOptions'=>array()),        
                
        array('visible'=>$visible,'label'=>'<i class="ion-ios-bell"></i>&nbsp; '.t("Notifications"),
        'url'=>array('/driver/index/notifications'),'linkOptions'=>array()),        
                
        array('visible'=>true,'label'=>'<i class="ion-android-car"></i>&nbsp; '.t("Assignment"),
        'url'=>array('/driver/index/assignment'),'linkOptions'=>array()),                
        
        array('visible'=>true,'label'=>'<i class="ion-ios-list"></i>&nbsp; '.t("Reports"),
        'url'=>array('/driver/index/reports'),'linkOptions'=>array()),       
        
        array('visible'=>$visible,'label'=>'<i class="ion-android-list"></i>&nbsp; '.t("Push Logs"),
        'url'=>array('/driver/index/pushlogs'),'linkOptions'=>array()),        
                
     )   
);       
?>

<div class="left-menu">
  <?php $this->widget('zii.widgets.CMenu', $menu);?>
</div> <!--left-menu-->
