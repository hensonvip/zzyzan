<?php 
class sina 
{
	function login($appid, $callback) {
		$_SESSION['rurl'] = $_REQUEST ["erphploginurl"];
		$_SESSION ['state'] = md5 ( uniqid ( rand (), true ) ); //CSRF protection
		$login_url = "https://api.weibo.com/oauth2/authorize?client_id=".$appid."&response_type=code&redirect_uri=".$callback."&state=".$_SESSION['state'];
		header ( "Location:$login_url" );
	}
	function callback($appid,$appkey,$path){
		if ($_REQUEST ['state'] == $_SESSION ['state']) {
			$url = "https://api.weibo.com/oauth2/access_token";
			$data = "client_id=".$appid."&client_secret=".$appkey."&grant_type=authorization_code&redirect_uri=".$path."&code=".$_REQUEST ["code"];
			$output = json_decode(erphplogin_post($url,$data));
			$_SESSION['access_token'] = $output->access_token;
			$_SESSION['uid'] = $output->uid;
		}else{
			echo ("The state does not match. You may be a victim of CSRF.");
			exit;
		}
	}
	function get_user_info() {
		$get_user_info = "https://api.weibo.com/2/users/show.json?uid=".$_SESSION['uid']."&access_token=".$_SESSION['access_token'];
		return get_url_contents ( $get_user_info );
	}
	function sina_cb(){
		global $wpdb;
		$openid_db = $wpdb->get_col("SELECT sinaid FROM $wpdb->users");
		$user_ID = $wpdb->get_results("SELECT ID FROM $wpdb->users WHERE sinaid='$_SESSION[uid]'");
		$user_ID = $user_ID[0]->ID;
		if (in_array($_SESSION['uid'], $openid_db)) {
	        wp_set_auth_cookie($user_ID,true,false);
	        wp_redirect($_SESSION['rurl']);
	        exit();
	    }else{
	        //$a= microtime()*1000000;
	        
	        $pass = wp_create_nonce(rand(10,1000));
	        $str = json_decode($this->get_user_info());
			$login_name = "u".$str->id;
			$username = $str->screen_name;
			$userimg = $str->avatar_large;
			$description = $str->description;
			//$userid = $str->id;
	        $userdata=array(
	          'user_login' => $login_name,
			  'user_email' => $login_name.'@live.com',
	          'display_name' => $username,
	          'user_pass' => $pass,
	          'role' => get_option('default_role'),
	          'nickname' => $username,
			  'first_name' => $username,
			  'description'=> $description
	        );
	        $user_id = wp_insert_user( $userdata );
	        if ( is_wp_error( $user_id ) ) {
	            echo $user_id->get_error_message();
	        }else{
	            $ff = $wpdb->query("UPDATE $wpdb->users SET sinaid = '$_SESSION[uid]' WHERE ID = '$user_id'");
	            if ($ff) {
	                $userapi = array(
	                    'userimg' => $userimg,
	                    'q_token' => '',
	                    's_token' => $_SESSION['access_token']
	                );
	                $data = add_user_meta($user_id,'userapi',$userapi);
	                wp_set_auth_cookie($user_id,true,false);
	                wp_redirect($_SESSION['rurl']);
	            }          
	        }
	        exit();
	    }
	}
	function sina_bd(){
		global $wpdb;
		$userinfo=get_userdata(get_current_user_id());
		$userid = $userinfo->id;
		$wpdb->query("UPDATE $wpdb->users SET sinaid = '$_SESSION[uid]' WHERE ID = $userid");
	    $userdata = get_user_meta($userid,'userapi',true);
	    $q_token = $userdata['q_token'];
	    $sinaimg = $userdata['userimg'];
	    $str = json_decode($this->get_user_info());
	    if (empty($sinaimg)) {
	        $sinaimg = $str->avatar_large;
	    }
	    $userapi = array(
	        'userimg' => $sinaimg,
	        'q_token' => $q_token,
	        's_token' => $_SESSION['access_token']
	    );
	    $data = update_user_meta($userid,'userapi',$userapi);
	    if ($data) {
	        wp_redirect($_SESSION['rurl']);
	        exit();
	    }else{
	        exit('<meta charset="UTF-8" />绑定失败，可能之前已经绑定，请直接使用该帐号登录。');
	    }
	}
}
?>