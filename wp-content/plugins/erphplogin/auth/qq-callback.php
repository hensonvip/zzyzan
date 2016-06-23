<?php  
include_once('../../../../wp-config.php');
$appid = get_option('erphplogin_qqid');
$appkey = get_option('erphplogin_qqkey');
$callback = new qq();
$callback->callback($appid,$appkey,constant("erphplogin").'auth/qq-callback.php');
$callback->get_openid();
$callback->qq_cb();
?>