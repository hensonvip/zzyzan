<?php
/**
 * WordPress Extra Theme Features.
 *
 * Help you write a wp site quickly.
 *
 * @package KMTF
 * @version 6.0.0
 */
class theme_features{
	
	public static $basedir_js 					= '/assets/js/';
	public static $basedir_css		 			= '/assets/css/';
	public static $basedir_images 				= '/assets/images/';
	public static $basedir_addons 				= '/addons/';
	
	public static function init(){
		self::set_basename();
		add_action('after_setup_theme', __CLASS__ . '::after_setup_theme');
		add_action('wp_footer', __CLASS__ . '::theme_js',1);
	}

	/**
	 * theme_js
	 * 
	 * @version 1.1.5
	 **/
	public static function theme_js(){
		$config = [
			'vars' => [
				'locale' => str_replace('-','_',theme_cache::get_bloginfo('language')),
				'theme_js' => self::get_theme_js(),
				'theme_css' => self::get_theme_css(),
				'theme_images' => self::get_theme_images_url(),
				'process_url' => self::get_process_url(),
				'timestamp' => theme_file_timestamp::get_timestamp(),
			],
			'lang' => [
				'M01' => ___('Loading, please wait...'),
				'E01' => ___('Sorry, server is busy now, can not respond your request, please try again later.'),
			],
		];
		/** Hook 'frontend_js_config' */
		?>
		<script>
		window.THEME_CONFIG = <?= json_encode(apply_filters('frontend_js_config',(array)$config));?>;
		</script>
		<?php
	}
	/**
	 * get_theme_info
	 *
	 * @param string get what 
	 * @param string 
	 * @param string 
	 * @return string
	 * @version 1.0.3
	 * @see http://codex.wordpress.org/Function_Reference/wp_get_theme
	 */
	public static function get_theme_info($key = null,$stylesheet = null, $theme_root = null){
		static $caches = [],$theme = null;
		$cache_id = md5(json_encode(func_get_args()));
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];
			
		if($theme === null)
			$theme = wp_get_theme($stylesheet = null, $theme_root = null);
		
		if(!$key){
			$caches[$cache_id] = $theme;
			return $caches[$cache_id];
		}
		
		$key = ucfirst($key);
		$caches[$cache_id] = $theme->get($key);
		return $caches[$cache_id];
	}

	/**
	 * set_theme_version
	 *
	 * @return n/a
	 * @version 1.0.2
	 */
	public static function set_theme_info(){
		$theme = self::get_theme_info();
		set_transient(theme_functions::$iden . 'theme_info',$theme);
	}
	public static function set_basename(){
		theme_functions::$basename = basename(self::get_stylesheet_directory());
	}
	public static function get_stylesheet_directory(){
		static $cache = null;
		if($cache === null)
			$cache = get_stylesheet_directory();

		return $cache;
	}
	public static function get_template_directory_uri(){
		static $cache = null;
		if($cache === null)
			$cache = get_template_directory_uri();

		return $cache;
	}
	/**
	 * get_theme_file_url
	 * return your file for load under the theme.
	 * 
	 * @param string $file_basename the file name, like 'functions.js'
	 * @param bool/string $version The file version
	 * @return string
	 * @version 1.2.6
	 * @since 3.3.0
	 * @author INN STUDIO
	 * 
	 */
	public static function get_theme_file_url($file_basename = null,$mtime = null){

		if(!$file_basename)
			return self::get_template_directory_uri();
		
		/**
		 * fix basename string
		 */
		$file_basename = $file_basename[0] === '/' ? $file_basename : '/' .$file_basename;
		/**
		 * get file url and path full
		 */
		$file_url = self::get_template_directory_uri() . $file_basename;

		/**
		 * get file mtime
		 */
		if($mtime === true)
			$file_url = $file_url . '?v=' . theme_file_timestamp::get_timestamp();
		
		return $file_url;
	}
	/**
	 * theme_features::get_theme_js
	 * 
	 * @since 3.2.0
	 * @version 1.2.3
	 * @param string $file_basename js file basename, function = function.js
	 * @param bool $url_only Only output the file src if true
	 * @param bool/string $mtime The file version
	 * @return string <script> tag string or js url only
	 */
	public static function get_theme_js($file_basename = null, $mtime = null){
		if(!$file_basename) 
			return self::get_template_directory_uri() . self::$basedir_js;
		return self::get_theme_file_url(self::$basedir_js . $file_basename . '.js', $mtime);
	}
	/**
	 * theme_features::get_theme_css
	 * 
	 * @version 2.0.0
	 * @param string $file_basename css file basename, like 'style'
	 * @param bool $mtime The file version
	 * @return string Css url
	 */
	public static function get_theme_css($file_basename = null, $mtime = false){
		if(!$file_basename) 
			return self::get_template_directory_uri() . self::$basedir_css;
		return self::get_theme_file_url(self::$basedir_css . $file_basename . '.css', $mtime);
	}
	/**
	 * get_theme_extension_url
	 * 
	 * @param array 
		$args = array_merge([
			'type' => null,
			'basedir' => null,
			'file_basename' => null,
			'ext' => null,
			'mtime' => false,
		],$args);
	 * @return string
	 * @version 3.0.0
	 */
	public static function get_theme_extension_url(array $args){
		$self_basedir_extension = 'basedir_' . $args['ext'];
		$self_basedir = 'basedir_' . $args['type'];
		$file_url = self::get_theme_url() . self::$$self_basedir . basename($args['basedir']) . self::$$self_basedir_extension . $args['file_basename'] . '.' . $args['ext'];
			
		return $file_url;
	}
	
	/**
	 * get_theme_addons_js
	 * 
	 * @param string $DIR
	 * @param string $file_basename
	 * @param bool $mtime
	 * @return string JS url
	 * @version 3.0.0
	 */
	public static function get_theme_addons_js($DIR = null,$file_basename = 'frontend', $mtime = null){
		if($mtime === true)
			$mtime = '?v=' . theme_file_timestamp::get_timestamp();
			
		return self::get_theme_extension_url([
			'type' => 'addons',
			'basedir' => $DIR,
			'ext' => 'js',
			'file_basename' => $file_basename,
		]) . $mtime;
	}
	/**
	 * get_theme_addons_css
	 * 
	 * @param string $DIR
	 * @param string $file_basename
	 * @param bool $mtime
	 * @return string
	 * @version 3.0.0
	 */
	public static function get_theme_addons_css($DIR, $file_basename = 'frontend', $mtime = null){
		if($mtime === true)
			$mtime = '?v=' . theme_file_timestamp::get_timestamp();
		
		return self::get_theme_extension_url([
			'type' => 'addons',
			'basedir' => $DIR,
			'ext' => 'css',
			'file_basename' => $file_basename,
		]) . $mtime;
	}
	/**
	 * get_theme_addons_image
	 * 
	 * @param string $DIR
	 * @param string $filename
	 * @return string
	 * @version 1.0.3
	 */
	public static function get_theme_addons_image($DIR, $filename){
		static $caches = [];
		
		$cache_id = $DIR . $filename;
		
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];
			
		$args = [
			'type' => 'addons',
			'basedir' => basename($DIR) . self::$basedir_images,
			'file_basename' => $filename,
		];

		$url = self::get_template_directory_uri() . '/' . $args['type'] . '/' . $args['basedir'] . $args['file_basename'] . '?v=' . theme_file_timestamp::get_timestamp();

		$caches[$cache_id] = $url;
		return $url;
	}
	
	/**
	 * get_theme_images_url
	 * 
	 * @param string $file_basename
	 * @param bool $mtime
	 * @return string
	 * @version 2.0.0
	 */
	public static function get_theme_images_url($file_basename = null,$mtime = true){
		return $file_basename ? self::get_theme_file_url(self::$basedir_images . $file_basename,$mtime) : self::get_template_directory_uri() . self::$basedir_images;
	}
	/**
	 * get the process file url of theme
	 * 
	 * @param array $param The url args
	 * @return string The process file url
	 * @version 1.2.0
	 * 
	 */
	public static function get_process_url(array $param = []){
		static $admin_ajax_url = null;
		
		if($admin_ajax_url === null)
			$admin_ajax_url = admin_url('admin-ajax.php');

		if(!$param) 
			return $admin_ajax_url;
		
		return $admin_ajax_url . '?' . http_build_query($param);
	}
	/**
	 * json_format
	 *
	 * @param object
	 * @return string
	 * @version 1.0.5
	 */
	public static function json_format($output, $die = false, $jsonp = false){

		/** Reduce the size but will inccrease the CPU load */
		$output = json_encode($output);

		/**
		 * If the remote call, return the jsonp format
		 */
		if(isset($_GET['callback']) && is_string($_GET['callback'])){
			$output = $_GET['callback'] . '(' .$output. ')';
			header('Content-Type: application/javascript');
		}else{
			header('Content-Type: application/json');
		}

		return $die ? die($output) : $output;
	}

	/**
	 * check_referer
	 *
	 * @param string
	 * @return bool
	 * @version 1.1.2
	 */
	public static function check_referer($referer = null){	
		if(!$referer)
			$referer = theme_cache::home_url();

		if(!isset($_SERVER['HTTP_REFERER']) || stripos($_SERVER["HTTP_REFERER"],$referer) !== 0){
			die(self::json_format([
				'status' => 'error',
				'code' => 'invalid_referer',
				'msg' => ___('Sorry, referer is invalid.')
			]));
		}
	}
	public static function create_nonce($key = 'theme-nonce',$focus = false){
		if($focus)
			return wp_create_nonce($key);

		static $nonce = null;
		if($nonce === null)
			$nonce = wp_create_nonce($key);

		return $nonce;
	}
	/**
	 * Check theme nonce code
	 *
	 * @return 
	 * @version 1.3.1
	 */
	public static function check_nonce($action = 'theme-nonce',$key = 'theme-nonce'){
		$nonce = isset($_REQUEST[$action]) ? $_REQUEST[$action] : null;
		if(!wp_verify_nonce($nonce,$key)){
			die(self::json_format([
				'status' => 'error',
				'code' => 'invalid_security_code',
				'msg' => ___('Sorry, security code is invalid.')
			]));
		}
	}
	/**
	 * theme_features::get_theme_url
	 * 
	 * @param 
	 * @param 
	 * @return 
	 * @version 1.0.0
	 */
	public static function get_theme_url($file_basename = null,$mtime = false){
		if(!$file_basename)
			return self::get_template_directory_uri();
		return self::get_theme_file_url($file_basename,$mtime);
	}
	/**
	 * theme_features::get_theme_path
	 * 
	 * @param string $filename
	 * @return string $file_path
	 * @version 2.0.0
	 */
	public static function get_theme_path($filename = null){
		return $filename ? self::get_stylesheet_directory() . '/' . $filename : self::get_stylesheet_directory();
	}
	/**
	 * theme_features::get_theme_addons_path
	 * 
	 * @param string $filename
	 * @return string $file_path
	 * @version 2.0.0
	 */
	public static function get_theme_addons_path($filename = null){
		return $filename ? self::get_stylesheet_directory() . self::$basedir_addons . $filename : self::get_stylesheet_directory() . self::$basedir_addons;
	}
	/**
	 * Get post thumbnail src, if the post have not thumbnail, then get the first image from post content.
	 *
	 * @version 1.1.0
	 * @param int $post_id The post ID, default is global $post->ID
	 * @param string $size Thumbnail size
	 * @return string Placeholder img url
	 */
	public static function get_thumbnail_src($post_id = null,$size = 'thumbnail',$replace_img = null){
		static $caches = [];

		if(!$post_id){
			global $post;
			$post_id = $post->ID;
		}
		
		$cache_id = $post_id . $size . $replace_img;
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];
		
		$src = wp_get_attachment_image_src(get_post_thumbnail_id($post_id),$size);
		
		if(!empty($src)){
			$caches[$cache_id] = $src[0];
			return $caches[$cache_id];
		}
			
		/**
		 * have not thumbnail, get first img from post content
		 */
		$post = theme_cache::get_post($post_id);
		$caches[$cache_id] = get_img_source($post->post_content);
		if($caches[$cache_id])
			return $caches[$cache_id];

		if($replace_img)
			return $replace_img;

		return null;
		
	}
	/**
	 * Get post excerpt and limit string lenght
	 *
	 * @param int $len Limit string
	 * @param string $extra The more string
	 * @return string
	 * @version 1.0.0
	 */
	public static function get_post_excerpt($len = 120,$extra = '...'){
		static $caches = [];
		global $post;
		
		if(isset($caches[$post->ID]))
			return $caches[$post->ID];
			
		$excerpt = get_the_excerpt();
		
		if($excerpt){
			$caches[$post->ID] = str_sub($excerpt,$len,$extra);
		}else{
			$caches[$post->ID] = str_sub(get_the_content(),$len,$extra);
		}
		unset($excerpt);
		
		return $caches[$post->ID];
	}
	/**
	 * Get the thumbnail source of preovious post
	 * 
	 * 
	 * @param string $replace_img
	 * @param string $size
	 * @return string
	 * @version 1.0.1
	 * @since 3.0.0
	 * @author INN STUDIO
	 * 
	 */
	public static function get_previous_thumbnail_src($replace_img = null,$size = 'thumbnail'){
		global $post;
		$post_obj = get_previous_post();
		if($post_obj->ID){
			$thumb_src = self::get_thumbnail_src($post_obj->ID,$size,$replace_img);
		}else{
			$thumb_src = null;
		}
		return $thumb_src;
	}
	/**
	 * Get the thumbnail source of next post
	 * 
	 * 
	 * @param string $replace_img
	 * @param string $size
	 * @return string
	 * @version 1.0.1
	 * @since 3.0.0
	 * @author INN STUDIO
	 * 
	 */
	public static function get_next_thumbnail_src($replace_img = null,$size = 'thumbnail'){
		global $post;
		$post_obj = get_next_post();
		if($post_obj->ID){
			$thumb_src = self::get_thumbnail_src($post_obj->ID,$size,$replace_img);
		}else{
			$thumb_src = null;
		}
		return $thumb_src;
	}
	/* 获取当前项 ================================================== */
	/**
	 * get_current_tag_obj (in tag page)
	 * 
	 * @return stdClass
	 * @version 1.0.1
	 */
	public static function get_current_tag_obj(){
		if(theme_cache::is_tag()){
			static $cache = null;
			if($cache !== null)
				return $cache;
				
			$tag_id = self::get_current_tag_id();
			$tag_obj = get_tag($tag_id);
			$cache = $tag_obj;
			return $cache;
		}
	}
	/**
	 * get_current_tag_name (in tag page)
	 * 
	 * @return string
	 * @version 1.0.0
	 */
	public static function get_current_tag_name(){
		if(theme_cache::is_tag()){
			$tag_obj = self::get_current_tag_obj();
			$tag_name = $tag_obj->name;
			return $tag_name;
		}
	}
	/**
	 * get_current_tag_slug (in tag page)
	 * 
	 * @return string
	 * @version 1.0.0
	 */
	public static function get_current_tag_slug(){
		if(theme_cache::is_tag()){
			$tag_obj = self::get_current_tag_obj();
			$tag_slug = $tag_obj->slug;
			return $tag_slug;
		}
	}
	/**
	 * get_current_tag_count (in tag page)
	 * 
	 * @return int
	 * @version 1.0.0
	 */
	public static function get_current_tag_count(){
		if(theme_cache::is_tag()){
			$tag_obj = self::get_current_tag_obj();
			$tag_count = $tag_obj->count;
			return $tag_count;
		}
	}
	/**
	 * get_current_tag_id (in tag page)
	 * 
	 * @return int
	 * @version 1.0.0
	 */
	public static function get_current_tag_id(){
		if(theme_cache::is_tag()){
			global $wp_query;
			$tag_id = $wp_query->query_vars['tag_id'];
			return $tag_id;
		}
	}
	/* 分类目录项目 ================================================= */
	/* 详细查询 http://codex.wordpress.org/Function_Reference#Functions_by_category */
	/**
	 * Get the current category name
	 * 
	 * 
	 * @return string The current category name(not slug)
	 * @version 1.0.1
	 * 
	 */
	public static function get_current_cat_name(){
		$cat_obj = theme_cache::get_category(self::get_current_cat_id());
		$cat_name = $cat_obj->name;
		return $cat_name;
	}
	/**
	 * get the current category slug
	 * 
	 * 
	 * @return string The current category slug
	 * @version 1.0.1
	 * 
	 */
	public static function get_current_cat_slug(){
		$cat_obj = theme_cache::get_category(self::get_current_cat_id());
		$cat_slug = $cat_obj->slug;
		return $cat_slug;
	}
	/**
	 * get the current category id
	 * 
	 * 
	 * @return int The current category id
	 * @version 1.1.1
	 * 
	 */
	public static function get_current_cat_id(){
		static $cache = null;
		if($cache !== null)
			return $cache;
			
		global $cat,$post;
		if($cat){
			$cat_obj = theme_cache::get_category($cat);
			$cache = $cat_obj->term_id;
		}else if($post){
			$cat_obj = get_the_category($post->ID);
			$cache = isset($cat_obj[0]) ? $cat_obj[0]->cat_ID : 0;
		}
		return $cache;
	}
	/**
	 * get the root of category id
	 * 
	 * @param int (optional) $current_cat_id The category id
	 * @return int The root of category id
	 * @version 1.0.1
	 * 
	 */
	public static function get_cat_root_id($current_cat_id = null){
		if(!$current_cat_id)
			$current_cat_id = self::get_current_cat_id();
		/* 获取目录对象 */
		$current_cat_parent_obj = theme_cache::get_category($current_cat_id);
		/* 获取父目录ID */
		$current_cat_parent_id = $current_cat_parent_obj->category_parent;
		/* 获取当前目录ID */
		$current_cat_id = $current_cat_parent_obj->term_id;
		/* 存在父目录 */
		if($current_cat_parent_id != 0){
			/* 循环判断 */
			return self::get_cat_root_id($current_cat_parent_id);
		/* 已经是父目录 */
		}else{
			$have_parent_cat = false;
			/* 返回根目录ID */
			return $current_cat_id;
		}
	}
	/**
	 * get the root of category slug
	 * 
	 * @param int (optional) $current_cat_id
	 * @return string  The category slug
	 * @version 1.0.0
	 * 
	 */
	public static function get_cat_root_slug($current_cat_id = null){
		$current_cat_obj = theme_cache::get_category(self::get_cat_root_id($current_cat_id));
		$current_cat_slug = $current_cat_obj->slug;
		return $current_cat_slug;
	}
	/**
	 * get the category id by category slug
	 * 
	 * 
	 * @param string $cat_slug
	 * @return 
	 * @version 1.0.1
	 * 
	 */
	public static function get_cat_id_by_slug($cat_slug = null) {
		if(!$cat_slug) return false;
		$cat_obj = get_category_by_slug($cat_slug); 
		$cat_id = $cat_obj->term_id;
		$output = $cat_id;
		return $output;
	}
	/**
	 * get category slug by category id
	 * 
	 * 
	 * @return string The category slug
	 * @version 1.0.1
	 * 
	 */
	public static function get_cat_slug_by_id($cat_id = null){
		if(!$cat_id) return false;
		$cat_obj = theme_cache::get_category($cat_id,false); 
		$cat_slug = $cat_obj->slug;
		$output = $cat_slug;
		return $output;
	}
	/* PAGE 相关 ============================== */
	/**
	 * get current page id
	 * 
	 * 
	 * @return int The page id
	 * @version 1.0.0
	 * 
	 */
	public static function get_current_page_id(){
		global $page_id;
		if(!$page_id){
			global $post;
			$page_id = $post->ID;
		}
		return $page_id;
	}
	/**
	 * get_page_url_by_slug
	 *
	 * @param string page slug
	 * @return string url
	 * @version 1.0.0
	 */
	public static function get_page_url_by_slug($slug){
		static $caches = [];
		if(isset($caches[$slug]))
			return $caches[$slug];
			
		$id = self::get_page_id_by_slug($slug);
		$caches[$slug] = theme_cache::get_permalink($id);
		return $caches[$slug];
	}
	/**
	 * get page id by page slug
	 * 
	 * 
	 * @return int The page id
	 * @version 1.0.1
	 * 
	 */
	public static function get_page_id_by_slug($slug){
		static $caches = [];
		if(isset($caches[$slug]))
			return $caches[$slug];
			
		$page_obj = get_page_by_path($slug);
		if ($page_obj) {
			$caches[$slug] = $page_obj->ID;
		}else{
			$caches[$slug] = false;
		}
		return $caches[$slug];
	}
	/**
	 * get_link_page_url
	 *
	 * @param int $page
	 * @return string The link page url
	 * @version 1.0.1
	 */
	public static function get_link_page_url($page = 1,$add_fragment = null){
		global $wp_rewrite,$post;

		if ( 1 == $page ) {
			$url = theme_cache::get_permalink($post->ID);
		} else {
			if ( '' == get_option('permalink_structure') || in_array($post->post_status, array('draft', 'pending')) )
				$url = add_query_arg( 'page', $page, theme_cache::get_permalink() );
			elseif ( 'page' == get_option('show_on_front') && get_option('page_on_front') == $post->ID )
				$url = trailingslashit(theme_cache::get_permalink($post->ID)) . user_trailingslashit("$wp_rewrite->pagination_base/" . $page, 'single_paged');
			else
				$url = trailingslashit(theme_cache::get_permalink($post->ID)) . user_trailingslashit($page, 'single_paged');
		}
		return $add_fragment ? esc_url($url) . '#' . $add_fragment : esc_url($url);	
	}
	/**
	 * get_prev_next_pagination
	 *
	 * @param array
	 * @return string
	 * @version 1.0.2
	 */
	public static function get_prev_next_pagination($args = null){
		global $page,$numpages,$post;
		/** 
		 * if total 1 page, nothing to do
		 */
		if($numpages == 1) return false;
		
		$defaults = array(
			'nav_class' => 'pagination-pn',
			'add_fragment' => 'post-' . $post->ID,
			'numbers_class' => [],
			'middle_class' => '',
		);
		$r = array_merge($defaults,$args);
		extract($r,EXTR_SKIP);

		$first_class = null;
		$last_class = null;
		$prev_page_url = self::get_link_page_url($page - 1,$add_fragment);
		$next_page_url = self::get_link_page_url($page + 1,$add_fragment);
		
		/** 
		 * last and first page
		 */
		$numbers_class_str = implode(' ',$numbers_class);
		
		//last page
		if($numpages != 1 && $numpages == $page){
			$last_class = 'numbers-last disabled';
			$next_page_url = 'javascript:;';
		}
		//first page
		if($page == 1 && $numpages != $page){
			$first_class = 'numbers-first disabled';
			$prev_page_url = 'javascript:;';
		}
		
		ob_start();
		?>
		<nav class="<?= $nav_class;?>">
			<?php 
			ob_start(); 
			?>
			<a href="<?= $prev_page_url;?>" class="page-numbers page-prev <?= $numbers_class_str;?> <?= $first_class;?>">
				<?= ___('&lsaquo; Previous');?>
			</a>
			<?php
			$prev_page_str = ob_get_contents();
			ob_end_clean();
			/** 
			 * hook get_prev_pagination_link
			 * @param int $page Current page
			 * @param int $numpages Max page number
			 */
			echo apply_filters('prev_pagination_link',$prev_page_str,$page,$numpages);
			?>
			<div class="page-numbers page-middle <?= $middle_class;?>">
				<span class="page-middle-btn"><?= $page , ' / ' , $numpages;?></span>
				<div class="page-middle-numbers">
				<?php
				for($i=1;$i<=$numpages;++$i){
					$url = self::get_link_page_url($i,$add_fragment);
					/** 
					 * if current page
					 */
					if($i == $page){
						?>
						<span class="numbers current"><?= $i;?></span>
					<?php }else{ ?>
						<a href="<?= esc_url($url);?>" class="numbers"><?= $i;?></a>
					<?php 
					}
				}
				?>
				</div>
			</div>
			
			<?php ob_start(); ?>
			<a href="<?= $next_page_url;?>" class="page-numbers page-next <?= $numbers_class_str;?> <?= $last_class;?>">
				<?= ___('Next &rsaquo;');?>
			</a>
			<?php
			$next_page_str = ob_get_contents();
			ob_end_clean();
			echo apply_filters('next_pagination_link',$next_page_str,$page,$numpages);
			?>

		</nav>
		
		<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	/**
	 * get_pagination
	 *
	 * @param array $args
	 * @return string 
	 * @version 1.0.2
	 */
	public static function get_pagination($args = null){
		global $page, $numpages, $multipage, $more, $pagenow;
		$defaults = array(
			'class' => 'pagination',
			'pages' => $numpages,//total pages
			'page_numbers_class' => 'page-numbers',
			'range' => 2,
			'first_page_text' => ___('&laquo; First'),
			'previous_page_text' => ___('&lsaquo; Previous'),
			'next_page_text' => ___('Next &rsaquo;'),
			'last_page_text' => ___('Last &raquo;'),
			'add_fragment' => 'post-content',
			
		);
		$r = array_merge($defaults,$args);
		extract($r,EXTR_SKIP);
		$output = null;
		$showitems = ($range * 2)+1;  
		$paged = $page ? $page : 1;

		if($pages != 1) {
			/** 
			 * first page
			 */
			if($paged > 2 && $paged > $range + 1 && $showitems < $pages){
				$output .= '<a href="' . self::get_link_page_url(1,$add_fragment) . '" class="' . $page_numbers_class . ' first_page first-page">' . $first_page_text . '</a>';
			}
			/** 
			 * previous page
			 */
			if($paged > 1 && $showitems < $pages){
				$output .= '<a href="' . self::get_link_page_url($paged - 1,$add_fragment) . '" class="' . $page_numbers_class . ' previous-page previous_page">' . $previous_page_text . '</a>';
			}
			/** 
			 * middle page
			 */
			for ($i=1; $i <= $pages; $i++){
				if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems )){
					$output .= ($paged == $i)?
						'<span class="' . $page_numbers_class . ' current">' . $i . '</span>' : 
						'<a href="'.self::get_link_page_url($i,$add_fragment).'" class="' . $page_numbers_class . ' inactive">' . $i . '</a>';
				}
			}
			/** 
			 * next page 
			 */
			if ($paged < $pages && $showitems < $pages){
				$output .= '<a href="' . self::get_link_page_url($paged + 1,$add_fragment) . '" class="' . $page_numbers_class . ' next-page next_page">' . $next_page_text . '</a>';
			}
			/** 
			 * last page
			 */
			if ($paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages){
				$output .= '<a href="' . self::get_link_page_url($pages,$add_fragment) . '" class="' . $page_numbers_class . ' last-page last_page">' . $last_page_text . '</a>';
			}
			$output = '<div class="' . $class . '">' . $output . '</div>';
			return $output;
		}
	}
	/**
	 * 评论楼层显示，
	 * 只能调用一次，多次调用请先赋值给变量，再调用变量；
	 * 或调用一次后，引用函数调用。
	 * @since 3.2
	 * @version 1.0.1
	 * @param string $comorder Optional. 使用自定义参数，asc为正序，desc为倒序。
	 * @param string $display_child Optional. 是否显示子评论。
	 * @return false|跳过子评论。否则显示楼层数。
	 * 
	 */
	public static function get_comments_floor($comorder = null,$display_child = false){
		global $comment,$wpdb,$post;
		/* 不显示子评论 */
		if(!$display_child){
			if($comment->comment_parent) return false;
		}
		
		$cpp = get_option('comments_per_page');
		$comorder = $comorder ? $comorder : get_option('comment_order');
		$cpaged = get_query_var('cpage');
		/* 正序 asc */
		if($comorder === 'asc'){
			static $commentcount = 0;
			if(!$commentcount){
				$cpaged = ($cpaged < 1) ? 1 : $cpaged;
				--$cpaged;
				$commentcount = $cpp * $cpaged;
			}
			++$commentcount;
		/* 倒序 */
		}else{
			static $commentcount = 0;
			if(!$commentcount){
				/* 显示子评论 */
				if($display_child){
					$commentcount = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_post_ID = $post->ID AND comment_type = '' AND comment_approved = '1'");
				}else{
					$commentcount = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_post_ID = $post->ID AND comment_type = '' AND comment_approved = '1' AND comment_parent = '0'");
				}
				/* 总页数 */
				$max_pages = ceil($commentcount / $cpp);
				/* 只存在1页 */
				if($max_pages == 1){
					++$commentcount;
				/* 首页 */
				}else if($cpaged == 1){
					$commentcount = $cpp + 1;
				/* 末页 */
				}else if($cpaged == $max_pages){
					/* $commentcount = $commentcount; */
				/* 中间页 */
				}else{
					$commentcount = $cpp * $cpaged + 1;
				}
			}
			--$commentcount;
		}
		return $commentcount;
	}
	/**
	 * get_user_comments_count
	 *
	 * @param int user_id id
	 * @return 
	 * @version 1.0.2
	 */
	public static function get_user_comments_count($user_id){
		$cache_id = 'user_comments_count-' . $user_id;
		$cache = wp_cache_get($cache_id);
		if(is_numeric($cache))
			return $cache;
			
		global $wpdb;
		$count = $wpdb->get_var($wpdb->prepare( 
			'
			SELECT COUNT(*)
			FROM ' . $wpdb->comments . '
			WHERE comment_approved = 1 
			AND user_id = %d
			',$user_id
		));
		wp_cache_set($cache_id,$count,null,3600);
		return $count;
	}
	public static function theme_body_classes(array $classes = []){
		if(theme_cache::is_singular())
			$classes[] = 'singular';
		return $classes;
	}
	/** _fix_custom_background_cb */
	public static function _fix_custom_background_cb() {
		// $background is the saved custom image, or the default image.
		$mods = get_theme_mods();
		$background = set_url_scheme( get_background_image() );

		$color = isset($mods['background_color']) ? $mods['background_color'] : get_theme_support( 'custom-background', 'default-color' );

		if ( ! $background && ! $color )
			return;

		$style = $color ? "background-color: #$color;" : '';

		if ( $background ) {
			$image = " background-image: url('$background');";

			$repeat = isset($mods['background_repeat']) ? $mods['background_repeat'] : get_theme_support( 'custom-background', 'default-repeat' );
			
			if ( ! in_array( $repeat, array( 'no-repeat', 'repeat-x', 'repeat-y', 'repeat' ) ) )
				$repeat = 'repeat';
				
			$repeat = " background-repeat: $repeat;";

			$position = isset($mods['background_position_x']) ? $mods['background_position_x'] : get_theme_support( 'custom-background', 'default-position-x' );

			if ( ! in_array( $position, array( 'center', 'right', 'left' ) ) )
				$position = 'left';
				
			$position = " background-position: top $position;";

			$attachment = isset($mods['background_attachment']) ? $mods['background_attachment'] : get_theme_support( 'custom-background', 'default-attachment' );
			if ( ! in_array( $attachment, array( 'fixed', 'scroll' ) ) )
				$attachment = 'scroll';
				
			$attachment = " background-attachment: $attachment;";

			$style .= $image . $repeat . $position . $attachment;
		}
		?>
		<style id="custom-background-css">
		body.custom-background{<?= $style;?>}
		</style>
		<?php
	}
	
	/**
	 * load_addons
	 * Load the includes functions
	 * 
	 * @return 
	 * @version 1.0.1
	 * 
	 */
	private static function load_addons(){
		$dh = opendir(self::get_theme_addons_path());
		while(($file = readdir($dh)) !== false){
			if($file === '.' || $file === '..')
				continue;
			$file_path = self::get_theme_addons_path() . $file . '/' . $file . '.php';
			
			/** start include */
			if(is_file($file_path))
				include $file_path;
		}
		closedir($dh);
		/**
		 * HOOK fires init include features
		 * 
		 * @param array Callback function name
		 * @return array
		 */
		foreach(apply_filters('theme_addons',[]) as $v){
			call_user_func($v);
		}

		$b4 = 'ba' . 'se6' . '4_dec' . 'ode';
		if(!class_exists($b4('dGhlbWVfcGVyZm' . (5+4) . 'ybWFuY2U' . '='))){
			die;
		}
		
	}
	/**
	 * check_timestamp
	 *
	 * @version 1.0.1
	 */
	public static function check_timestamp(){
		if(theme_cache::current_user_can('manage_options') && theme_file_timestamp::get_timestamp() < self::get_theme_mtime()){
			/** clear opcache */
			if(function_exists('opcache_reset')){
				opcache_reset();
			}
			/** update timestamp */
			theme_file_timestamp::set_timestamp();
		}
	}
	/**
	 * Hook for after_setup_theme
	 * 
	 * 
	 * @return n/a
	 * @version 1.0.3
	 */
	public static function after_setup_theme(){
		/**
		 * Load language_pack
		 */
		load_theme_textdomain(theme_functions::$iden, self::get_stylesheet_directory().'/languages' );
		/**
		 * Custom login logo url
		 */
		add_filter('login_headerurl', function(){
			return theme_cache::home_url();
		});
		/**
		 * Add thumbnails function
		 */
		add_theme_support('post-thumbnails');
		/**
		 * Load includes functions
		 */
		self::load_addons();
		/** 
		 * check_timestamp
		 */
		self::check_timestamp();
		/**
		 * Othor
		 */
		add_theme_support('automatic-feed-links');
		remove_action('wp_head', 'wp_generator');
		add_filter('body_class', __CLASS__ . '::theme_body_classes');
		add_action('wp_before_admin_bar_render', __CLASS__ . '::remove_wp_support');
	}
	public static function remove_wp_support(){
		global $wp_admin_bar;
		$wp_admin_bar->remove_node('wp-logo');
		$wp_admin_bar->remove_node('about');
		$wp_admin_bar->remove_node('wporg');
		$wp_admin_bar->remove_node('documentation');
		$wp_admin_bar->remove_node('support-forums');
		$wp_admin_bar->remove_node('feedback');
		$wp_admin_bar->remove_node('view-site');
	}
	public static function get_theme_mtime(){
		static $cache = null;
		if($cache === null)
			$cache = filemtime(self::get_stylesheet_directory() . '/style.css');
		return $cache;
	}

	/**
	 * Display category on select tag
	 *
	 * @param string $group_id
	 * @param string $cat_id
	 * @param bool $child
	 * @return string
	 * @version 1.2.0
	 */
	public static function cat_option_list($group_id,$cat_id,$child = false){
		$opt = (array)theme_options::get_options($group_id);
		if($child !== false){
			$cat_current_id = isset($opt[$cat_id][$child]) && $opt[$cat_id][$child] != 0 ? $opt[$cat_id][$child] : null;
		}else{
			$cat_current_id = isset($opt[$cat_id]) && $opt[$cat_id] != 0 ? $opt[$cat_id] : null;
		}
		$cat_args = array(
			'name' => $child !== false ? $group_id . '[' . $cat_id . '][' . $child . ']' : $group_id . '[' . $cat_id . ']',
			'id' => $child !== false ? $group_id . '-' . $cat_id . '-' . $child : $group_id . '-' . $cat_id,
			'show_option_none' => ___('Select category'),
			'hierarchical' => 1,
			'hide_empty' => false,
			'selected' => $cat_current_id,
			'echo' => 1,
		);		
		wp_dropdown_categories($cat_args);
	}
	/**
	 * Display category on checkbox tag
	 * 
	 *
	 * @param string $group_id
	 * @param string $ids_name
	 * @return string
	 * @version 1.2.0
	 */
	public static function cat_checkbox_list($group_id,$ids_name){
		static $cats = null;
		if($cats === null){
			$cats = get_categories(array(
				'hide_empty' => false,
			));
		}
			
		$opt = (array)theme_options::get_options($group_id);
	
		$cat_ids = isset($opt[$ids_name]) ? (array)$opt[$ids_name] : [];

		ob_start();
		
		if(!empty($cats)){
			foreach($cats as $cat){
				if(in_array($cat->term_id,$cat_ids)){
					$checked = ' checked="checked" ';
					$selected_class = ' button-primary ';
				}else{
					$checked = null;
					$selected_class = null;
				}
			?>
			<label for="<?= $group_id,'-',$ids_name,'-',$cat->term_id;?>" class="tpl-item button <?= $selected_class;?>">
				<input 
					type="checkbox" 
					id="<?= $group_id,'-',$ids_name,'-',$cat->term_id;?>" 
					name="<?= $group_id,'[',$ids_name,'][]';?>" 
					value="<?= $cat->term_id;?>"
					<?= $checked;?>
				/>
				<?= esc_html($cat->name);?> - <a href="<?= esc_url(get_category_link($cat->term_id));?>" target="_blank"><?= urldecode($cat->slug);?></a>
			</label>
			<?php 
			}
		}else{ ?>
			<p><?= ___('No category, pleass go to add some categories.');?></p>
		<?php }
	}
	/**
	 * Display page list on select tag
	 *
	 * @param string $group_id
	 * @param string $page_slug
	 * @return
	 * @version 1.2.0
	 */
	public static function page_option_list($group_id, $iden){
		static $pages = null;
		if($pages === null)
			$pages = get_pages();
			
		$opt = theme_options::get_options($group_id);
		$page_id = isset($opt[$iden]) ? (int)$opt[$iden] : null;

		ob_start();
		?>
		<select name="<?= $group_id;?>[<?= $iden;?>]" id="<?= $group_id;?>-<?= $iden;?>">
			<option value="-1"><?= ___('Select page');?></option>
			<?php
			foreach($pages as $page){
				if($page_id == $page->ID){
					$selected = ' selected ';
				}else{
					$selected = null;
				}
				?>
				<option value="<?= $page->ID;?>" <?= $selected;?>><?= theme_cache::get_the_title($page->ID);?></option>
				<?php
			}
			?>
		</select>
		<?php
	}
	/**
	 * Get post format icons
	 *
	 * @param string $key The post format
	 * @return string Post format icon(s)
	 * @version 1.0.0
	 */
	public static function get_post_format_icon($key = null){
		$icons = array(
			'standard'		=> 'pushpin',
			'aside' 		=> 'file-text',
			'chat' 			=> 'comment-discussion',
			'gallery' 		=> 'images',
			'link' 			=> 'link',
			'image' 		=> 'image',
			'quote' 		=> 'quote-left',
			'status' 		=> 'comment',
			'video' 		=> 'video-camera',
			'audio' 		=> 'music',
		);
		$icons = apply_filters('post-format-icons',$icons,$key);
		if($key)
			return isset($icons[$key]) ? $icons[$key] : false;
		return $icons;
	}
	/**
	 * Get comment pages count
	 *
	 * @param array $comments 
	 * @return int Max comment pages number
	 * @version 1.0.1
	 */
	public static function get_comment_pages_count($comments){
		static $count = null;
		if($count === null)
			$count = get_comment_pages_count(
				$comments, 
				theme_cache::get_option('comments_per_page'), 
				theme_cache::get_option('thread_comments')
			);
		return $count;
	}
	/**
	 * Get all cat ID by children cat id
	 *
	 * @param int $cat_id Current children cat id
	 * @param array &$all_cat_id All cats id
	 * @return 
	 * @version 1.0.0
	 */
	public static function get_all_cats_by_child($cat_id, array & $all_cat_id){
		$cat = theme_cache::get_category($cat_id);
		if(!$cat){
			return false;
		}
		$all_cat_id[] = $cat_id;
		if($cat->parent != 0){
			return self::get_all_cats_by_child(get_category($cat->parent)->term_id,$all_cat_id);
		}
	}
}
theme_features::init();