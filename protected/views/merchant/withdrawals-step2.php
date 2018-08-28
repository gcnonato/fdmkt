<?php 
$withdrawal_id=isset($_GET['id'])?$_GET['id']:'';
?>

<div class="uk-width-1">
<a href="<?php echo Yii::app()->request->baseUrl; ?>/merchant/withdrawals/" class="uk-button"><i class="fa fa-long-arrow-left"></i> <?php echo Yii::t("default","Back")?></a>


<?php if ( $res=Yii::app()->functions->getWithdrawalInformation($withdrawal_id)):?>
<?php 
$wd_days_process=Yii::app()->functions->getOptionAdmin("wd_days_process");
if (empty($wd_days_process)){
	$wd_days_process=5;
}
$process_date=date("d F Y", strtotime ($res['date_created']." +$wd_days_process days"));
?>

<p>
<?php echo t("Your withdrawal request has been sent to admin")?>.<br/>
<?php echo t("Total Amount")." ".displayPrice(adminCurrencySymbol(),normalPrettyPrice($res['amount']))?>
</p>
<p>
<?php echo t("Completaremos el procesamiento del pago el ")?> <?php echo $process_date." ";?>
<?php echo t("(o el siguiente día hábil), pero puede tomar hasta 7 días para que aparezca en tu cuenta. Cuando finalice el proceso recibirás un mensaje de confirmación.")?>
</p>

<?php else :?>
<p class="uk-text-danger"><?php echo t("Sorry but we cannot find what you are looking for.")?></p>
<?php endif;?>

</div>
