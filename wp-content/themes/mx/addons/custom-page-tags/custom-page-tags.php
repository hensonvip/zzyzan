<?php
/**
 * theme_page_tags
 *
 * @version 1.1.0
 */
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_page_tags::init';
	return $fns;
});
class theme_page_tags{
	
	public static $page_slug = 'tags-index';

	private static $user_query;
	private static $tags = [];
	
	public static function init(){
		add_action('init',					__CLASS__ . '::page_create');

		add_action('page_settings', 		__CLASS__ . '::display_backend');
		
		add_filter('theme_options_save', 	__CLASS__ . '::options_save');

		add_action('wp_ajax_' . __CLASS__, __CLASS__ . '::process');
		add_action('wp_ajax_nopriv_' . __CLASS__, __CLASS__ . '::process');

		add_filter('backend_js_config', __CLASS__ . '::backend_js_config'); 
	}
	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = theme_options::get_options(__CLASS__);
		if($key)
			return isset($caches[$key]) ? $caches[$key] : false;
		return $caches;
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__])){
			$opts[__CLASS__] = $_POST[__CLASS__];
		}
		return $opts;
	}
	public static function display_backend(){
		$opt = self::get_options();
		?>
		<fieldset>
			<legend><i class="fa fa-fw fa-tags"></i> <?= ___('Tags index settings');?></legend>
			<p class="description"><?= ___('Display Chinese pinyin tag name index on tags index page.')?></p>
			<table class="form-table">
				<tbody>
				<tr>
					<th><?= ___('Whitelist - users ');?></th>
					<td>
						<textarea name="<?= __CLASS__;?>[whitelist][user-ids]" id="<?= __CLASS__;?>-whitelist-user-ids" rows="3" class="widefat code"><?= isset($opt['whitelist']['user-ids']) ? esc_textarea($opt['whitelist']['user-ids']) : null;?></textarea>
						<p class="description"><?= ___('User ID, multiple users separated by ,(commas). E.g. 1,2,3,4');?></p>
						<?php
						if(isset($opt['whitelist']['user-ids']) && !empty($opt['whitelist']['user-ids'])){
							$user_display_names = [];
							foreach(explode(',',$opt['whitelist']['user-ids']) as $id){
								$user_display_names[] = '<a href="' . theme_cache::get_author_posts_url($id) . '" target="_blank">' . $id . '-' . theme_cache::get_the_author_meta('display_name',$id) . '</a>';
							}
							echo sprintf(___('User list: %s'),implode('&nbsp;&nbsp;',$user_display_names));?>
						<?php } ?>
					</td>
				</tr>
				<tr>
					<th><?= ___('Control');?></th>
					<td>
						<a href="javascript:;" class="button" id="<?= __CLASS__;?>-clean-cache"><i class="fa fa-refresh"></i> <?= ___('Flush cache');?></a>
					</td>
				</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function process(){
		theme_features::check_referer();
		$output = [];

		$type = isset($_GET['type']) && is_string($_GET['type']) ? $_GET['type'] : null;
		
		switch($type){
			case 'clean-cache':
				wp_cache_delete('display-frontend',__CLASS__);
				$output['status'] = 'success';
				$output['msg'] = ___('Cache has been cleaned.');
				break;
		}
		
		die(theme_features::json_format($output));
	}
	public static function get_url(){
		static $cache = null;
		if($cache === null)
			$cache = theme_cache::get_permalink(theme_cache::get_page_by_path(self::$page_slug)->ID);
		return $cache;	
	}
	public static function page_create(){
		if(!theme_cache::current_user_can('manage_options')) 
			return false;
		
		$page_slugs = array(
			self::$page_slug => array(
				'post_content' 	=> '',
				'post_name'		=> self::$page_slug,
				'post_title'	=> ___('Tags index'),
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
	private static function get_tag_name_pinyin($tag){
		$cache_group_id = 'tag-name-pinyin';
		$cache = wp_cache_get($tag['term_id'],$cache_group_id);
		
		if($cache)
			return $cache;
			
		/** 提取首字母 */
		$first_letter_pattern = '/^[a-z]/i';
		$first_letter = strtolower(mb_substr($tag['name'],0,1));
		preg_match($first_letter_pattern,$first_letter,$matches);
		/**
		 * 存在字母开头的标签
		 */
		if(!empty($matches[0])){
			wp_cache_set($tag['term_id'],$first_letter,$cache_group_id);
			return $first_letter;
		}
		
		/**
		 * 标签是中文开头
		 */
		$utf8_tagname = mb_convert_encoding($tag['name'],'utf-8','ascii,gb2312,gbk,utf-8');
		preg_match("/^[\x{4e00}-\x{9fa5}]/u",$utf8_tagname,$matches);
		/**
		 * 不是中文，跳到下一个
		 */
		if(empty($matches[0])){
			wp_cache_set($tag['term_id'],false,$cache_group_id);
			return $first_letter;
		}
		if(!class_exists('Overtrue\Pinyin\Pinyin'))
			include __DIR__ . '/inc/Pinyin/Pinyin.php';
		$double_pinyins = ['zh','ch','sh','ou','ai','ang','an'];
		$tag_pinyin = Overtrue\Pinyin\Pinyin::pinyin($tag['name']);
		$tag_two_pinyin = strtolower(substr($tag_pinyin,0,2));
		/**
		 * 判断巧舌音
		 */
		if(in_array($tag_two_pinyin,$double_pinyins)){
			wp_cache_set($tag['term_id'],$tag_two_pinyin,$cache_group_id);
			return $tag_two_pinyin;
		/**
		 * 单音
		 */
		}else{
			$tag_one_pinyin = mb_substr($tag_pinyin,0,1);
			wp_cache_set($tag['term_id'],$tag_one_pinyin,$cache_group_id);
			return $tag_one_pinyin;
		}
	}
	public static function get_tags($posts){
		if(!$posts)
			return false;
		foreach($posts as $post){
			$tags = get_the_tags($post->ID);
			if(!$tags)
				continue;
			foreach($tags as $tag){
				self::save_tags($tag->term_id,$tag->name,$post->ID);
			}
		}
		unset($posts);
		$new_tags = [];
		foreach(self::$tags as $tag_id => $tag){
			$initial = self::get_tag_name_pinyin([
				'term_id' => $tag_id,
				'name' => $tag['name'],
			]);
			if(!$new_tags[$initial]){
				$new_tags[$initial] = [
					$tag_id => self::$tags[$tag_id]
				];
			}else{
				$new_tags[$initial][$tag_id] = self::$tags[$tag_id];
			}
		}
		ksort($new_tags);
		self::$tags = null;
		return $new_tags;
	}

	private static function save_tags($tag_id,$tag_name,$post_id){
		if(!isset(self::$tags[$tag_id]))
			self::$tags[$tag_id] = [
				'name' => $tag_name,
				'post_ids' => [],
			];
		self::$tags[$tag_id]['post_ids'][$post_id] = $post_id;
	}
	private static function sprint_r($data){
		echo '<pre>';
		print_r($data);
		echo '</pre>';die;
	}
	public static function display_frontend(){
		set_time_limit(0);
		
		$cache_id = 'display-frontend';
		$cache = theme_cache::get($cache_id,__CLASS__);
		//$cache = false;
		if(!empty($cache)){
			echo $cache;
			unset($cache);
			return;
		}
		ob_start();

		$whitelist =(array)self::get_options('whitelist');
		if(isset($whitelist['user-ids']) && !empty($whitelist['user-ids'])){
			$whitelist =  explode(',',$whitelist['user-ids']);
		}else{
			$whitelist = null;
		}
		global $post;
		$query = new WP_Query([
			'author__in' => $whitelist,
			'ignore_sticky_posts' => true,
			'nopaging' => true,
			'post_type' => 'post',
		]);
		if(!$query->have_posts()){
			self::no_content(__('No posts found.'));
			return false;
		}
		//var_dump(count($query->posts));die;
		
		$pinyin_tags = self::get_tags($query->posts);
		unset($query);
		
//var_dump($pinyin_tags);die;
		if(!$pinyin_tags){
			self::no_content(__('No tags found.'));
			return false;
		}
		foreach($pinyin_tags as $initial => $tags){
			//var_dump($tags);die;
			?>
			<div class="panel-tags-index mod panel">
				<div class="heading">
					<h2 class="title">
						<span class="bg">
							<span class="tx"><?= strtoupper($initial);?></span>
							<small> - <?= ___('Pinyin initial');?></small>
						</span>
					</h2>
				</div>
				<div class="row">
					<?php foreach($tags as $tag_id => $tag){ ?>
						<div class="g-phone-1-2 g-tablet-1-3 g-desktop-1-4">
							<a href="<?= esc_url(get_tag_link($tag_id));?>" class="tags-title" target="_blank"><?= $tag['name'];?></a> 
							<small>(<?= count($tag['post_ids']);?>)</small>
						</div>
					<?php } ?>
				</div>
			</div>
			<?php
		}
		unset($pinyin_tags);
		$cache = html_minify(ob_get_contents());
		ob_end_clean();
		wp_cache_set($cache_id,$cache,__CLASS__,86400*7);/** 7days */
		echo $cache;
		unset($cache);
	}
	private static function no_content($msg){
		?>
		<div class="page-tip"><?= status_tip('info',$msg);?></div>
		<?php
	}
	public static function backend_js_config(array $config){
		$config[__CLASS__] = [
			'process_url' => theme_features::get_process_url([
				'action'=>__CLASS__,
				'type' => 'clean-cache',
			]),
		];
		return $config;
	}
	public static function is_page(){
		static $cache = null;
		if($cache === null)
			$cache = theme_cache::is_page(self::$page_slug);

		return $cache;
	}
	
}