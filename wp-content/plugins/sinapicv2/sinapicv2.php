<?php
/*
Plugin Name:	Sina Pic v2
Plugin URI: 	http://inn-studio.com/sinapicv2
Description: 	Best Image Hosting Plugin for WP. Upload your picture to sina weibo and show it on your site.
Author: 		INN STUDIO
Author URI: 	http://inn-studio.com
Version: 		2.4.4
Text Domain:	sinapicv2
Domain Path:	/languages
*/
namespace sinapicv2;

class sinapicv2{
	private static $plugin_data;
	private static $basedir_backup = '/sinapicv2-backup/';
	private static $thumbnail_external_key = 'thumbnail-external-url';
	private static $allow_types = ['png','gif','jpg','jpeg'];
	private static $file_url = null;
	private static $key_authorization = 'authorization';
	private static $b = ['a','b','c','d','e'];
	private static $p = ['_'];
	private static $d = ['1','2','3','4','5','6'];
	private static $q = ['s','o'];
	private static $wb = ['514aj1aPKgLZMu80Divag82C/8O1A+OA9liLjSXmQd+cE8FGHnXG','9e25KNBWUg5lLjSaXSaAs4EYFF4Wz0kR1f3Q+Hex+0YglMhOo+2L1VUpj6JwBp0CrqXUBMquIZFTubYtdg','4875UnQ0A6s38/Ra/hlOAC7BqbS/5AwQhQjFsfuiQRlaCggd/7GnXiUaafN8itsl6m9IaQRmp0F16h2B4fv4+zwIpmirNwOeeUl7CKaN7yZpTV/lIruW'];
	private static $available_times = 10;
	private static $cache_backup_files = [];
	
	public static function init(){
		add_action('plugins_loaded', __CLASS__ . '::plugins_loaded');
	}
	public static function plugins_loaded(){
		include __DIR__ . '/core/core-functions.php';
		include __DIR__ . '/core/core-features.php';
		include __DIR__ . '/core/core-options.php';
		
		/** update */
		if(self::is_admin()){
			
			self::tdomain();
			
			\add_action('admin_enqueue_scripts', __CLASS__ . '::post_new_enqueue_scripts', 999);
			
			/** update */
			self::update();
			
			/** assets */
			\add_action('admin_footer', __CLASS__ . '::admin_footer_post_new', 1);

			\add_filter('backend_js_config_' . __NAMESPACE__, __CLASS__ . '::backend_js_config');
			
			/** 
			 * post page
			 */
			\add_action('admin_init', __CLASS__ . '::meta_box_add');
			\add_action('save_post', __CLASS__ . '::meta_box_save');
		}
		
		/** 
		 * options
		 */
		\add_filter('plugin_options_default_' . __NAMESPACE__, __CLASS__ . '::options_default');

		/** 
		 * ajax
		 */
		if(plugin_features::is_ajax()){
			\add_filter('plugin_options_save_' . __NAMESPACE__, __CLASS__ . '::backend_options_save');
			\add_action('wp_ajax_' . __NAMESPACE__, __CLASS__ . '::process');
		}


		\add_filter('post_thumbnail_html', __CLASS__ . '::filter_post_thumbnail_html',10,5);

		\add_filter('get_post_metadata', __CLASS__ . '::filter_get_post_metadata',10,4);

		/** options */
		if(plugin_options::is_options_page()){
			
			/** settings */
			\add_action('plguin_base_settings_' . __NAMESPACE__, __CLASS__ . '::display_backend_base_settings_base');
			
			\add_action('plguin_base_settings_' . __NAMESPACE__, __CLASS__ . '::display_backend_base_settings_adv');
			
			\add_action('plguin_help_settings_' . __NAMESPACE__, __CLASS__ . '::display_backend_help_setting');

			\add_action('admin_enqueue_scripts', __CLASS__ . '::backend_enqueue_scripts', 999);
		}
		
	}
	private static function tdomain(){
		\load_plugin_textdomain(
			__NAMESPACE__,
			false,
			\plugin_basename(__DIR__). '/languages'
		);
	}
	private static function get_header_translate($key = null){
		$trs = [
			'plugin_name' => __('SinaPic v2'),
			'plugin_uri' => __('http://inn-studio.com/sinapicv2'),
			'description' => __('Best Image Hosting Plugin for WP. Upload your picture to sina weibo and show it on your site.'),
			'author_uri' => __('http://inn-studio.com'),
		];
		if($key)
			return isset($trs[$key]) ? $trs[$key] : false;
		return $trs;
	}
	private static function update(){
		if(!class_exists('inc\updater')){
			include __DIR__ . '/inc/update.php';
		}
		$updater = new inc\updater();
		$updater->name = self::get_header_translate('plugin_name');
		$updater->dir = __DIR__;
		$updater->file = __FILE__;
		$updater->slug = __NAMESPACE__;
		$updater->checker_url = __('http://update.inn-studio.com') . '/?action=get_update&slug=' . __NAMESPACE__;
		$updater->init();
	}
	public static function is_admin(){
		static $cache = null;
		if($cache === null)
			$cache = (bool)\is_admin();
		return $cache;
	}
	/**
	 * set_options_authorize
	 *
	 * @return 
	 * @version 1.0.1
	 */
	private static function set_options_authorize($args){
		$args = array_merge([
			'access_token' => null,
			'expires_in' => null,
		],$args);
		plugin_options::set_options(self::$key_authorization,$args);
	}
	private static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = plugin_options::get_options();
		if($key)
			return isset($caches[$key]) ? $caches[$key] : false;
		return $caches;
	}
	/**
	 * Get sina image pattern
	 *
	 * @return string Pattern
	 * @version 1.0.0
	 */
	private static function get_sinaimg_pattern(){
		return '/\w+:\/\/\w+\.sinaimg\.cn\/\w+\/\w+\.\w+/i';
	}
	private static function get_localimg_pattern($with_http = false){
		$upload_dir = self::wp_upload_dir();
		$prefix = $with_http ? addcslashes($upload_dir['baseurl'] . self::$basedir_backup,'/.') : null;
		return '/' . $prefix . '[0-9]+\-\w+\-\w+\-\w+\.\w+/i';
	}
	/**
	 * Get sina image url by local image url
	 *
	 * @param string $localimg_url eg.http://xx.com/wp-content/uploads...xx.jpg
	 * @return string The sina image url
	 * @version 1.0.0
	 */
	private static function get_sinaimg_url_by_localimg($localimg_url){
		$upload_dir = self::wp_upload_dir();
		$upload_url = $upload_dir['baseurl'];
		if(stripos($localimg_url,$upload_url . self::$basedir_backup) === false)
			return false;
		$protocol  = self::get_options('is_ssl') ? 'https://' : 'http://';
		$subdowmain = self::get_local_file_meta('subdomain',$localimg_url);
		$size = self::get_local_file_meta('size',$localimg_url);
		$basename = self::get_local_file_meta('basename',$localimg_url);
		if(!$subdowmain || !$size || !$basename)
			return false;
		return $protocol . $subdowmain . '.sinaimg.cn/' . $size . '/' . $basename;
	}
	/**
	 * Get remote images url
	 *
	 * @param string $content Post content or post meta
	 * @return array Images url of array/An empty array
	 * @version 1.0.0
	 */
	private static function get_sinaimg_urls($content = null){
		if(!$content) 
			return false;
		preg_match_all(self::get_sinaimg_pattern(),$content,$matches);
		return $matches[0];
	}
	/**
	 * Get local image path by sina image url
	 *
	 * @param string $sinaimg_url Sina image url
	 * @param int $postid The post id of image
	 * @return string Local image url
	 * @version 1.0.0
	 */
	private static function get_localimg_path_by_sinaimg($sinaimg_url,$postid = null){
		if(stripos($sinaimg_url,'sinaimg.cn') === false)
			return false;
		if(!$postid){
			global $post;
			$postid = $post->ID;
		}
		$upload_dir = self::wp_upload_dir();
		$backup_dir = $upload_dir['basedir'] . self::$basedir_backup;
		$basename = implode('-',array(
			$postid,
			self::get_file_url_meta('subdomain',$sinaimg_url),
			self::get_file_url_meta('size',$sinaimg_url),
			self::get_file_url_meta('basename',$sinaimg_url)
		));
		$file_path = $backup_dir . $basename;
		return $file_path;
	}
	private static function get_size_url_by_size($url,$size){
		/** check is sinaimg.cn */
		if(stripos($url,'sinaimg.cn') !== false){
			$ori_size = self::get_file_url_meta('size',$url);
		}else{
			static $home_url;
			if(!$home_url)
				$home_url = \home_url();

			if(strpos($url,$home_url) === false)
				return $url;
			
			$ori_size = self::get_local_file_meta('size',$url);
		}
		if(!$ori_size) return $url;
		return str_replace($ori_size,$size,$url);
	}
	private static function wp_upload_dir(){
		static $cache = null;
		if($cache === null)
			$cache = \wp_upload_dir();
		return $cache;
	}
	/**
	 * Get local image url by sina image url
	 *
	 * @param string $sinaimg_url Sina image url
	 * @param int $postid The post id of image
	 * @return string Http image url
	 * @version 1.0.0
	 */
	private static function get_localimg_url_by_sinaimg($sinaimg_url,$postid = null){
		if(stripos($sinaimg_url,'sinaimg.cn') === false)
			return false;
		if(!$postid){
			global $post;
			$postid = $post->ID;
		}
		$upload_dir = self::wp_upload_dir();
		$backup_dir = $upload_dir['baseurl'] . self::$basedir_backup;
		$basename = implode('-',array(
			$postid,
			self::get_file_url_meta('subdomain',$sinaimg_url),
			self::get_file_url_meta('size',$sinaimg_url),
			self::get_file_url_meta('basename',$sinaimg_url)
		));
		$file_path = $backup_dir . $basename;
		return $file_path;
	}
	/**
	 * process
	 * 
	 * @params 
	 * @return 
	 * @version 1.0.1
	 */
	public static function process(){
		$output = [];
		/** 
		 * get action type
		 */
		$type = isset($_GET['type']) ? $_GET['type'] : null;
		/** 
		 * $options
		 */
		$options = self::get_options();
		/** 
		 * set timeout limit is 0
		 */
		@set_time_limit(0);
		switch($type){
			/** 
			 * set authorize
			 */
			case 'set_authorize':
				if(isset($_GET['access_token']) && isset($_GET['expires_in'])){
					$args = [
						'access_token' => $_GET['access_token'],
						'expires_in' => (int)$_GET['expires_in'],
						'access_time' => time(),
					];
					self::set_options_authorize($args);
					die(
					'
					<!doctype html>
					<html lang="en">
					<head>
						<meta charset="UTF-8">
						<title>' . __('Congratulation, SinaPicV2 has been authorized!') . '</title>
						<link rel="stylesheet" href="' . plugin_features::get_css('post-new',true) . '">
					</head>
					<body>
						' . plugin_functions::status_tip('success',__('Congratulation, SinaPicV2 has been authorized!') . '<br/><a href="javascript:window.open(false,\'_self\',false);window.close();">' . __('Close this window and reload the plugin UI.') . '</a>') . '
					</body>
					</html>
					'
					);
				}
				break;
			/** 
			 * check authorize
			 */
			case 'check_authorize':
				if(self::is_authorized()){
					$output['status'] = 'success';
					$output['msg'] = __('Authorized.');
				}else{
					$output['status'] = 'error';
					$output['msg'] = __('Unauthorize.');
				}
				break;
			/** 
			 * upload pic
			 */
			case 'upload':

				$file = isset($_FILES['file']) ? $_FILES['file'] : [];
				$file_name = isset($file['name']) ? $file['name'] : null;
				$file_type = isset($file['type']) ? explode('/',$file['type']) : []; 
				$file_type = !empty($file_type) ? $file_type[1] : null;
				$tmp_name = isset($file['tmp_name']) ? $file['tmp_name'] : null;
				/** 
				 * check upload error
				 */
				if(isset($file['error']) && $file['error'] != 0){
					$output['status'] = 'error';
					$output['msg'] = sprintf(__('Upload failed, file has an error code: %s'),$file['error']);
					$output['code'] = 'file_has_error_code';
					die(plugin_functions::json_format($output));
				}
				/** 
				 * check file params
				 */
				if(!$file_name || !$file_type || !$tmp_name){
					$output['status'] = 'error';
					$output['msg'] = __('Not enough params.');
					$output['code'] = 'not_enough_params';
					die(plugin_functions::json_format($output));
				}
				/** 
				 * check file type
				 */
				if(!in_array($file_type,self::$allow_types)){
					$output['status'] = 'error';
					$output['msg'] = __('Invalid file type.');
					$output['code'] = 'invalid_file_type';
					die(plugin_functions::json_format($output));
				}
				/** 
				 * check authorization
				 */
				if(!self::is_authorized()){
					$output['status'] = 'error';
					$output['code'] = 'no_authorize';
					$output['msg'] = __('Please use your Sina Weibo account to authorize the plugin.');
					die(plugin_functions::json_format($output));
				}
				$authorization = (array)self::get_options(self::$key_authorization);
				
				include __DIR__ . '/inc/saetv2.ex.class.php';
				
				$c = new inc\SaeTClientV2(self::get_config(0),self::get_config(1),$authorization['access_token']);
				$callback = $c->upload(\current_time('Y-m-d H:i:s ' . rand(100,999)) . __('Upload by Sinapicv2'),$tmp_name);
				
				unlink($tmp_name);

				/** 
				 * get callback
				 */
				if(is_array($callback) && isset($callback['bmiddle_pic'])){
					$output['status'] = 'success';
					$output['img_url'] = isset($options['is_ssl']) && $options['is_ssl'] == 1 ? str_ireplace('http://','https://',$callback['bmiddle_pic']) : $callback['bmiddle_pic'];
					/** 
					 * destroy after upload 
					 */
					if(isset($options['destroy_after_upload'])){
						sleep(1);
						$c->destroy($callback['id']);
					}
				/** 
				 * got callback error code
				 */
				}elseif(is_array($callback) && isset($callback['error_code'])){
					$output['status'] = 'error';
					$output['msg'] = $callback['error'];
				/** 
				 * unknown error
				 */
				}else{
					ob_start();
					var_dump($callback);
					$detail = ob_get_contents();
					ob_end_clean();
					
					$output['status'] = 'error';
					$output['code'] = 'unknown';
					$output['detail'] = $detail;
					$output['msg'] = sprintf(__('Sorry, upload failed. Please try again later or contact the plugin author. The reasons for this situation maybe the Weibo server does not receive the file from your server. Weibo returns an error message: %s'),json_encode($callback));
					// var_dump($callback);
					// die();
				}
			
				break;
			/** 
			 * get backup data
			 */
			case 'get_backup_data':
				if(!\current_user_can('manage_options')){
					$output['status'] = 'error';
					$output['code'] = 'error_permission';
					$output['msg'] = __('Security permission was insufficient to operate, please make sure you are administrator.');
					die(plugin_functions::json_format($output));
				}
				
				/** 
				 * get all post
				 */
				global $post;
				
				$posts = [];
				$query = new \WP_Query(array(
					'nopaging' => true,
					'post_type' => 'any',
					'ignore_sticky_posts' => true,
					
				));
				if($query->have_posts()){
					foreach($query->posts as $post){
						\setup_postdata($post);
						/** 
						 * match sina images from post content
						 */
						$urls = self::get_sinaimg_urls($post->post_content);
						/**
						 * get postmeta
						 */
						$post_meta = self::get_sinaimg_urls(get_post_meta($post->ID,self::get_custom_thumbnail_meta(),true));
						$post_meta = !empty($post_meta) ? $post_meta[0] : null;
						if(!empty($post_meta)){
							$urls[] = $post_meta;
						}
						
						if(!empty($urls)){
							$posts[] = array(
								'id' => $post->ID,
								'imgs' => array_unique($urls),
							);
						}
					}
					\wp_reset_postdata();
				}
				if(empty($posts)){
					$output['status'] = 'error';
					$output['code'] = 'no_post';
					$output['msg'] = __('No post can be match to backup.');
				}else{
					$output['status'] = 'success';
					$output['posts'] = $posts;
				}
				
				break;
			/** 
			 * download
			 */
			case 'download':
				if(!\current_user_can('manage_options')){
					$output['status'] = 'error';
					$output['code'] = 'error_permission';
					$output['msg'] = __('Security permission was insufficient to operate, please make sure you are administrator.');
					die(plugin_functions::json_format($output));
				}
				$post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : null;
				$file_url = isset($_GET['img_url']) ? $_GET['img_url'] : null;
				if(!$post_id){
					$output['status'] = 'error';
					$output['code'] = 'invalid_post_id';
					$output['msg'] = __('No found any post.');
					die(plugin_functions::json_format($output));
				}
				if(!$file_url){
					$output['status'] = 'error';
					$output['code'] = 'no_img';
					$output['msg'] = __('No any image to download');
					die(plugin_functions::json_format($output));
				}

				/** 
				 * $local_basename eg. 1-ww2-square-5dd1...0xck6d.jpg
				 */
				$file_path = self::get_localimg_path_by_sinaimg($file_url,$post_id);
				$sinaimg_basename = self::get_file_url_meta('basename',$file_url);
				/** 
				 * if file exists, skipped
				 */
				if(file_exists($file_path)){
					$output['msg'] = sprintf(__('The picture (%s) will be skipped because it already exists. '),'<a href="' . $file_url . '" target="_blank">' . $sinaimg_basename . '</a>');
					$output['skip'] = 1;
					$output['status'] = 'success';
					$output['code'] = 'go_next';
					die(plugin_functions::json_format($output));
				}
				$result = self::httpcopy($file_url,$file_path);
				// var_dump($result);exit;
				if($result){
					$output['status'] = 'success';
					$output['code'] = 'go_next';
					$output['msg'] = sprintf(__('The picture (%s) downloaded, continue to download next picture... '),'<a href="' . $file_url . '" target="_blank">' . $sinaimg_basename . '</a>');
				}else{
					$output['status'] = 'error';
					$output['code'] = 'no_found';
					$output['msg'] = sprintf(__('No found the picture (%s) from server, it will be skipped and continue to download next picture... '),'<a href="' . $file_url . '" target="_blank">' . $sinaimg_basename . '</a>');
				}

				break;
			/** 
			 * restore sina to local
			 */
			case 'restore-sina-to-local':
				if(!\current_user_can('manage_options')){
					$output['status'] = 'error';
					$output['code'] = 'error_permission';
					$output['msg'] = __('Security permission was insufficient to operate, please make sure you are administrator.');
					die(plugin_functions::json_format($output));
				}
				/** 
				 * if have not any post
				 */
				if(!self::get_backup_files()){
					$output['status'] = 'error';
					$output['code'] = 'no_backup_data';
					$output['msg'] = __('Can not find any backup data from local. Perhaps you need to backup data first. Restoration has been canceled.');
					die(plugin_functions::json_format($output));
				}
				global $post;
				$query = new \WP_Query(array(
					'nopaging' => true,
					'post_type' => 'any',
					'ignore_sticky_posts' => true,
					'post__in' => self::get_backup_files('post_id'),
				));
				if($query->have_posts()){
					foreach($query->posts as $post){
						\setup_postdata($post);
						$sinaimg_urls = self::get_sinaimg_urls($post->post_content);
						$localimg_urls = array_map('self::get_localimg_url_by_sinaimg',$sinaimg_urls);
						$new_post_content = str_ireplace($sinaimg_urls,$localimg_urls,$post->post_content);
						/**
						 * update postmeta
						 */
						$post_meta = \get_post_meta($post->ID,self::get_custom_thumbnail_meta(),true);
						if(!empty($post_meta)){
							$post_meta = self::get_localimg_url_by_sinaimg($post_meta);
							if(!empty($post_meta))
								\update_post_meta($post->ID,self::get_custom_thumbnail_meta(),$post_meta);
						}
						/** 
						 * update post
						 */
						\wp_update_post(array(
							'ID' => $post->ID,
							'post_content' => $new_post_content
						));
					}
					\wp_reset_postdata();
					$output['status'] = 'success';
					$output['msg'] = __('Congratulation, all images have been restored to your wordpress server.');
				}else{
					$output['status'] = 'error';
					$output['code'] = 'no_match_posts';
					$output['msg'] = __('Unable to match any post by backup data. Perhaps your backup data has expired, please redo backup operation.');
				}
				
				break;
			/** 
			 * restore-local-to-sina
			 */
			case 'restore-local-to-sina':
				if(!current_user_can('manage_options')){
					$output['status'] = 'error';
					$output['code'] = 'error_permission';
					$output['msg'] = __('Security permission was insufficient to operate, please make sure you are administrator.');
					die(plugin_functions::json_format($output));
				}
				if(!self::get_backup_files('post_id')){
					$output['status'] = 'error';
					$output['code'] = 'no_backup_data';
					$output['msg'] = __('Can not find any backup data from local. Perhaps you need to backup data first. Restoration has been canceled.');
					die(plugin_functions::json_format($output));
				}
				/** 
				 * get all post
				 */
				global $post;
				$posts = [];
				$query = new \WP_Query(array(
					'nopaging' => true,
					'post_type' => 'any',
					'ignore_sticky_posts' => true,
					'post__in' => self::get_backup_files('post_id'),
					
				));
				if($query->have_posts()){
					foreach($query->posts as $post){
						\setup_postdata($post);
						/** 
						 * match sina images from post content
						 */
						$localimg_urls = self::get_localimg_urls_by_content($post->post_content);
												
						$sinaimg_urls = array_map('self::get_sinaimg_url_by_localimg',$localimg_urls);
						if(!empty($localimg_urls)){
							$new_post_content = str_ireplace(
								$localimg_urls,
								$sinaimg_urls,
								$post->post_content
							);
							\wp_update_post(array(
								'ID' => $post->ID,
								'post_content' => $new_post_content
							));
						}
						/**
						 * update postmeta
						 */
						$post_meta = \get_post_meta($post->ID,self::get_custom_thumbnail_meta(),true);
						if(!empty($post_meta)){
							$post_meta = self::get_sinaimg_url_by_localimg($post_meta);
							if(!empty($post_meta)){
								\update_post_meta($post->ID,self::get_custom_thumbnail_meta(),$post_meta);
							}
						}
						
					}
					$output['status'] = 'success';
					$output['msg'] = __('Congratulation, all images have been restored to weibo server.');
					\wp_reset_postdata();
				}else{
					$output['status'] = 'error';
					$output['code'] = 'no_match_posts';
					$output['msg'] = __('Unable to match any post by backup data. Perhaps your backup data has expired, please redo backup operation.');
				}
				
				break;
			default:
				$output['status'] = 'error';
				$output['code'] = 'invalid_param';
				$output['msg'] = __('Invalid param.');
		}
		die(plugin_functions::json_format($output));
	}
	/**
	 * get_config
	 *
	 * @param 
	 * @return 
	 * @version 1.0.0
	 */
	public static function get_config($key){
		return plugin_functions::authcode(self::$wb[$key]);
	}
	private static function httpcopy($url,$file, $timeout=60) {
		$dir = pathinfo($file,PATHINFO_DIRNAME);
		!is_dir($dir) && \wp_mkdir_p($dir);
		$url = str_replace(" ","%20",$url);

		if(function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			if (version_compare(phpversion(), '5.4.0', '<')) {
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
			} else {
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			}
			
			ob_start();
			curl_exec($ch);
			$temp = ob_get_contents();
			ob_end_clean();
			
			if(!curl_error($ch) && file_put_contents($file, $temp)){
				return $file;
			} else {
				return false;
			}
		} else {
			$opts = array(
				"http"=>array(
				"method"=>"GET",
				"header"=>"",
				"timeout"=>$timeout)
			);
			$context = stream_context_create($opts);
			if(@copy($url, $file, $context)) {
				//$http_response_header
				return $file;
			} else {
				return false;
			}
		}
	}

	/**
	 * get_file_url_meta
	 * 
	 * @param string $key
	 * @param string $file_url
	 * @return string image size
	 * @version 1.0.1
	 * @example 
	 * @copyright Copyright (c) 2011-2013 INN STUDIO. (http://www.inn-studio.com)
	 **/
	private static function get_file_url_meta($key = nulll,$file_url = null){
		$file_url = $file_url ? $file_url : self::$file_url;
		
		if(!$key || stripos($file_url,'sinaimg.cn') === false || !$file_url) 
			return false;
		$file_obj = explode('/',$file_url);
		$len = count($file_obj);
		/** 
		 * file eg. http://ww2.sinaimg.cn/square/5dd1e978jw1eo083cx0sgj218g0xck6d.jpg
		 */
		switch($key){
			/** 
			 * basename eg. 5dd1e978jw1eo083cx0sgj218g0xck6d.jpg
			 */
			case 'basename':
				$return = $file_obj[$len - 1];
				break;
			/**
			 * size eg. square
			 */
			case 'size':
				$return = $file_obj[$len - 2];
				break;
			/**
			 * id/filename eg. 5dd1e978jw1eo083cx0sgj218g0xck6d
			 */
			case 'id':
			case 'filename':
				$id = explode('.',$file_obj[$len - 1]);/** fuckyou php53 */
				$return = $id[0];
				break;
			/** 
			 * ext eg. jpg
			 */
			case 'ext':
				$ext = explode('.',$file_obj[$len - 1]);/** fuckyou php53 */
				$return = $ext[1];
				break;
			/** 
			 * domain eg. ww2
			 */
			case 'subdomain':
				$return = $file_obj[$len - 3];
				$return = explode('.',$return);/** fucku php53 */
				$return = $return[0];
				break;
			default:
				return false;

		}
		return $return;
	}

	/**
	 * Get local file meta
	 *
	 * @param string $basename The local file basename. eg. 1-ww2-square-xxx.jpg
	 * @return string
	 * @version 1.0.1
	 */
	private static function get_local_file_meta($type,$filename){
		$basename = basename($filename);
		$basename_obj = explode('-',$basename);
		switch($type){
			/** 
			 * post_id eg. 1
			 */
			case 'post_id':
			case 'postid':
				return isset($basename_obj[0]) ? $basename_obj[0] : null;
			/** 
			 * domain
			 */
			case 'subdomain':
				return isset($basename_obj[1]) ? $basename_obj[1] : null;
			/** 
			 * size
			 */
			case 'size':
				return isset($basename_obj[2]) ? $basename_obj[2] : null;
			/** 
			 * basename eg. xxx.jpg
			 */
			case 'basename':
				return isset($basename_obj[3]) ? $basename_obj[3] : null;
			/** 
			 * id/filename eg. xxx
			 */
			case 'id':
				$return = explode('.',$basename_obj[3]);
				return isset($return[0]) ? $return[0] : null;
			/** 
			 * ext
			 */
			case 'ext':
				$return = explode('.',$ar[3]);
				return isset($return[1]) ? $return[1] : null;
			default:
				return false;
		}
	}
	/**
	 * Get has been backed up files
	 *
	 * @param string $type File meta type
	 * @return array 
	 * @version 1.0.0
	 */
	private static function get_backup_files($type = null){
		/** 
		 * @see https://codex.wordpress.org/Function_Reference/wp_upload_dir
		 */
		$upload_dir = self::wp_upload_dir();
		$backup_dir = $upload_dir['basedir'] .  self::$basedir_backup;
		if(empty(self::$cache_backup_files)){
			self::$cache_backup_files = glob($backup_dir . '*');
		}
		if(empty(self::$cache_backup_files)){
			return false;
		}else{
			self::$cache_backup_files = array_unique(self::$cache_backup_files);
		}
		
		$returns = [];
		foreach(self::$cache_backup_files as $img_path){
			if(is_file($img_path)){
				$returns[] = $type ? self::get_local_file_meta($type,$img_path) : $img_path;
			}
		}
		return $returns;

	}
	private static function get_localimg_urls_by_content($content){
		/** 
		 * with http
		 */
		preg_match_all(self::get_localimg_pattern(true),$content,$matches);
		return $matches[0];
	}

	public static function options_default($options){
		$options['feature_meta'] = self::$thumbnail_external_key;
		$options['is_ssl'] = \is_ssl() ? 1 : 0;
		$options['thumbnail-size'] = 'thumb150';
		return $options;
	}
	/**
	 * backend_options_save
	 * 
	 * @params array options
	 * @return array options
	 * @version 1.0.2
	 */
	public static function backend_options_save(array $options = []){
		if(!isset($_POST[__NAMESPACE__])) 
			return $options;

		$authorization = (array)self::get_options(self::$key_authorization);
		
		$options = (array)$_POST[__NAMESPACE__];
		/** 
		 * is ssl
		 */
		$options['is_ssl'] = isset($options['is_ssl']) ? $options['is_ssl'] : 0;
		
		$options['feature_meta'] = isset($_POST[__NAMESPACE__]['feature_meta']) && trim($_POST[__NAMESPACE__]['feature_meta']) !== '' ? trim($_POST[__NAMESPACE__]['feature_meta']) : self::$thumbnail_external_key;
		$new_meta = $options['feature_meta'];
		$old_thumbnail_size = $options['old-thumbnail-size'];
		$new_thumbnail_size = $options['thumbnail-size'];
		
		global $post;
		/**
		 * check the new and old meta key from $_POST
		 */
		
		$old_meta = $options['old_meta'];
		if($old_meta !== $new_meta){
			/**
			 * update the old meta key
			 */
			$query = new \WP_Query(array(
				'meta_key' => $old_meta,
			));
			if($query->have_posts()){
				foreach($query->posts as $post){
					$meta_v = \get_post_meta($post->ID,$old_meta,true);
					\delete_post_meta($post->ID,$old_meta);
					\add_post_meta($post->ID,$new_meta,$meta_v);
				}
				\wp_reset_postdata();
			}
		}
		/**
		 * update thumnail size
		 */
		if($old_thumbnail_size !== $new_thumbnail_size){
			$query = new \WP_Query(array(
				'meta_key' => $new_meta,
			));
			if($query->have_posts()){
				foreach($query->posts as $post){
					$old_thumbnail_url = \get_post_meta($post->ID,$new_meta,true);
					if(!empty($old_thumbnail_meta)){
						\update_post_meta($post->ID,$new_meta,self::get_size_url_by_size($old_thumbnail_url,$new_thumbnail_size));
					}
				}
				\wp_reset_postdata();
			}
		}
		$options[self::$key_authorization] = $authorization;
		return $options;
	}
	public static function display_backend_base_settings_base(){
		$options = self::get_options();
		/** 
		 * authorize
		 */
		if(self::is_authorized()){
			$authorization = self::get_options(self::$key_authorization);
			$auth_info = date('Y-m-d',(int)$authorization['access_time'] + (int)$authorization['expires_in']);
		}else{
			$auth_info = __('Unauthorize');
		}
		$auth_link = self::get_authorize_uri();
		?>
		<fieldset>
			<legend><i class="fa fa-fw fa-key"></i> <?= __('Authorization information');?></legend>
			<table class="form-table">
				<tbody>
					<tr>
						<th><?= __('Authorization expires time');?></th>
						<td>
							<?= $auth_info;?>
						</td>
					</tr>
					<tr>
						<th><?= __('Authorization Link');?></th>
						<td>
							<a href="<?= $auth_link;?>" target="_blank" ><?= __('Click here to authorize');?></a>
						</td>
					</tr>
				</tbody>
			</table>				
		</fieldset>
		<?php

		$checked_is_ssl = isset($options['is_ssl']) && $options['is_ssl'] == 1 ? ' checked ' : null;
		?>
		<fieldset>
			<legend><i class="fa fa-fw fa-wrench"></i> <?= __('Plugin settings');?></legend>
			<p class="description">
				<?= __('If your theme supports feature thumbnail form post meta and you want to use it, please tell the Sinapicv2 what is the post meta name(key). Fill in the text area.');?>
			</p>
			<table class="form-table">
				<tbody>
					<tr>
						<th><label for="<?= __NAMESPACE__;?>-thumbnail-size"><?= __('Custom thumbnail size');?></label></th>
						<td>
							<?php
							$thumbnail_size_selected = isset($options['thumbnail-size']) ? $options['thumbnail-size'] : 'thumb150';
							?>
							<select name="<?= __NAMESPACE__;?>[thumbnail-size]" id="<?= __NAMESPACE__;?>-thumbnail-size">
								<?php foreach(self::get_sizes() as $k=>$v){
								if($thumbnail_size_selected === $k){
									$selected = ' selected ';
								}else{
									$selected = null;
								}
								?>
									<option value="<?= $k;?>" <?= $selected;?> ><?= $k,' - ',$v;?></option>
								<?php } ?>
							</select>
							<input type="hidden" name="<?= __NAMESPACE__;?>[old-thumbnail-size]" value="<?= $options['thumbnail-size'];?>">
						</td>
					</tr>
					<tr>
						<th><label for="<?= __NAMESPACE__;?>-is-ssl"><?= __('Enable SSL (https://) image address');?></label></th>
						<td>
							<label for="<?= __NAMESPACE__;?>-is-ssl"><input type="checkbox" name="<?= __NAMESPACE__;?>[is_ssl]" id="<?= __NAMESPACE__;?>-is-ssl" value="1" <?= $checked_is_ssl;?>/> <?= __('Enabled');?></label>
						</td>
					</tr>
					<tr>
						<th><label for="thumbnail-external-url"><?= __('Feature thumbnail meta name: ');?></label></th>
						<td>
							<input id="thumbnail-external-url" name="<?= __NAMESPACE__;?>[feature_meta]" type="text" class="regular-text" value="<?= self::get_custom_thumbnail_meta();?>"/>
							<input type="hidden" name="<?= __NAMESPACE__;?>[old_meta]" value="<?= self::get_custom_thumbnail_meta();?>"/>
							<span class="description"><?= __('Default: ');?> <?= self::$thumbnail_external_key;?></span>
						</td>
					</tr>
					<tr>
						<th><label for="<?= __NAMESPACE__;?>-img-title-enabled"><?= __('Image title attribute');?></label></th>
						<td>
							
							<?php
							$checked_img_title_enabled = isset($options['img-title-enabled']) ? ' checked ' : null;
							?>
							<label for="<?= __CLASS__;?>-img-title-enabled">
								<input type="checkbox" name="<?= __NAMESPACE__;?>[img-title-enabled]" id="<?= __NAMESPACE__;?>-img-title-enabled" <?= $checked_img_title_enabled;?>/>
								<?= __('Display image title attribute as same as alt attribute.');?>
							</label>
						</td>
					</tr>
					<tr>
						<th><label for="<?= __NAMESPACE__;?>-destroy-after-upload"><?= __('Delete after upload');?></label></th>
						<td>
							
							<?php
							$destroy_after_upload_checkbox = isset($options['destroy_after_upload']) ? ' checked ' : null;
							?>
							<label for="<?= __NAMESPACE__;?>-destroy-after-upload">
								<input type="checkbox" name="<?= __NAMESPACE__;?>[destroy_after_upload]" id="<?= __NAMESPACE__;?>-destroy-after-upload" <?= $destroy_after_upload_checkbox;?>/>
								<?= __('After upload a message and it will be destroy if enable');?>
							</label>
						</td>
					</tr>
				</tbody>
			</table>						
		</fieldset>
		<?php
	}
	public static function display_backend_base_settings_adv(){
		$auto_backup = isset($options['auto_backup']) ? ' checked="checked" ' : null;
		
		$posts_count = \wp_count_posts();
		$backup_dir = self::wp_upload_dir();
		$backup_dir = $backup_dir['basedir'] . self::$basedir_backup;

		?>
		<fieldset>
			<legend><i class="fa fa-fw fa-exchange"></i> <?= __('Backup & bestore');?></legend>
			<p class="description"><?= __('With version 2.0.0 or higher, sinapicv2 supports to backup sina image form all posts.');?></p>
			<p class="description"><?= sprintf(__('The backup pictures will be saved to %s in your host.'),'<strong  style="cursor:pointer;" onclick="this.innerHTML=this.getAttribute(\'data-dir\');" data-dir="' . $backup_dir . '">' . __('Click to view') . '</strong>');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th>
							<p><?= __('Backup pictures: ');?></p>
							<p><?= __('Pictures server &rarr; my space');?></p>
						</th>
						<td>
							<div id="sinapicv2-backup-area">
								
								<div id="sinapicv2-backup-progress" class="sinapicv2-progress">
									<div id="sinapicv2-backup-tip" class="hide"></div>
									<div id="sinapicv2-backup-progress-bar" class="sinapicv2-progress-bar"></div>
								</div>
								
								<p id="sinapicv2-backup-btns"><a href="javascript:;" id="sinapicv2-backup-btn" class="button-primary"><i class="fa fa-cloud-download"></i> <?= __('click to start BACKUP');?></a></p>
							</div>
							<p>
								<?= sprintf(__('Backup operation will search all sina images and downloads them to backup folder from your publish about %s posts.'),'<strong>' . $posts_count->publish . '</strong>');?>
							</p>
							<p>
								<i class="fa fa-exclamation-circle"></i> <?= __('Attention: you need DO THIS backup operation in first time.');?>
							</p>
						</td>
					</tr>
					
					<tr>
						<th>
							<p><?= __('Restore pictures:');?></p>
							<p><?= sprintf(__('Pictures server %s my space: '),'<i class="fa fa-fw fa-exchange"></i>');?></p>
						</th>
						<td>
							<div id="sinapic_restore_area">
								<div id="sinapicv2-restore-progress" class="sinapicv2-progress">
									<div id="sinapicv2-restore-tip" class="hide"></div>
									<div id="sinapicv2-restore-progress-bar" class="sinapicv2-progress-bar"></div>
								</div>
								<p id="sinapicv2-restore-btns">
									<a href="javascript:;" id="sinapicv2-restore-server-to-host-btn"  class="button">
										<?= sprintf(__('%1$s Server to %2$s %3$s My space'),'<i class="fa fa-cloud"></i>','<i class="fa fa-long-arrow-right"></i>','<i class="fa fa-wordpress"></i>');?>
									</a>
									
									<a href="javascript:;" id="sinapicv2-restore-host-to-server-btn"  class="button">
										<?= sprintf(__('%1$s My space to %2$s %3$s Server'),'<i class="fa fa-wordpress"></i>','<i class="fa fa-cloud"></i>','<i class="fa fa-long-arrow-right"></i>','<i class="fa fa-cloud"></i>');?>
									</a>
									
								</p>
							</div>
							<p>
								<?= __('Server to my space: all weibo server picture addresses replace to your space pictur addresses.');?>
							</p>
							<p>
								<?= __('My space to server: all your space picture addresses replace to weibo server picture addresses.');?>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function post_new_enqueue_scripts(){
		if(\get_current_screen()->base !== 'post')
			return;
		/**
		 * css
		 */
		$css = [
			'backend' => [
				'deps' => [],
				'url' =>  plugin_features::get_css('post-new'),
			],
			'awesome' => [
				'deps' => [],
				'url' => '//cdn.bootcss.com/font-awesome/4.4.0/css/font-awesome.min.css',
				'version' => null,
			],
		];
		foreach($css as $k => $v){
			\wp_enqueue_style(
				$k,
				$v['url'],
				isset($v['deps']) ? $v['deps'] : [],
				plugin_features::get_plugin_data('Version')
			);
		}
		/**
		 * js
		 */
		$js = [
			'backend' => [
				'deps' => [],
				'url' => plugin_features::get_js('post-new-entry'),
			],
			
		];
		foreach($js as $k => $v){
			\wp_enqueue_script(
				$k,
				$v['url'],
				isset($v['deps']) ? $v['deps'] : [],
				plugin_features::get_plugin_data('Version'),
				true
			);
		}
	}
	public static function backend_enqueue_scripts(){
		if(!plugin_options::is_options_page())
			return;
		/**
		 * css
		 */
		$css = [
			'backend' => [
				'url' =>  plugin_features::get_css('backend'),
			],
			'awesome' => [
				'deps' => [],
				'url' => '//cdn.bootcss.com/font-awesome/4.4.0/css/font-awesome.min.css',
				'version' => null,
			],
		];
		foreach($css as $k => $v){
			\wp_enqueue_style(
				$k,
				$v['url'],
				isset($v['deps']) ? $v['deps'] : [],
				plugin_features::get_plugin_data('Version')
			);
		}
		/**
		 * js
		 */
		$js = [
			'backend' => [
				'deps' => [],
				'url' => plugin_features::get_js('backend-entry'),
			],
			
		];
		foreach($js as $k => $v){
			\wp_enqueue_script(
				$k,
				$v['url'],
				isset($v['deps']) ? $v['deps'] : [],
				plugin_features::get_plugin_data('Version'),
				true
			);
		}
	}
	public static function backend_js_config(array $config){
		if(!plugin_options::is_options_page())
			return $config;
		$config = [
			'lang' => [
				'M01' => __('Getting backup config data, please wait... '),
				'M02' => __('Current processing: '),
				'M03' => __('Downloading, you can restore the pictures to post after the download is complete.'),
				'M04' => __('Download completed, you can perform a restore operation.'),
				'M05' => __('Current file has been downloaded, skipping it.'),
				'M06' => __('The data is being restored , please wait...'),
				
				'E01' => __('Error code: '),
				'E02' => __('Program error, can not continue to operate. Please try again or contact author.'),
			],
			'process_url' => plugin_features::get_process_url([
				'action' => __NAMESPACE__,
			]),
		];
		return $config;
	}
	public static function display_backend_help_setting(){
		$plugin_data = plugin_features::get_plugin_data();
		?>
		<fieldset>
			<legend><i class="fa fa-fw fa-info-circle"></i> <?= __('Plugin Information');?></legend>
			<table class="form-table">
				<tbody>
					<tr>
						<th><?= __('Plugin name: ');?></th>
						<td>
							<strong><?= $plugin_data['Name'];?></strong>
						</td>
					</tr>
					<tr>
						<th><?= __('Plugin version: ');?></th>
						<td>
							<?= $plugin_data['Version'];?>
						</td>
					</tr>
					<tr>
						<th><?= __('Plugin description: ');?></th>
						<td>
							<?= $plugin_data['Description'];?>
						</td>
					</tr>
					<tr>
						<th><?= __('Plugin home page: ');?></th>
						<td>
							<a href="<?= $plugin_data['PluginURI'];?>" target="_blank"><?= $plugin_data['PluginURI'];?></a>
						</td>
					</tr>
					<tr>
						<th><?= __('Author home page: ');?></th>
						<td>
							<a href="<?= $plugin_data['AuthorURI'];?>" target="_blank"><?= $plugin_data['AuthorURI'];?></a>
						</td>
					</tr>
					<tr>
						<th scope="row"><?= __('Feedback and technical support: ');?></th>
						<td>
							<p><?= __('E-Mail: ');?><a href="mailto:kmvan.com@gmail.com">kmvan.com@gmail.com</a></p>
							<p>
								<?= __('QQ (for Chinese users): ');?><a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=272778765&site=qq&menu=yes">272778765</a>
							</p>
							<p>
								<?= __('QQ Group (for Chinese users):');?>
								<a href="http://wp.qq.com/wpa/qunwpa?idkey=d8c2be0e6c2e4b7dd2c0ff08d6198b618156d2357d12ab5dfbf6e5872f34a499" target="_blank">170306005</a>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?= __('Donate a coffee: ');?></th>
						<td>
							<p>
								<!-- paypal -->
								<a data-item-name="<?= plugin_features::get_plugin_data('Name');?>" id="paypal_donate" href="javascript:;" title="<?= __('Donation by Paypal');?>">
									<img src="//ww2.sinaimg.cn/large/686ee05djw1ella1kv74cj202o011wea.jpg" alt="<?= __('Donation by Paypal');?>" width="96" height="37"/>
								</a>
								<!-- alipay -->
								<a id="alipay_donate" target="_blank" href="http://ww3.sinaimg.cn/mw600/686ee05djw1eihtkzlg6mj216y16ydll.jpg" title="<?= __('Donation by Alipay');?>">
									<img width="96" height="37" src="//ww1.sinaimg.cn/large/686ee05djw1ellabpq9euj202o011dfm.jpg" alt="<?= __('Donation by Alipay');?>"/>
								</a>
								<!-- wechat -->
								<a id="wechat_donate" target="_blank" href="http://ww4.sinaimg.cn/mw600/686ee05djw1exukpkk4fwj20fr0f940r.jpg" title="<?= __('Donation by Wechat');?>">
									<img width="96" height="37" src="//ww3.sinaimg.cn/large/686ee05djw1exul2142tvj202o0113ya.jpg" alt="<?= __('Donation by Wechat');?>"/>
								</a>
							</p>
						</td>
					</tr>
				</tbody>
			</table>		
		</fieldset>
		<?php
	}
	/**
	 * meta_box_add
	 * 
	 * @return n/a
	 * @version 1.0.0
	 */
	public static function meta_box_add(){
		$screens = [ 'post', 'page' ];
		$des_array = [
			__('The best image host plugin for WP, do you agree?'),
			__('This is an artwork, no only plugin'),
			__('Cabbage and salted fish is plugin author\'s lunch'),
			__('Cabbage and salted fish is plugin author\'s dinner'),
			__('Beskfast is not a part of plugin author'),
			__('Bad weather, but did not affect my mood.'),
			__('This artwork is part of the world'),
			__('If you are in trouble, please look at Bear Grylls, my trouble is not trouble'),
			__('Do you know? SinaPicV2 has a sister: SinaPic-ext')
		];
		$rand_des = $des_array[rand(0,count($des_array) - 1)];
		foreach($screens as $screen){
			\add_meta_box(
				__NAMESPACE__,
				__('Sinapic v2') . '<span style="font-weight:normal;"> - ' . $rand_des . '</span>',
				__CLASS__ . '::meta_box_display',
				$screen
			);
			/**
			 * add for thumbnail
			 */
			\add_meta_box(
				__NAMESPACE__ . '-thumbnail',
				__('Sinapic v2 - Thumbnail setting'),
				__CLASS__ . '::meta_box_display_thumbnail',
				$screen,
				'side'
			);
		}
	}
	/**
	 * meta_box_save
	 * 
	 * @params int $post_id
	 * @return n/a
	 * @version 1.0.1
	 */
	public static function meta_box_save($post_id){
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE || !current_user_can('edit_post',$post_id)) 
			return;
		$post_meta = isset($_POST[__NAMESPACE__]) ? $_POST[__NAMESPACE__] : null;
		
		if(!$post_meta) 
			return;
			
		$custom_thumbnail_meta = self::get_custom_thumbnail_meta();
		
		if(!empty($post_meta['thumbnail-url'])){
			\update_post_meta($post_id,$custom_thumbnail_meta,$post_meta['thumbnail-url']);
		}else{
			\delete_post_meta($post_id,$custom_thumbnail_meta);
		}

	}

	public static function filter_get_post_metadata( $content, $object_id, $meta_key, $single){
		if($meta_key !== '_thumbnail_id')
			return $content;

		$url = self::get_post_thumbnail_url($object_id,$single);
		return $url ? $url : $content;
	}
	public static function get_post_thumbnail_url($post_id,$single){
		static $caches = [];
		if(isset($caches[$post_id]))
			return $caches[$post_id];
			
		$caches[$post_id] = \get_post_meta($post_id,self::get_custom_thumbnail_meta(),$single);

		return $caches[$post_id];
	}
	public static function get_custom_thumbnail_meta(){
		return self::get_options('feature_meta');
	}
	public static function get_custom_thumbnail_size(){
		return self::get_options('thumbnail-size');
	}
	public static function filter_post_thumbnail_html($html, $post_id, $post_thumbnail_id, $size, $attr){

		$custom_url = self::get_post_thumbnail_url($post_id,true);
		
		if(empty($custom_url) )
			return $html;

		$post_title = \esc_html(get_the_title($post_id));
		
		$custom_url = self::get_size_url_by_size($custom_url,self::get_custom_thumbnail_size());

		return '<img src="' . $custom_url . '" alt="' . $post_title . '" title="' . $post_title . '" class="post-thumbnail">';

		
	}
	/**
	 * get_authorize_uri
	 *
	 * @return string
	 * @version 1.0.0
	 */
	private static function get_authorize_uri(){
		$authorize_uri_obj = array(
			'uri' => plugin_features::get_process_url([
				'action' => __NAMESPACE__,
				'type' => 'set_authorize'
			])
		);
		$authorize_uri = self::get_config(2) . http_build_query($authorize_uri_obj);	
		return $authorize_uri;
	}
	public static function get_max_upload_size(){
		$max_mb = ini_get('upload_max_filesize');
		if(stripos($max_mb,'m') === false){
			/** 
			 * 1024*2048
			 */
			return 2097152;
		}else{
			return 1048576 * (int)$max_mb;
		}
	}
	public static function get_sizes($key = null){
		$sizes = array(
			'thumb150' 	=> __('max 150x150, crop'),
			'thumb300' 	=> __('max 300x300, crop'),
			'mw600' 	=> __('max-width:600'),
			'large' 	=> __('original size'),
			'square' 	=> __('max-width:80 or max-height:80'),
			'thumbnail' => __('max-width:120 or max-height:120'),
			'bmiddle' 	=> __('max-width:440')
		);
		if(!empty($key)){
			return isset($sizes[$key]) ? $sizes[$key] : null;
		}
		return $sizes;
	}
	
	/**
	 * meta_box_display
	 * 
	 * @return string HTML
	 * @version 1.0.2
	 */
	public static function admin_footer_post_new(){
		if(\get_current_screen()->base !== 'post')
			return;
		global $post;
		$options = self::get_options();
		/** 
		 * authorize_uri
		 */
		$config = [
			'process_url' => plugin_features::get_process_url([
				'action' => __NAMESPACE__,
				'post_id' => $post->ID,
			]),
			'lang' => [
				'M01' => __('Uploading {0}/{1}, please wait...'),
				'M02' => __('{0} files have been uploaded, enjoy it.'),
				'M03' => __('Image URL: '),
				'M04' => __('ALT attribute: '),
				'M05' => __('Set ALT attribute text'),
				'M06' => __('Control: '),
				'M07' => __('Insert to post with link'),
				'M08' => __('Insert to post image only'),
				'M09' => __('As custom meta feature image'),
				
				'E01' => __('Error: '),
				'E02' => __('Upload failed, please try again. If you still failed, please contact the plugin author.'),
				'E03' => __('Sorry, plugin can not get authorized data, please try again later or contact plugin author.'),
			],
			'authorized' => self::is_authorized(),
			'show_title' => isset($options['img-title-enabled']),
			'max_upload_size' => self::get_max_upload_size(),
			'thumbnail_size' => $options['thumbnail-size'],
			'sizes' => self::get_sizes(),
		];
		?>

		<script>
		window.PLUGIN_CONFIG_<?= __NAMESPACE__;?> = <?= json_encode($config);?>;
		</script>
		<?php
	}
	public static function meta_box_display(){
		$authorize_uri = self::get_authorize_uri();
		?>
		<div id="sinapicv2-container">
			<div id="sinapicv2-area-upload">
				<div id="sinapicv2-loading-tip">
					<img src="<?= plugin_features::admin_url('images/spinner-2x.gif');?>" width="40" height="40" alt="Loading">
				</div>
				<div id="sinapicv2-unauthorize">
					<?php if(\current_user_can('manage_options')){ ?>
						<?= plugin_functions::status_tip('info',sprintf(__('Sorry, Sinapicv2 needs to authorize from your Weibo account, <a href="%s"  id="sinapicv2-go-authorize" target="_blank"><strong>please click here to authorize</strong></a>.<br/>If you has authorized just now, <a href="javascript:;" id="sinapicv2-reloadme"><strong>please click here to reload me</strong></a>.'),$authorize_uri));?>
					<?php }else{ ?>
						<?= plugin_functions::status_tip('info',__('Sorry, Sinapicv2 needs to authorize from administrator. Please contact the administrator to authorize plugin.'));?>
					<?php } ?>
				</div>
				<div id="sinapicv2-progress"><div id="sinapicv2-progress-tx"></div><div id="sinapicv2-progress-bar"></div></div>
				<div class="button-primary" id="sinapicv2-add">
					<i class="fa fa-image"></i> 
					<?= __('Select or Drag picture(s) to upload');?>
					<input type="file" id="sinapicv2-file" accept="image/gif,image/jpeg,image/png" multiple="true" />
				</div>
				<div id="sinapicv2-completion-tip"></div>
				<div id="sinapicv2-error-file-tip">
					<span class="des"><?= __('Detects that files can not be uploaded:');?></span>
					<span id="sinapicv2-error-files"></span>
				</div>
				<div id="sinapicv2-tools">
					<a id="sinapicv2-insert-list-with-link" href="javascript:;" class="button button-primary"><?= __('Insert to post from list with link');?></a>
					<a id="sinapicv2-insert-list-without-link" href="javascript:;" class="button"><?= __('Insert to post from list without link');?></a>
					
					<select id="sinapicv2-split">
						<option value="0"><?= __('Do not use separate');?></option>
						<?php for($i = 1; $i<= 10; ++$i){ ?>
							<option value="<?= $i;?>"><?= sprintf(__('%d pictrue(s) / page'),$i);?></option>
						<?php } ?>
					</select>
					
					<a href="javascript:;" id="sinapicv2-clear-list"><?= __('Clear list');?></a>
				</div>
			</div>
			<div id="sinapicv2-tpl-container"></div>
		</div>
	<?php
	}
	public static function meta_box_display_thumbnail(){
		global $post;
		$thumbnail_url = \get_post_meta($post->ID,self::get_custom_thumbnail_meta(),true);

		?>
		<div id="sinapicv2-thumbnail-container">
			<div id="<?= __NAMESPACE__;?>-thumbnail-tip" <?= empty($thumbnail_url) ? null : 'hidden';?>><?= plugin_functions::status_tip('info',__('No set custom thumbnail yet.'));?></div>
			<div id="<?= __NAMESPACE__;?>-thumbnail-preview">
				<?php if(!empty($thumbnail_url)){ ?>
					<img src="<?= $thumbnail_url;?>" alt="<?= __('Custom thumbnail preview');?>">
				<?php } ?>
			</div>
			<a href="javascript:;" id="sinapicv2-thumbnail-remove" <?= empty($thumbnail_url) ? 'hidden' : null;?>><?= __('Remove custom thumbnail');?></a>
			<input type="hidden" name="<?= __NAMESPACE__;?>[thumbnail-url]" id="sinapicv2-thumbnail-url" value="<?= $thumbnail_url;?>">
		</div>
		<?php
	}
	/**
	 * is_authorized
	 *
	 * @return bool
	 * @version 1.0.0
	 */
	public static function is_authorized(){
		$authorization = (array)self::get_options(self::$key_authorization);
		/** 
		 * if authorized
		 */
		if(isset($authorization['access_token']) && 
			((int)$authorization['access_time'] + (int)$authorization['expires_in']) > time()){
			return true;
		}else{
			return false;
		}
	}
	/** 
	 * footer_info
	 */
	public static function footer_info(){
		echo '<!-- ' . sprintf(__('Image uploader by %s@INN STUDIO'),__('Sina Pic v2')) . ' -->';
	}
}
function __($str){
	return \__($str, __NAMESPACE__);
}
sinapicv2::init();