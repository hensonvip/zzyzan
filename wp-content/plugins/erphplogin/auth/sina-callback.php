<?php  
include_once('../../../../wp-config.php');
$appid = get_option('erphplogin_sinaid');
$appkey = get_option('erphplogin_sinakey');
$callback = new sina();
$callback->callback($appid,$appkey,constant("erphplogin").'auth/sina-callback.php');
$callback->sina_cb();
?>