<?php

add_filter('theme_addons',function($fns){
	$fns[] = 'theme_login_with_email::init';
	return $fns;
});
class theme_login_with_email {
	private static $iden = 'theme-login-with-email';

	public static function init(){
		add_filter('authenticate',__CLASS__ . '::authenticate', 20, 3);
	}
	public static function authenticate($user, $username, $password){
		if(is_email($username)){
			$user = get_user_by('email',$username);
			if($user) $username = $user->user_login;
		}
		return wp_authenticate_username_password(null,$username,$password);
	}
}
