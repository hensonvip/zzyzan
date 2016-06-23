<?php  
include_once('../../../../wp-config.php');
$appid =get_option('erphplogin_sinaid');
$login = new sina();
$login->login($appid,constant("erphplogin").'auth/sina-callback.php');
?>