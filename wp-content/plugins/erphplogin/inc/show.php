<?php 
function get_erphplogin(){
	if(!is_user_logged_in()){
		return '<a href="'.constant("erphplogin").'auth/qq.php?erphploginurl='.get_option('erphplogin_url').'"><img src="'.constant("erphplogin").'img/login_qq.png"></a>
	<a href="'.constant("erphplogin").'auth/sina.php?erphploginurl='.get_option('erphplogin_url').'"><img src="'.constant("erphplogin").'img/login_sina.png"></a>';
	}else{return '';}
}

?>
