<?php //if ( $res=FunctionsV3::merchantInformation($merchant_id)):?>
<?php //foreach ($res as $val): ?>
<?php //if ( $val['food_handling_card'] == 'Al día' ):?>
<?php   
$imagename = getOption($merchant_id,'merchant_food_handling_card_photo');
$msg = getOption($merchant_id,'merchant_food_message');
if($imagename!='')
{
?>  
   <div>
       <h3> <a style="cursor:pointer;" onclick="showimage()"> Permiso de manipulación de alimentos: </a></h3>
        <img id="merchant_food_handling_card_photo"  src="<?php echo uploadURL()."/".$imagename?>" style="display:none;">
    </div>
<?php
}
else{
    //$imagename = getOption($merchant_id,'merchant_food_handling_card_photo');
    if ( $res=FunctionsV3::merchantInformation($merchant_id)):
        foreach ($res as $val): 
            if ( $val['food_handling_card'] == 'En proceso' ):
                echo "<a style='text-decoration:none'>Permiso de manipulación de alimentos: En proceso</a>";
            endif;
            if ( $val['food_handling_card'] == 'No cuento con permiso' ):
                echo "<a style='text-decoration:none'>Permiso de manipulación de alimentos: No cuento con permiso</a>";
            endif;
        endforeach;
    endif;

}
?> 
<?php //endif;?>
<?php if ( $msg!='' ):?>
   <div>
      <h3> <?php //echo getOption($merchant_id,'merchant_food_message')?></h3>
    </div> 
<?php endif;?>
<?php //endforeach;?>
<?php //endif;?>
<script>
    function showimage(){
        $("#merchant_food_handling_card_photo").css({"display":"block"});
    }
</script>