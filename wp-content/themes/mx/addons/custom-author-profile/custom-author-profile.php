<?php
/**
 * @version 1.0.2
 */
class theme_custom_author_profile{
	public static $cache_expire = 43200;

	public static $user_meta_key = array(
		'followers_count' 	=> 'followers_count',
		'following_count' 	=> 'following_count',
		'follower' 			=> 'follower',
		'following' 		=> 'following',
	);
	public static function init(){
		add_filter('query_vars',			__CLASS__ . '::filter_query_vars');
		
		add_filter('wp_title',				__CLASS__ . '::wp_title',10,2);
		
	}
	public static function wp_title($title, $sep){
		if(!is_author())
			return $title;
			
		global $author;
		$tab_active = get_query_var('tab');
		$author_display_name = theme_cache::get_the_author_meta('display_name',$author);
		$tabs = self::get_tabs(null,$author);
		if(!empty($tab_active) && isset($tabs[$tab_active])){
			$title = $tabs[$tab_active]['text'];
		}else{
			$title = $tabs['profile']['text'];
		}
		$title = $author_display_name . $sep . $title;
		return $title . $sep . theme_cache::get_bloginfo('name');
	}
	public static function filter_query_vars($vars){
		if(!in_array('tab',$vars)) $vars[] = 'tab';
		if(!in_array('page',$vars)) $vars[] = 'page';
		return $vars;
	}
	public static function get_count($key,$user_id){

		$cache_id = 'user-count-' . $user_id;
		$caches = (array)wp_cache_get($cache_id);
		switch($key){
			case 'works':
				if(!isset($caches['works'])){
					$caches['works'] = (int)count_user_posts($user_id);
					wp_cache_set($cache_id,$caches,null,self::$cache_expire);
				}
				return $caches['works'];
			case 'comments':
				if(!isset($caches['comments'])){
					$caches['comments'] = (int)theme_features::get_user_comments_count($user_id);
					wp_cache_set($cache_id,$caches,null,self::$cache_expire);
				}
				return $caches['comments'];
			case 'followers_count':
				return (int)get_user_meta($user_id,self::$user_meta_key['followers_count'],true);
			case 'following_count':
				return (int)get_user_meta($user_id,self::$user_meta_key['following_count'],true);
		}
	}
	public static function get_roles($user_id_or_role){
		$roles = [
			'subscriber' => [
				// 'name' => ___('Author'),
				'name' => '订阅者',
				'label' => 'default',
			],
			'contributor' => [
				// 'name' => ___('Author'),
				'name' => '投稿者',
				'label' => 'warning',
			],
			'author' => [
				// 'name' => ___('Certification Author'),
				'name' => '作者',
				'label' => 'primary',
			],
			'editor' => [
				// 'name' => ___('Editor'),
				'name' => '编辑',
				'label' => 'success',
			],
			'administrator' => [
				// 'name' => ___('Editor'),
				'name' => '管理员',
				'label' => 'danger',
			],
		];
		if(is_numeric($user_id_or_role)){
			$user_id_or_role = array_keys(theme_cache::get_the_author_meta('wp_capabilities',$user_id_or_role));
			$user_id_or_role = isset($user_id_or_role[0]) ? $user_id_or_role[0] : 'contributor';
		}
		return isset($roles[$user_id_or_role]) ? $roles[$user_id_or_role] : false;
	}
	public static function get_tabs($key,$author_id){
		static $caches = [], $baseurl = [];
		
		if(isset($caches[$author_id])){
			if($key)
				return isset($caches[$author_id][$key]) ? $caches[$author_id][$key] : null;
			return $caches[$author_id];
		}
		
		if(!isset($baseurl[$author_id]))
			$baseurl[$author_id] = theme_cache::get_author_posts_url($author_id);
			
		$caches[$author_id] = array(
			'profile' => array(
				'text' => ___('Profile'),
				'icon' => 'newspaper-o',
				'url' => esc_url($baseurl[$author_id])
			),
			'works' => array(
				'text' => ___('Works'),
				'icon' => 'file-text',
				'url' => esc_url(add_query_arg('tab','works',$baseurl[$author_id])),
				'count' => self::get_count('works',$author_id),
			),
			'comments' => array(
				'text' => ___('Comments'),
				'icon' => 'comments',
				'url' => esc_url(add_query_arg('tab','comments',$baseurl[$author_id])),
				'count' => self::get_count('comments',$author_id),
			),
			
			//'followers' => array(
			//	'text' => ___('Followers'),
			//	'icon' => 'venus-double',
			//	'url' => esc_url(add_query_arg('tab','followers',$baseurl[$author_id])),
			//	'count' => self::get_count('followers_count',$author_id),
			//),
			//'following' => array(
			//	'text' => ___('Following'),
			//	'icon' => 'venus-mars',
			//	'url' => esc_url(add_query_arg('tab','following',$baseurl[$author_id])),
			//	'count' => self::get_count('following_count',$author_id),
			//)
		);
		if(class_exists('custom_post_point')){
			$caches[$author_id]['rates'] = array(
				'text' => ___('Rates'),
				'icon' => 'star',
				'url' => esc_url(add_query_arg('tab','rates',$baseurl[$author_id])),
				'count' => self::get_count('followers_count',$author_id),
			);
		}
		if($key){
			
			return isset($caches[$author_id][$key]) ? $caches[$author_id][$key] : null;
		}
		return $caches[$author_id];
	}

}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_custom_author_profile::init';
	return $fns;
});