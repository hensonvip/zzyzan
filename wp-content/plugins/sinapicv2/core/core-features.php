<?php
namespace sinapicv2;
/**
 * WordPress Extra Plugin Features.
 *
 * Help you write a wp plugin quickly.
 *
 * @package KMTF
 * @version 3.0.0
 * @author INN STUDIO
 */
class plugin_features{
	public static $basedir_js 					= '/assets/js/';
	public static $basedir_css		 			= '/assets/css/';
	public static $basedir_images 				= '/assets/images/';
	
	public static function get_plugin_data($key = null){
		static $caches = [];

		if(empty($caches))
			$caches = \get_plugin_data(dirname(__DIR__) . '/' . __NAMESPACE__ . '.php',false);

		if($key){
			return isset($caches[$key]) ? $caches[$key] : null;
		}else{
			return $caches;
		}
	}
	public static function get_base_path(){
		static $cache = null;
		if($cache === null)
			$cache = dirname(__DIR__);
		return $cache;
	}
	public static function get_base_url(){
		static $cache = null;
		if($cache === null){
			$cache = \plugins_url(basename(dirname(__DIR__)));
		}
		return $cache;
	}
	public static function get_file_path($file_name = null){
		$file_path = self::get_base_path() . '/' . $file_name;
		return $file_path;
	}
	public static function get_file_url($file_name = null,$mtime = false){
		$file_path = self::get_file_path($file_name);
		$file_url = $file_name ? self::get_base_url() . $file_name : self::get_base_url();
		if($mtime){
			if(is_file($file_path))
				return $file_url . '?v=' . filemtime($file_path);
			return $file_url;
		}
		return $file_url;
	}
	public static function get_js($file_basename, $mtime = false){
		$filename = $file_basename . '.js';
		return self::get_file_url(self::$basedir_js . $filename, $mtime);
	}
	public static function get_img($filename, $mtime = false){
		return self::get_file_url(self::$basedir_images . $filename, $mtime);
	}
	public static function get_css($file_basename, $mtime = false){
		$filename = $file_basename . '.css';
		return self::get_file_url(self::$basedir_css . $filename, $mtime);
	}
	public static function get_process_url(array $param = []){
		$admin_ajax_url = self::admin_url('admin-ajax.php');
		if(!$param) 
			return $admin_ajax_url;
		return $admin_ajax_url . '?' . http_build_query($param);
	}
	public static function is_ajax(){
		static $cache = null;
		if($cache === null)
			$cache = defined('DOING_AJAX') && DOING_AJAX;
		return $cache;
	}
	public static function admin_url($path = null){
		static $cache = null;
		if($cache === null)
			$cache = \admin_url();
		return $cache . $path;
	}
	public static function is_admin(){
		static $cache = null;
		if($cache === null)
			$cache = (bool)\is_admin();
		return $cache;
	}
}