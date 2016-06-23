<?php
/**
 * @version 1.0.0
 */
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_custom_account::init';
	return $fns;
});
class theme_custom_account{
	
	public static $iden = 'theme_custom_account';
	public static $page_slug = 'account';

	public static function init(){
		add_action('init', 					__CLASS__ . '::page_create');
		
		add_filter('query_vars',			__CLASS__ . '::filter_query_vars');
		
		add_action('template_redirect',		__CLASS__ . '::template_redirect');

	}
	public static function filter_query_vars($vars){
		if(!in_array('tab',$vars)) 
			$vars[] = 'tab';
			
		return $vars;
	}
	public static function template_redirect(){

		if(!self::is_page())
			return false;

		if(theme_cache::is_user_logged_in()){
			$account_navs = apply_filters('account_navs',[]);

			if(!isset($account_navs[get_query_var('tab')]))
				wp_redirect(add_query_arg('tab','dashboard',self::get_url()));
			
		}else{
			wp_redirect(theme_custom_sign::get_tabs('login',get_current_url())['url']);
			die;
		}

	}
	public static function get_url(){
		static $cache = null;
		if($cache === null)
			$cache = theme_cache::get_permalink(get_page_by_path(self::$page_slug)->ID);
			
		return $cache;
	}
	public static function is_page(){
		static $cache = null;
		if($cache === null)
			$cache = theme_cache::is_page(self::$page_slug);

		return $cache;
	}
	public static function page_create(){
		if(!theme_cache::current_user_can('manage_options')) 
			return false;
		
		$page_slugs = array(
			self::$page_slug => array(
				'post_content' 	=> '[no-content]',
				'post_name'		=> 'account',
				'post_title'	=> ___('Account'),
				'page_template'	=> 'page-' . self::$page_slug . '.php',
			)
		);
		
		$defaults = array(
			'post_content' 		=> '[post_content]',
			'post_name' 		=> null,
			'post_title' 		=> null,
			'post_status' 		=> 'publish',
			'post_type'			=> 'page',
			'comment_status'	=> 'closed',
		);
		foreach($page_slugs as $k => $v){
			theme_cache::get_page_by_path($k) || wp_insert_post(array_merge($defaults,$v));
		}
	}
}