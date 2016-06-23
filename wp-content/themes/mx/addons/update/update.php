<?php
/*
Feature Name:	theme_update
Feature URI:	http://www.inn-studio.com
Version:		3.0.1
Description:	theme_update
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
class theme_update{
	private static $checker_url;
	private static $last_theme;
	public static function init(){
		if(!theme_cache::current_user_can('manage_options'))
			return;
		
		self::$checker_url = ___('http://update.inn-studio.com') . '/?action=get_update&host=' . $_SERVER['HTTP_HOST'] . '&slug=' . theme_functions::$iden;
		
		add_filter('site_transient_update_themes', __CLASS__ . '::check_for_update');
		
		add_filter('upgrader_source_selection', __CLASS__ . '::filter_upgrader_source_selection', 10, 3);
		
		add_filter('upgrader_pre_install', __CLASS__ . '::filter_upgrader_pre_install', 10, 2);
		
	}
	public static function filter_upgrader_pre_install($true,$hook_extra){
		self::$last_theme = $hook_extra['theme'];
		return $true;
	}
	public static function check_for_update($transient){
		if (!isset($transient->checked[theme_functions::$basename]))
			return $transient;

		$response = self::get_response(self::$checker_url);
		if(!$response)
			return $transient;

		$response = self::to_wp_format((array)$response);

		/** version compare */
		if(version_compare($transient->checked[theme_functions::$basename], $response['new_version'], '>='))
			return $transient;
			
		/** have new version */
		$transient->response[theme_functions::$basename] = $response;
		return $transient;
	}

	public static function filter_upgrader_source_selection($source, $remote_source = null, $upgrader = null){
		/** if not current theme, return source */
		if(self::$last_theme != theme_functions::$basename)
			return $source;

		$corrected_source = $remote_source . '/' . self::$last_theme . '/';

		if(rename($source, $corrected_source)){
			return $corrected_source;
		}else{
			$upgrader->skin->feedback(___('Unable to rename downloaded theme.'));
			return new WP_Error();
		}
	}
	private static function get_response($remote_url){
		static $response = null;
		if($response === null){
			$response = wp_remote_get( $remote_url );
		}else{
			return $response;
		}

		if( is_wp_error($response) || ($response['response']['code'] != 200) )
			return false;

		$response = json_decode($response['body'],true);
		if(!$response)
			return false;

		if(!isset($response['version']) || !isset($response['homepage']) || !isset($response['download_url']))
			return false;
			
		return $response;
	}
	private static function to_wp_format(array $response){
		if(!isset($response['version'],$response['homepage'],$response['download_url']))
			return false;
		return [
			'new_version' => $response['version'],
			'url' => $response['homepage'],
			'package' => $response['download_url'],
		];
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_update::init';
	return $fns;
});