<?php
/**
 * @version 2.0.0
 */
class theme_page_nagination_ajax{
	public static function init(){
		add_filter('frontend_js_config',	__CLASS__ . '::frontend_js_config');
		
		add_action('wp_ajax_' . __CLASS__,	__CLASS__ . '::process');
		add_action('wp_ajax_nopriv_' . __CLASS__,	__CLASS__ . '::process');
		
	}
	private static function is_enabled(){
		global $post, $numpages;
		return theme_cache::is_singular() && $numpages > 1;
	}
	public static function process(){

		theme_features::check_referer();

		$post_id = isset($_GET['post-id']) && is_numeric($_GET['post-id']) ? (int)$_GET['post-id'] : false;
		if(!$post_id)
			die(theme_features::json_format([
				'status' => 'error',
				'code' => 'invaild_post_id',
				'msg' => ___('Sorry, post id is invaild.'),
			]));
			
		global $post,$page;
		/**
		 * post
		 */
		$post = theme_cache::get_post($post_id);
		if(!$post)
			die(theme_features::json_format([
				'status' => 'error',
				'code' => 'post_not_exist',
				'msg' => ___('Sorry, the post does not exist.'),
			]));

		/**
		 * page
		 */
		$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : false;
		if(!$page)
			die(theme_features::json_format([
				'status' => 'error',
				'code' => 'invaild_page_number',
				'msg' => ___('Sorry, page number is invaild.'),
			]));

		set_query_var('page',$page);
		setup_postdata($post);
		ob_start();
		
		if(class_exists('theme_img_lazyload'))
			remove_filter('the_content','theme_img_lazyload::the_content');
			
		the_content();
		$content = html_minify(ob_get_contents());
		ob_end_clean();

		die(theme_features::json_format([
			'status' => 'success',
			'content' => $content,
		]));
	}
	public static function frontend_js_config(array $config){
		if(!self::is_enabled())
			return $config;
		global $post,$page,$numpages;
			if($page < 1)
				$page = 1;
			if($page > $numpages)
				$page = $numpages;

		$config[__CLASS__] = [
			'process_url' => theme_features::get_process_url([
				'action' => __CLASS__,
				'post-id' => $post->ID,
			]),
			'post_id' => $post->ID,
			'numpages' => $numpages,
			'page' => $page,
			'url_tpl' => theme_features::get_link_page_url(9999),
			'lang' => [
				'M02' => ___('Content loaded.'),
				'M03' => ___('Already first page.'),
				'M04' => ___('Already last page.'),
				'E01' => ___('Sorry, server is busy now, can not respond your request, please try again later.'),
			],
		];
		return $config;
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_page_nagination_ajax::init';
	return $fns;
});