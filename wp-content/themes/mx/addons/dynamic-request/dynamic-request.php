<?php
/** 
 * @version 2.0.0
 */
class dynamic_request{
	public static function init(){
		add_action('wp_ajax_' . __CLASS__, __CLASS__ . '::process');
		add_action('wp_ajax_nopriv_' . __CLASS__, __CLASS__ . '::process');
		add_action('wp_enqueue_scripts', __CLASS__  . '::frontend_enqueue_scripts' ,1);
	}
	public static function process(){
		theme_features::check_referer();
		
		$output = apply_filters('dynamic_request_process',[]);
		$output['theme-nonce'] = wp_create_nonce('theme-nonce');
		/**
		 * dev mode
		 */
		if(class_exists('theme_dev_mode') && theme_dev_mode::get_options('queries')){
			global $wpdb;
			$output['debug'] = [
				'queries' => $wpdb->queries,
				'time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
				'memory' => sprintf('%01.3f',memory_get_usage()/1024/1024),
			];
		}
		header('Content-Type: application/javascript');
		die('window.DYNAMIC_REQUEST = ' . json_encode($output));
	}
	public static function frontend_enqueue_scripts(){
		$data = apply_filters('dynamic_request',[]);
		$data['action'] = __CLASS__;
		wp_enqueue_script(
			__CLASS__,
			theme_features::get_process_url($data),
			[],
			theme_file_timestamp::get_timestamp(),
			true
		);
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'dynamic_request::init';
	return $fns;
});