<?php
/** 
 * @version 1.0.0
 */
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_custom_edit::init';
	return $fns;
});
class theme_custom_edit{
	public static $iden = 'theme_custom_edit';
	public static $page_slug = 'account';
	public static $pages = [];

	public static function init(){
		
		// henson add
		if(current_user_can('manage_options') || current_user_can( 'publish_pages' )) {
		    foreach(self::get_tabs() as $k => $v){
		    	$nav_fn = 'filter_nav_' . $k; 
		    	add_filter('account_navs',__CLASS__ . "::$nav_fn",$v['filter_priority']);
		    }
		}
		
		add_filter('wp_title', __CLASS__ . '::wp_title',10,2);
	}
	public static function wp_title($title, $sep){
		if(!self::is_page()) 
			return $title;
			
		if(self::get_tabs(get_query_var('tab'))){
			$title = self::get_tabs(get_query_var('tab'))['text'];
		}
		return $title . $sep . theme_cache::get_bloginfo('name');
	}
	public static function filter_query_vars($vars){
		if(!in_array('tab',$vars)) $vars[] = 'tab';
		return $vars;
	}
	public static function filter_nav_edit($navs){
		$navs['edit'] = '<a href="' . esc_url(self::get_tabs('edit')['url']) . '">
			<i class="fa fa-' . self::get_tabs('edit')['icon'] . ' fa-fw"></i> 
			' . self::get_tabs('edit')['text'] . '
		</a>';
		return $navs;
	}
	public static function get_url(){
		static $cache = null;
		if($cache === null)
			$cache = theme_cache::get_permalink( theme_cache::get_page_by_path(self::$page_slug)->ID);
		return $cache;
	}
	public static function get_tabs($key = null){
		$baseurl = self::get_url();
		$tabs = [
			'edit' => [
				'text' => ___('My posts'),
				'icon' => 'lightbulb-o',
				'url' => esc_url(add_query_arg('tab','edit',$baseurl)),
				'filter_priority' => 22,
			],
		];
		if($key){
			return isset($tabs[$key]) ? $tabs[$key] : false;
		}
		return $tabs;
	}
	public static function get_edit_post_link($post_id){
		static $caches = [];
		if(!isset($caches[$post_id]))
			$caches[$post_id] = esc_url(theme_custom_contribution::get_tabs('post')['url'] . '&post=' . $post_id);
		return $caches[$post_id];
	}
	public static function get_query(){
		global $paged;
		return new WP_Query([
			'author' => theme_cache::get_current_user_id(),
			'posts_per_page' => 20,
			'post_status' => ['publish','pending'],
			'post_type' => 'post',
			'paged' => (int)$paged,
		]);
		
	}
	public static function is_page(){
		static $cache = null;
		if($cache === null)
			$cache = theme_cache::is_page(self::$page_slug) && self::get_tabs(get_query_var('tab'));
			
		return $cache;
	}
}