<?php
/**
 * theme_page_cats
 *
 * @version 1.0.2
 */
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_page_cats::init';
	return $fns;
});
class theme_page_cats{
	
	public static $page_slug = 'cats-index';
	
	public static function init(){
		
		add_action('init',__CLASS__ . '::page_create');

		add_action('page_settings', 		__CLASS__ . '::display_backend');

		add_action('wp_ajax_' . __CLASS__, __CLASS__ . '::process');
		
		add_filter('theme_options_save', 	__CLASS__ . '::options_save');

		add_filter('backend_js_config',__CLASS__ . '::backend_js_config');
	}
	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = theme_options::get_options(__CLASS__);
		if($key)
			return isset($caches[$key]) ? $caches[$key] : false;
		return $caches;
	}
	public static function options_save($opts){
		if(isset($_POST[__CLASS__]))
			$opts[__CLASS__] = $_POST[__CLASS__];
		return $opts;
	}
	public static function display_backend(){
		$opt = self::get_options();
		?>
		<fieldset>
			<legend><i class="fa fa-fw fa-sitemap"></i> <?= ___('Categories index settings');?></legend>
			<p class="description"><?= ___('Display posts number or alphabet slug index on categories index page.')?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th><?= ___('Index Categories');?></th>
						<td>
							<?= theme_features::cat_checkbox_list(__CLASS__,'cats');?>
						</td>
					</tr>
					<tr>
						<th><?= ___('Control');?></th>
						<td>
							<div id="<?= __CLASS__;?>-tip-clean-cache"></div>
							<p>
							<a href="javascript:;" class="button" id="<?= __CLASS__;?>-clean-cache" data-tip-target="<?= __CLASS__;?>-tip-clean-cache"><i class="fa fa-refresh"></i> <?= ___('Flush cache');?></a>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function process(){
		theme_features::check_referer();
		if(!theme_cache::current_user_can('manage_options')) 
			die;
		$output = [];
		wp_cache_delete(__CLASS__);
		$output['status'] = 'success';
		$output['msg'] = ___('Cache has been cleaned.');
		die(theme_features::json_format($output));
	}
	public static function page_create(){
		if(!theme_cache::current_user_can('manage_options')) 
			return false;
		
		$page_slugs = array(
			self::$page_slug => array(
				'post_content' 	=> '',
				'post_name'		=> self::$page_slug,
				'post_title'	=> ___('Categories index'),
				'page_template'	=> 'page-' . self::$page_slug . '.php',
			)
		);
		
		$defaults = array(
			'post_content' 		=> '[post_content]',
			'post_name' 		=> null,
			'post_title' 		=> null,
			'post_status' 		=> 'publish',
			'post_type'			=> 'page',
			'comment_status'	=> 'closed',
		);
		foreach($page_slugs as $k => $v){
			theme_cache::get_page_by_path($k) || wp_insert_post(array_merge($defaults,$v));
		}

	}
	public static function get_slugs(){
		global $post;
		
		$cats = (array)self::get_options('cats');
		$new_tags = [];
		/**
		 * get all whitelist posts & tag ids
		 */
		$query = new WP_Query(array(
			'nopaging' => 1,
			'category__in' => $cats,
			'ignore_sticky_posts' => true,
		));
		if($query->have_posts()){
			/** load pinyin */
			foreach($query->posts as $post){
				/** 提取别名是数字或英文开头的 */
				$first_letter_pattern = '/^[a-z0-9]/';
				$first_letter = $post->post_name[0];

				preg_match($first_letter_pattern,$first_letter,$matches);
				if(!empty($matches[0])){
					if(isset($new_tags[$first_letter][$post->ID]))
						continue;
					$new_tags[$first_letter][$post->ID] = $post->ID;
					continue;
				}
			}
			wp_reset_postdata();
		}else{
			return false;
		}
		unset($query);
		wp_reset_query();
		return $new_tags;
	}
	public static function display_frontend(){
		$cache = theme_cache::get(__CLASS__);
		if(!empty($cache)){
			echo $cache;
			unset($cache);
			return;
		}
		ob_start();
		$slugs = self::get_slugs();
		if(empty($slugs)){
			?><div class="page-tip"><?= status_tip('info',___('No cagtegory yet.'));?></div><?php
			return false;
		}
		global $post;
		ksort($slugs);
		foreach($slugs as $k => $post_ids){
		?>
			<div class="panel-tags-index mod panel">
				<div class="heading">
					<h2 class="title">
						<span class="bg">
							<span class="tx"><?= strtoupper($k);?></span>
							<small> - <?= ___('Initial');?></small>
						</span>
					</h2>
				</div>
				<div class="row">
					<?php
					$query = new WP_Query(array(
						'nopaging' => true,
						'post__in' => $post_ids,
						'ignore_sticky_posts' => true,
					));
					foreach($query->posts as $post){
						setup_postdata($post);
						theme_functions::archive_card_xs([
							'classes' => 'g-phone-1-2 g-tablet-1-3 g-desktop-1-4',
						]);
					}
					unset($query);
					wp_reset_postdata();
					?>
				</div>
			</div>
			<?php
		}
		$cache = ob_get_contents();
		ob_end_clean();
		wp_cache_set(__CLASS__,$cache,null,86400);/** 24 hours */
		echo $cache;
		unset($cache,$slugs);
	}
	public static function backend_js_config(array $config){
		$config[__CLASS__] = [
			'process_url' => theme_features::get_process_url(array('action'=>__CLASS__)),
		];
		return $config;
	}
}