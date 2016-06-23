<?php
/*
Feature Name:	Post Views
Feature URI:	http://www.inn-studio.com
Version:		3.0.3
Description:	Count the post views.
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
class theme_post_views{
	public static $post_meta_key = 'views';
	public static $cache_key = array(
		'views' => 'theme_post_views',
		'times' => 'theme_post_views_times'
	);
	public static $expire = 2505600; /** 29 days */
	public static $cookie_expire = 60; /** 1 min */
	public static $cookie = null;

	public static function init(){

		add_action('base_settings', __CLASS__ . '::display_backend');

		add_filter('theme_options_default', __CLASS__ . '::options_default');

		add_filter('theme_options_save', __CLASS__ . '::options_save');

		if(!self::is_enabled())
			return;

		add_filter('frontend_js_config', __CLASS__ . '::frontend_js_config');

		
		add_filter('dynamic_request_process', __CLASS__ . '::process_dynamic_request_process');
		add_filter('dynamic_request', __CLASS__ . '::dynamic_request');


		/** admin post/page css */
		add_action('admin_head', __CLASS__ . '::admin_css');
		add_action('manage_posts_custom_column', __CLASS__ . '::admin_show',10,2);
		add_filter('manage_posts_columns', __CLASS__ . '::admin_add_column');
	}
	public static function options_default(array $opts = []){
		$opts[__CLASS__] = array(
			'enabled' => 1,
			'storage-times' => 10,
		);
		return $opts;
	}
	public static function display_backend(){
		?>
		<fieldset>
			<legend><i class="fa fa-fw fa-eye"></i> <?= ___('Post views settings');?></legend>
			<table class="form-table">
				<tbody>
					<tr>
						<th><label for="<?= __CLASS__;?>-enabled"><?= ___('Enable or not?');?></label></th>
						<td>
							<select name="<?= __CLASS__;?>[enabled]" id="<?= __CLASS__;?>-enabled" class="widefat">
								<?php the_option_list(-1,___('Disable'),self::get_options('enabled'));?>
								<?php the_option_list(1,___('Enable'),self::get_options('enabled'));?>
							</select>
						</td>
					</tr>
					<?php if(wp_using_ext_object_cache()){ ?>
						<tr>
							<th><label for="<?= __CLASS__;?>-storage-times"><?= ___('Max cache storage times');?></label></th>
							<td>
								<input class="short-text" type="number" name="<?= __CLASS__;?>[storage-times]" id="<?= __CLASS__;?>-storage-times" value="<?= self::get_storage_times();?>" min="1">
								<span class="description"><?= ___('Using cache to improve performance. When the views more than max storage times, views will be save to database.');?></span>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	
	public static function update_views($post_id){
		if(wp_using_ext_object_cache())
			return self::update_views_using_cache($post_id);
		return self::update_views_using_db($post_id);
	}
	private static function update_views_using_db($post_id){
		$meta = (int)get_post_meta($post_id,self::$post_meta_key,true) + 1;
		update_post_meta($post_id,self::$post_meta_key,$meta);
		return $meta;
	}
	/**
	 * update_views_using_cache
	 * 
	 * 
	 * @return 
	 * @version 1.0.2
	 * 
	 */
	private static function update_views_using_cache($post_id,$force = false){

		$times = wp_cache_get($post_id,__CLASS__);

		$meta = (int)get_post_meta($post_id,self::$post_meta_key,true) + (int)$times;
		/**
		 * force to update db
		 */
		if($force){
			$meta++;
			wp_cache_set($post_id,0,__CLASS__,self::$expire);
			update_post_meta($post_id,self::$post_meta_key,$meta);
		/**
		 * update cache
		 */
		}else{
			/**
			 * if views more than storage times, update db and reset cache
			 */
			if($times >= self::get_storage_times()){
				$meta = $meta + $times + 1;
				update_post_meta($post_id,self::$post_meta_key,$meta);
				wp_cache_set($post_id,0,__CLASS__,self::$expire);
			/**
			 * update cache
			 */
			}else{
				if($times === false)
					wp_cache_set($post_id,0,__CLASS__,self::$expire);
					
				wp_cache_incr($post_id,1,__CLASS__);
				$meta++;
			}
		}
		return $meta;
	}
	private static function get_storage_times(){
		return (int)self::get_options('storage-times');
	}
	/**
	 * get the views
	 * 
	 * @params int $post_id
	 * @return int the views
	 * @version 2.0.0
	 * 
	 */
	public static function get_views($post_id = null){
		if(!$post_id){
			global $post;
			$post_id = $post->ID;
		}
		
		$meta = (int)get_post_meta($post_id,self::$post_meta_key,true) + 1;
		
		if(wp_using_ext_object_cache())
			return $meta + (int)wp_cache_get($post_id,__CLASS__);
		
		return $meta;
	}
	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = (array)theme_options::get_options(__CLASS__);
		if($key){
			if(isset($caches[$key])){
				return $caches[$key];
			}else{
				$caches[$key] = isset(self::options_default()[__CLASS__][$key]) ? self::options_default()[__CLASS__][$key] : false;
				return $caches[$key];
			}
		}
		return $caches;
	}
	public static function is_enabled(){
		return self::get_options('enabled') == 1;
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__])){
			$opts[__CLASS__] = $_POST[__CLASS__];
		}
		return $opts;
	}

	public static function admin_add_column($columns){
		$columns[self::$post_meta_key] = ___('Views');
		return $columns;
	}
	public static function admin_show($column_name,$post_id){
		if ($column_name != 'views') 
			return;	
		echo self::get_views($post_id);
	}
	public static function admin_css(){
		?><style>.fixed .column-views{width:3em}</style><?php
	}
	public static function process_dynamic_request_process(array $output){
		$id = isset($_GET[__CLASS__]) && is_numeric($_GET[__CLASS__]) ? (int)$_GET[__CLASS__] : null;
		
		if(!$id)
			return $output;

		if(!self::is_viewed($id)){
			$views = self::update_views($id);
		}else{
			$views = self::get_views($id);
		}
		
		$output[__CLASS__] = [
			$id => $views
		];
		return $output;
	}
	public static function get_viewed_ids(){
		if(self::$cookie === null)
			self::$cookie = isset($_COOKIE[__CLASS__]) ? json_decode($_COOKIE[__CLASS__],true) : false;

		return self::$cookie;
	}
	public static function set_viewed_ids($post_id){
		$expire = time() + self::$cookie_expire;
		self::$cookie = self::get_viewed_ids();
		if(empty(self::$cookie)){
			self::$cookie = [$post_id];
			setcookie(__CLASS__,json_encode([$post_id]),$expire);
			return true;
		}else{
			if(!in_array($post_id,self::$cookie)){
				self::$cookie[] = $post_id;
				setcookie(__CLASS__,json_encode(self::$cookie),$expire);
				return true;
			}
			return false;
		}
	}
	public static function is_viewed($post_id){
		return !self::set_viewed_ids($post_id);
	}
	public static function dynamic_request(array $output){
		if(!theme_cache::is_singular('post'))
			return $output;
		$output[__CLASS__] = get_the_ID();
		return $output;
	}
	public static function frontend_js_config(array $config){
		if(!theme_cache::is_singular('post'))
			return $config;

		$config[__CLASS__] = 1;
		return $config;
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_post_views::init';
	return $fns;
});