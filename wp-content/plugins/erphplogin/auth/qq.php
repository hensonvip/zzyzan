<?php  
include_once('../../../../wp-config.php');
$scope = 'get_user_info,add_share,list_album,add_album,upload_pic,add_topic,add_one_blog,add_weibo';
$appid = get_option('erphplogin_qqid');
$login = new qq();
$login->login($appid,$scope,constant("erphplogin").'auth/qq-callback.php');
?>