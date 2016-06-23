<?php
/**
 * @version 2.0.0
 */
class theme_asset_enqueue{
	public static function init(){
		/** frontend */
		add_action('wp_enqueue_scripts', __CLASS__ . '::frontend_enqueue_scripts', 999);
		add_action('wp_enqueue_scripts', __CLASS__ . '::frontend_enqueue_css');
		
		/** admin */
		//add_action('')
		add_action('admin_enqueue_scripts', __CLASS__ . '::backend_enqueue_scripts', 999);
		add_action('admin_enqueue_scripts', __CLASS__ . '::backend_post_new_enqueue_scripts', 999);
		add_action('admin_enqueue_scripts', __CLASS__ . '::backend_enqueue_css');
	}
	/**
	 * backend css
	 */
	public static function backend_enqueue_css(){
		if(!theme_options::is_options_page())
			return;
		$css = [
			'backend' => [
				'deps' => ['awesome'],
				'url' =>  theme_features::get_theme_css('backend'),
			],
			'awesome' => [
				'deps' => [],
				'url' => '//cdn.bootcss.com/font-awesome/4.4.0/css/font-awesome.min.css',
				'version' => null,
			],
			
		];

		foreach($css as $k => $v){
			wp_enqueue_style(
				$k,
				$v['url'],
				isset($v['deps']) ? $v['deps'] : [],
				self::get_version($v)
			);
		}
	}
	/**
	 * backend js
	 */
	public static function backend_enqueue_scripts(){
		if(!theme_options::is_options_page())
			return;
			
		$js = [
			'frontend' => [
				'url' => theme_features::get_theme_js('backend-entry'),
			],
			
		];
		foreach($js as $k => $v){
			wp_enqueue_script(
				$k,
				$v['url'],
				isset($v['deps']) ? $v['deps'] : [],
				self::get_version($v),
				true
			);
		}
		
	}
	public static function backend_post_new_enqueue_scripts($hook){
		$pages = [
			'post-new.php',
			'post.php'
		];
		if(!in_array($hook,$pages))
			return;
			
		$js = [
			'frontend' => [
				'deps' => [],
				'url' => theme_features::get_theme_js('backend-post-new-entry'),
			],
			
		];
		foreach($js as $k => $v){
			wp_enqueue_script(
				$k,
				$v['url'],
				isset($v['deps']) ? $v['deps'] : [],
				self::get_version($v),
				true
			);
		}
	}
	/** frontend js */
	public static function frontend_enqueue_scripts(){
		$frontend_js = theme_cache::is_user_logged_in() ? 'frontend-logged' : 'frontend-entry';
		$js = [
			'frontend' => [
				'deps' => [],
				'url' => theme_features::get_theme_js($frontend_js),
			],
			
		];
		foreach($js as $k => $v){
			wp_enqueue_script(
				$k,
				$v['url'],
				isset($v['deps']) ? $v['deps'] : [],
				self::get_version($v),
				true
			);
		}
		
	}

	private static function get_version($v){
		return array_key_exists('version', $v) ? $v['version'] : theme_file_timestamp::get_timestamp();
	}
	/**
	 * frontend css
	 */
	public static function frontend_enqueue_css(){
		$frontend_css = theme_cache::is_user_logged_in() ? 'frontend-logged' : 'frontend';
		$css = [
			'frontend' => [
				'deps' => ['awesome'],
				'url' =>  theme_features::get_theme_css($frontend_css),
			],
			'awesome' => [
				'deps' => [],
				'url' => '//cdn.bootcss.com/font-awesome/4.4.0/css/font-awesome.min.css',
				'version' => null,
			],
			//'awesome' => [
			//	'deps' => [],
			//	'url' => theme_features::get_theme_css('modules/awesome/4.4.0/css/font-awesome'),
			//],
			
		];

		foreach($css as $k => $v){

			wp_enqueue_style(
				$k,
				$v['url'],
				isset($v['deps']) ? $v['deps'] : [],
				self::get_version($v)
			);
		}
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_asset_enqueue::init';
	return $fns;
});