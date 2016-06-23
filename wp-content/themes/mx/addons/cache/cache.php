<?php
/*
Feature Name:	theme-cache
Feature URI:	http://inn-studio.com
Version:		3.0.1
Description:	theme-cache
Author:			INN STUDIO
Author URI:		http://inn-studio.com
*/

class theme_cache{
	public static $cache;
	public static $cache_base_key;
	
	public static function init(){
		self::$cache_base_key = theme_functions::$iden;
		
		add_action('advanced_settings', __CLASS__ . '::display_backend');
		add_action('wp_ajax_' . __CLASS__, __CLASS__ . '::process');
		/**
		 * When update menu
		 */
		add_filter('pre_set_theme_mod_nav_menu_locations',function($return){
			self::delete_keys('nav-menu');
			return $return;
		});
	
		/**
		 * When update widget
		 */
		add_filter('widget_update_callback',function($instance){
			self::delete_keys('dynamic-sidebar');
			return $instance;
		});
		
		/**
		 * When update option for widget
		 */
		add_action('update_option_sidebars_widgets',function(){
			self::delete_keys('dynamic-sidebar');
		});
		
		/**
		 * when post delete
		 */
		add_action('delete_post', function($post_id){
			$post = self::get_post($post_id);
			if($post->type !== 'page')
				return;
			$caches = (array)wp_cache_get('pages_by_path');
			if(isset($caches[$post->post_name])){
				unset($caches[$post->post_name]);
				wp_cache_set('pages_by_path',$caches);
			}
		});
		/**
		 * when post save
		 */
		add_action('save_post', function($post_id){
			$post = self::get_post($post_id);
			if($post->type !== 'page')
				return;
			$caches = (array)wp_cache_get('pages_by_path');
			if(!isset($caches[$post->post_name])){
				$caches[$post->post_name] = $post_id;
				wp_cache_set('pages_by_path',$caches);
			}
		});
		
	}
	private static function get_process_url($type){
		return esc_url(add_query_arg(array(
			'action' => __CLASS__,
			'type' => $type
		),theme_features::get_process_url()));

	}
	/**
	 * Admin Display
	 */
	public static function display_backend(){
		?>
		<fieldset id="<?= __CLASS__;?>">
			<legend><i class="fa fa-fw fa-hourglass-end"></i> <?= ___('Theme cache');?></legend>
			<p class="description"><?= ___('Maybe the theme used cache for improve performance, you can clean it when you modify some site contents if you want.');?></p>
			<table class="form-table">
				<tbody>
					<?php if(class_exists('Memcache')){ ?>
					<tr>
						<th><?= ___('Memcache cache');?></th>
						<td><p>
							<?php
							if(file_exists(WP_CONTENT_DIR . '/object-cache.php')){ ?>
								<a class="button" href="<?= self::get_process_url('disable-cache');?>" onclick="return confirm('<?= ___('Are you sure DELETE object-cache.php to disable theme object cache?');?>')">
									<?= ___('Disable theme object cache');?>
								</a>
								
								<a class="button" href="<?= self::get_process_url('re-enable-cache');?>" onclick="return confirm('<?= ___('Are you sure RE-CREATE object-cache.php to re-enable theme object cache?');?>')">
									<?= ___('Re-enable theme object cache');?>
								</a>
								
							<?php }else { ?>
								<a class="button-primary" href="<?= self::get_process_url('enable-cache');?>">
									<?= ___('Enable theme object cache');?>
								</a>
							<?php } ?>
							<span class="description"><i class="fa fa-exclamation-circle"></i> <?= ___('Save your settings before click.');?></span>
							
						</p></td>
					</tr>
					<?php } ?>
					<tr>
						<th scope="row"><?= ___('Control');?></th>
						<td>
							<?php
							if(isset($_GET[__CLASS__])){
								echo status_tip('success',___('Theme cache has been cleaned or rebuilt.'));
							}
							?>
							<p>
								<?php
								/**
								 * poicache - wp advanced cache
								 */
								if(class_exists('innstudio\advanced_cache')){
									?>
									<a href="<?= self::get_process_url('flush-poicache');?>" class="button" onclick="javascript:this.innerHTML='<?= ___('Processing, please wait...');?>'"><?= ___('Clean PoiCache cache');?></a>
								<?php } ?>
								
								<a href="<?= self::get_process_url('flush');?>" class="button" onclick="javascript:this.innerHTML='<?= ___('Processing, please wait...');?>'"><?= ___('Clean all cache');?></a>
								
								<a href="<?= self::get_process_url('dynamic-sidebar');?>" class="button" onclick="javascript:this.innerHTML='<?= ___('Processing, please wait...');?>'"><?= ___('Clean widget cache');?></a>
								
								<a href="<?= self::get_process_url('nav-menu');?>" class="button" onclick="javascript:this.innerHTML='<?= ___('Processing, please wait...');?>'"><?= ___('Clean menu cache');?></a>
								
								
								<span class="description"><i class="fa fa-exclamation-circle"></i> <?= ___('Save your settings before clean');?></span>
								
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	<?php
	}
	private static function disable_cache(){
		$result = @unlink(WP_CONTENT_DIR . '/object-cache.php');
		if($result === true) return true;
		die(sprintf(___('Can not delete the %s file, please make sure the folder can be written.'),WP_CONTENT_DIR . '/object-cache.php'));
	}
	private static function enable_cache(){
		$result = copy(__DIR__ . '/object-cache.php',WP_CONTENT_DIR . '/object-cache.php');
		if($result === true) return true;
		die(sprintf(___('Can not create the %s file, please make sure the folder can be written.'),WP_CONTENT_DIR . '/object-cache.php'));
	}
	/**
	 * process
	 */
	public static function process(){
		$type = isset($_GET['type']) ? $_GET['type'] : null;
		if(!self::current_user_can('manage_options'))
			die;
			
		switch($type){
			/** poicache */
			case 'flush-poicache':
				self::cleanup_poicache();
				break;
			case 'flush':
				self::cleanup();
			break;
			case 're-enable-cache':
				self::cleanup();
				self::disable_cache();
				self::enable_cache();
			break;
			case 'disable-cache':
				self::cleanup();
				self::disable_cache();
			break;
			case 'enable-cache':
				self::enable_cache();
			break;
			default:
				self::delete_keys($type);
		}
		wp_redirect(admin_url('themes.php?page=core-options&' . __CLASS__ . '=1'));

		die;
	}
	public static function cleanup_poicache(){
		if(!class_exists('innstudio\advanced_cache'))
			return false;
		$dir = WP_CONTENT_DIR . '/poicache/';
		$dh = opendir($dir);
		while(($file = readdir($dh)) !== false){
			if($file === '.' || $file === '..')
				continue;
			unlink($dir . $file);
		}
		closedir($dh);
	}
	public static function cleanup(){
		if(wp_using_ext_object_cache())
			return wp_cache_flush();
	}
	public static function get_categories($args = ''){
		static $caches = [];
		$cache_id = md5(json_encode($args));
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];
		$caches[$cache_id] = get_categories($args);
		return $caches[$cache_id];
	}
	public static function get_category($category, $output = OBJECT, $filter = 'raw'){
		static $caches = [];
		$cache_id = md5(json_encode(func_get_args()));
		
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];
			
		$category = get_term( $category, 'category', $output, $filter );

		if ( is_wp_error( $category ) ){
			$caches[$cache_id] = $category;
			return $category;
		}

		_make_cat_compat( $category );

		$caches[$cache_id] = $category;
		
		return $category;
	}
	public static function get_avatar($id_or_email, $size = 96, $default = '', $alt = '', $args = null){
		static $caches = [];
		$cache_id = md5(json_encode(func_get_args()));
		if(!isset($caches[$cache_id]))
			$caches[$cache_id] = get_avatar($id_or_email, $size, $default , $alt, $args);
		return $caches[$cache_id];
	}
	public static function get_avatar_url($id_or_email){
		static $caches = [];
		$cache_id = md5(json_encode($id_or_email));
		if(!isset($caches[$cache_id]))
			$caches[$cache_id] = get_avatar_url($id_or_email);
		return $caches[$cache_id];
	}
	public static function get_comment_author($comment_id){
		static $caches = [];
		if(!isset($caches[$comment_id]))
			$caches[$comment_id] = esc_html(get_comment_author($comment_id));
		return $caches[$comment_id];
	}
	public static function get_comment(&$comment = null, $output = OBJECT){
		static $caches = [];
		$cache_id = md5(json_encode(func_get_args()));
		if(!isset($caches[$cache_id]))
			$caches[$cache_id] = get_comment($comment, $output);
		return $caches[$cache_id];
	}
	public static function get_post(){
		static $caches = [];
		$cache_id = md5(json_encode(func_get_args()));
		if(!isset($caches[$cache_id]))
			$caches[$cache_id] = call_user_func_array('get_post', func_get_args());
		return $caches[$cache_id];
	}
	public static function get_the_title($post_id){
		static $caches = [];
		if(!isset($caches[$post_id]))
			$caches[$post_id] = esc_html(get_the_title($post_id));
		return $caches[$post_id];
	}
	public static function get_permalink($post_id,  $leavename = false){
		static $caches = [];
		if(is_object($post_id))
			$post_id = $post_id->ID;
			
		if(!isset($caches[$post_id]))
			$caches[$post_id] = esc_url(get_permalink($post_id,$leavename));
		return $caches[$post_id];
	}
	public static function get_the_author_meta($field,$user_id){
		static $cache = [];
		$cache_id = $field . $user_id;
		if(!isset($cache[$cache_id]))
			switch($field){
				case 'display_name':
				case 'first_name':
				case 'last_name':
				case 'description':
				case 'user_firstname':
				case 'user_lastname':
				case 'nickname':
					$cache[$cache_id] = esc_html(get_the_author_meta($field,$user_id));
					break;
				default:
					$cache[$cache_id] = get_the_author_meta($field,$user_id);
			}
			
		return $cache[$cache_id];
	}
	public static function get_current_user_id(){
		if(!self::is_user_logged_in())
			return false;
		static $cache = null;
		if($cache === null)
			$cache = get_current_user_id();
		return $cache;
	}
	public static function current_user_can($capability){
		if(!self::is_user_logged_in())
			return false;
		static $caches = [];
		$cache_id = md5(json_encode($capability));
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];
		$caches[$cache_id] = current_user_can($capability);
		return $caches[$cache_id];
	}
	public static function wp_title($sep = '&raquo;', $display = true, $seplocation = ''){
		static $caches = [];
		$cache_id = md5(json_encode(func_get_args()));
		if(!isset($caches[$cache_id]))
			$caches[$cache_id] = esc_html(wp_title($sep, $display, $seplocation));
		return $caches[$cache_id];
	}
	/**
	 * Get option from cache
	 *
	 * @param string $key
	 * @return mixed 
	 * @version 1.0.1
	 */
	public static function get_option($key, $default = false){
		static $caches = [];
		$cache_id = $key . $default;
		if(!isset($caches[$cache_id]))
			$caches[$cache_id] = get_option($cache_id);
		return $caches[$cache_id];
	}

	public static function home_url($path = null){
		static $caches = [],$cache = null;
		if($path === null){
			if($cache !== null)
				return $cache;
			$cache = home_url();
			return $cache;
		}else{
			if(isset($caches[$path]))
				return $caches[$path];
			$caches[$path] = home_url($path);
			return $caches[$path];
		}
	}
	public static function get_current_screen(){
		static $cache = null;
		if($cache === null)
			$cache = get_current_screen();
		return $caceh;
	}
	public static function is_archive(){
		static $cache = null;
		if($cache === null)
			$cache = (bool)is_archive();
		return $cache;
	}
	public static function is_post_type_archive($post_types = null){
		static $caches = [];
		$cache_id = md5(json_encode(func_get_args()));
		if(!isset($caches[$cache_id]))
			$caches[$cache_id] = (bool)is_post_type_archive($post_types);
		return $caches[$cache_id];
	}
	public static function is_attachment(){
		static $cache = null;
		if($cache === null)
			$cache = (bool)is_attachment();
		return $cache;
	}
	public static function is_admin(){
		static $cache = null;
		if($cache === null)
			$cache = (bool)is_admin();
		return $cache;
	}
	public static function is_front_page(){
		static $cache = null;
		if($cache === null)
			$cache = (bool)is_front_page();
		return $cache;
	}
	public static function is_author($author = null){
		static $caches = [];
		$cache_id = 'author' . $author;
		if(!isset($caches[$cache_id]))
			$caches[$cache_id] = (bool)is_author($author);
		return $caches[$cache_id];
	}
	public static function is_404(){
		static $cache = null;
		if($cache === null)
			$cache = (bool)is_404();
		return $cache;
	}
	public static function is_search(){
		static $cache = null;
		if($cache === null)
			$cache = (bool)is_search();
		return $cache;
	}
	public static function is_tag(){
		static $cache = null;
		if($cache === null)
			$cache = (bool)is_tag();
		return $cache;
	}
	public static function is_category(){
		static $cache = null;
		if($cache === null)
			$cache = (bool)is_category();
		return $cache;
	}
	public static function is_date(){
		static $cache = null;
		if($cache === null)
			$cache = (bool)is_date();
		return $cache;
	}
	public static function is_day(){
		static $cache = null;
		if($cache === null)
			$cache = (bool)is_day();
		return $cache;
	}
	public static function is_month(){
		static $cache = null;
		if($cache === null)
			$cache = (bool)is_month();
		return $cache;
	}
	public static function is_year(){
		static $cache = null;
		if($cache === null)
			$cache = (bool)is_year();
		return $cache;
	}
	public static function is_home(){
		static $cache = null;
		if($cache === null)
			$cache = (bool)is_home();
		return $cache;
	}
	public static function is_singular($post_types = null){
		static $caches = [];
		$cache_id = md5(json_encode(func_get_args()));
		if(!isset($caches[$cache_id]))
			$caches[$cache_id] = (bool)is_singular($post_types);
		return $caches[$cache_id];
	}
	public static function is_page($page = null){
		static $caches = [];
		$cache_id = md5(json_encode(func_get_args()));
		if(!isset($caches[$cache_id]))
			$caches[$cache_id] = (bool)is_page($page);
		return $caches[$cache_id];
	}
	public static function get_bloginfo($key){
		static $caches = [];
		if(!isset($caches[$key]))
			$caches[$key] = get_bloginfo($key);
		return $caches[$key];
	}
	public static function is_user_logged_in(){
		static $cache = null;
		if($cache === null)
			$cache = (bool)is_user_logged_in();
		return $cache;
	}
	public static function get_author_posts_url($user_id,$author_nicename = ''){
		$group_id = 'author_posts_urls';
		$cache = wp_cache_get($user_id,$group_id);
		if(!$cache){
			$cache = esc_url(get_author_posts_url($user_id,$author_nicename));
			wp_cache_set($user_id,$cache,$group_id);
		}
		return $cache;
	}
	/**
	 * add cache for get_page_by_path()
	 * 
	 * @version 1.0.1
	 */
	public static function get_page_by_path($page_path, $output = OBJECT, $post_type = 'page'){
		$cache_id = 'pages_by_path';
		$caches = (array)wp_cache_get($cache_id);
		/** get post id from cache */
		if(isset($caches[$page_path])){
			return self::get_post($caches[$page_path],$output);
		/** get post id from db */
		}else{
			$post = get_page_by_path($page_path,$output,$post_type);
			if(!$post)
				return false;
			$caches[$page_path] = $post->ID;
			wp_cache_set($cache_id,$caches);
			return $post;
		}
	}

	/**
	 * Delete cache
	 *
	 * @param string $key Cache key
	 * @param string $group Cache group
	 * @return bool
	 * @version 2.1.1
	 */
	public static function delete($key,$group = null){
		if(wp_using_ext_object_cache()){
			$group = self::$cache_base_key . '-' . $group;
			return wp_cache_delete($key,$group);
		}
	}
	/**
	 * Set cache
	 *
	 * @param string $key Cache ID
	 * @param mixed $data Cache contents
	 * @param string $group Cache group
	 * @return int $expire Cache expire time (s)
	 * @version 2.0.5
	 */
	public static function set($key,$data,$group = null,$expire = 0){
		if(theme_dev_mode::is_enabled())
			return false;
			
		if(wp_using_ext_object_cache()){
			$group = self::$cache_base_key . '-' . $group;
			return wp_cache_set($key,$data,$group,$expire);
		}
		return false;
	}
	/**
	 * Get the cache
	 *
	 * @param string $key Cache ID
	 * @param string $group Cache group
	 * @param bool $force True to get cache forced
	 * @return mixed
	 * @version 2.0.2
	 */
	public static function get($key,$group = null,$force = false){
		/**
		 * if dev mode enabled, do NOT get data from cache
		 */
		if(theme_dev_mode::is_enabled()) 
			return false;
		
		if(wp_using_ext_object_cache()){
			$group = self::$cache_base_key . '-' . $group;
			return wp_cache_get($key,$group,$force);
		}
		return false;
	}
	private static function get_keys($key = null,$group_key){
		$group_key = 'keys-' . $group_key;
		$keys = array_filter((array)self::get($group_key));
		if($key)
			return in_array($key,$keys);
		return $keys;
	}
	private static function set_key($key,$group_key){
		$group_key = 'keys-' . $group_key;
		$keys = array_filter((array)self::get($group_key));
		$keys[] = $key;
		self::set($group_key,$keys);
		return $keys;
	}
	private static function delete_keys($group_id){
		if(!$group_id)
			return false;
		$group_key = 'keys-' . $group_id;
		$keys = array_filter((array)self::get($group_key));
		if(!$keys)
			return false;
		/** delete content */
		foreach($keys as $key)
			self::delete($key,$group_id);
		self::delete($group_key);
	}
	private static function get_page_prefix(){
		static $cache_id_prefix = null;
		if($cache_id_prefix !== null)
			return $cache_id_prefix;
			
		if(self::is_singular()){
			global $post;
			$cache_id_prefix = 'post-' . $post->ID;
		}else if(self::is_home()){
			$cache_id_prefix = 'home';
		}else if(self::is_category()){
			$cache_id_prefix = 'cat-' . theme_features::get_current_cat_id();
		}else if(self::is_tag()){
			$cache_id_prefix = 'tag-' . theme_features::get_current_tag_id();
		}else if(self::is_search()){
			$cache_id_prefix = 'search';
		}else if(self::is_404()){
			$cache_id_prefix = 'error404';
		}else if(self::is_author()){
			global $author;
			$cache_id_prefix = 'author-' . $author;
		}else if(self::is_front_page()){
			$cache_id_prefix = 'frontpage';
		}else if(self::is_post_type_archive()){
			$cache_id_prefix = 'post-type-' . get_query_var('post_type');
		}else if(self::is_archive()){
			$cache_id_prefix = 'archive';
		}else{
			$cache_id_prefix = get_current_url();
		}
	 	return $cache_id_prefix;
	}
	/**
	 * output dynamic sidebar from cache
	 *
	 * @param string The widget sidebar name/id
	 * @param int Cache expire time
	 * @return string
	 * @version 3.0.1
	 */
	public static function dynamic_sidebar($id,$expire = 3600){
		$cache_group_id = 'dynamic-sidebar';
		$cache_id = md5(self::get_page_prefix() . wp_is_mobile() . $id);
		$cache = self::get($cache_id,$cache_group_id);

		$exists_key = self::get_keys($cache_id,$cache_group_id);
		
		if($exists_key && $cache){
			echo $cache;
			return $cache;
		}

		if(!$cache){
			ob_start();
			dynamic_sidebar($id);
			$cache = html_minify(ob_get_contents());
			ob_end_clean();
			self::set($cache_id,$cache,$cache_group_id,$expire);
		}
		if(!$exists_key){
			self::set_key($cache_id,$cache_group_id);
		}
		
		echo $cache;
		return $cache;
	}
	/**
	 * wp nav menu from cache
	 *
	 * @param string The widget sidebar name/id
	 * @param int Cache expire time
	 * @return string
	 * @version 2.1.1
	 */
	public static function wp_nav_menu($args,$expire = 3600){
		$cache_group_id = 'nav-menu';
		$cache_id = md5(self::get_page_prefix() . json_encode($args));

		$cache = self::get($cache_id,$cache_group_id);

		$exists_key = self::get_keys($cache_id,$cache_group_id);
		
		if($exists_key && $cache){
			echo $cache;
			unset($cache);
			return;
		}

		if(!$cache){
			ob_start();
			wp_nav_menu($args);
			$cache = html_minify(ob_get_contents());
			ob_end_clean();
			self::set($cache_id,$cache,$cache_group_id,$expire);
		}
		if(!$exists_key){
			self::set_key($cache_id,$cache_group_id);
		}
		
		echo $cache;
		unset($cache);
		return;
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_cache::init';
	return $fns;
});