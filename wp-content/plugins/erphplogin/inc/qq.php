<?php 
class qq
{
	function login($appid, $scope, $callback) {
		$_SESSION['rurl'] = $_REQUEST ["erphploginurl"];
		$_SESSION ['state'] = md5 ( uniqid ( rand (), true ) ); //CSRF protection
		$login_url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=" . $appid . "&redirect_uri=" . urlencode ( $callback ) . "&state=" . $_SESSION ['state'] . "&scope=" . $scope;
		header ( "Location:$login_url" );
	}
	function callback($appid,$appkey,$path) {
		if ($_REQUEST ['state'] == $_SESSION ['state']) {
			$token_url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&" . "client_id=" . $appid . "&redirect_uri=" . urlencode ( $path ) . "&client_secret=" . $appkey . "&code=" . $_REQUEST ["code"];
			
			$response = get_url_contents ( $token_url );
			if (strpos ( $response, "callback" ) !== false) {
				$lpos = strpos ( $response, "(" );
				$rpos = strrpos ( $response, ")" );
				$response = substr ( $response, $lpos + 1, $rpos - $lpos - 1 );
				$msg = json_decode ( $response );
				if (isset ( $msg->error )) {
					echo "<h3>错误代码:</h3>" . $msg->error;
					echo "<h3>信息  :</h3>" . $msg->error_description;
					exit ();
				}
			}
			
			$params = array ();
			parse_str ( $response, $params );
			$_SESSION ['access_token'] = $params ["access_token"];
		} else {
			echo ("The state does not match. You may be a victim of CSRF.");
			exit;
		}
	}
	function get_openid() {
		$graph_url = "https://graph.qq.com/oauth2.0/me?access_token=" . $_SESSION ['access_token'];
		
		$str = get_url_contents ( $graph_url );
		if (strpos ( $str, "callback" ) !== false) {
			$lpos = strpos ( $str, "(" );
			$rpos = strrpos ( $str, ")" );
			$str = substr ( $str, $lpos + 1, $rpos - $lpos - 1 );
		}
		
		$user = json_decode ( $str );
		if (isset ( $user->error )) {
			echo "<h3>错误代码:</h3>" . $user->error;
			echo "<h3>信息  :</h3>" . $user->error_description;
			exit ();
		}
		$_SESSION ['openid'] = $user->openid;
	}
	function get_user_info() {
		$get_user_info = "https://graph.qq.com/user/get_user_info?" . "access_token=" . $_SESSION ['access_token'] . "&oauth_consumer_key=".get_option('erphplogin_qqid')."&openid=" . $_SESSION ['openid'] . "&format=json";		
		return get_url_contents ( $get_user_info );
	}
	function qq_cb(){
		global $wpdb;
		$openid_db = $wpdb->get_col("SELECT qqid FROM $wpdb->users");
	    if (in_array($_SESSION['openid'], $openid_db)) {
	        $user_ID = $wpdb->get_results("SELECT ID FROM $wpdb->users WHERE qqid='$_SESSION[openid]'");
	        $user_ID = $user_ID[0]->ID;
	        wp_set_auth_cookie($user_ID,true,false);
	        wp_redirect($_SESSION['rurl']);
	        exit();
	    }else{
	        $a= microtime()*1000000;
	        $pass = wp_create_nonce(rand(10,1000));
	        $uinfo = json_decode($this->get_user_info());
			$login_name = "qq".wp_create_nonce($a);
	        $username = $uinfo->nickname;
	        $userdata=array(
	          'user_login' => $login_name,
			  'user_email' => $login_name.'@live.com',
	          'display_name' => $username,
			  'nickname' => $username,
	          'user_pass' => $pass,
	          'role' => get_option('default_role'),
	          'first_name' => $username
	        );
	        $user_id = wp_insert_user( $userdata );
	        if ( is_wp_error( $user_id ) ) {
	            echo $user_id->get_error_message();
	        }else{
	            $ff = $wpdb->query("UPDATE $wpdb->users SET qqid = '$_SESSION[openid]' WHERE ID = '$user_id'");
	            if ($ff) {
	                $userapi = array(
	                    'userimg' => $uinfo->figureurl_qq_2,
	                    'q_token' => $_SESSION['access_token'],
	                    's_token' => ''
	                );
	                $data = add_user_meta($user_id,'userapi',$userapi);
	                wp_set_auth_cookie($user_id,true,false);
	                wp_redirect($_SESSION['rurl']);
					
	            }          
	        }
	        exit();
	    }
	}
	function qq_bd(){
		global $wpdb;
		$userinfo=get_userdata(get_current_user_id());
		$userid = $userinfo->id;
		$wpdb->query("UPDATE $wpdb->users SET qqid = '$_SESSION[openid]' WHERE ID = $userid");
	    $userdata = get_user_meta($userid,'userapi',true);
	    $s_token = $userdata['s_token'];
	    $qqimg = $userdata['userimg'];
	    $uinfo = json_decode($this->get_user_info());
	    if (empty($qqimg)) {
	        $qqimg = $uinfo->figureurl_qq_2;
	    }
	    $userapi = array(
	        'userimg' => $qqimg,
	        'q_token' => $_SESSION['access_token'],
	        's_token' => $s_token
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