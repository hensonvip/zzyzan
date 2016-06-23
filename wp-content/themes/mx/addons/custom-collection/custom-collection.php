<?php
/** 
 * @version 1.0.1
 */

class theme_custom_collection{
	public static $page_slug = 'account';
	public static $file_exts = array('png','jpg','gif','jpeg');
	public static $thumbnail_size = 'large';


	public static function init(){
		add_filter('frontend_js_config',	__CLASS__ . '::frontend_js_config');

		add_filter('theme_options_save', 	__CLASS__ . '::options_save');
		add_filter('theme_options_default', 	__CLASS__ . '::options_default');
		
		
		add_action('wp_ajax_' . __CLASS__, __CLASS__ . '::process');

		add_action('page_settings',			__CLASS__ . '::display_backend');

		self::add_editor_style();
		
		if(!self::is_enabled())
			return;
			
		foreach(self::get_tabs() as $k => $v){
			$nav_fn = 'filter_nav_' . $k; 
			add_filter('account_navs',__CLASS__ . "::$nav_fn",$v['filter_priority']);
		}

		add_filter('wp_title',				__CLASS__ . '::wp_title',10,2);

	}

	public static function add_editor_style(){
	    add_editor_style([theme_features::get_theme_addons_css(__DIR__,'editor',true)]);
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
	public static function filter_nav_collection($navs){
		$navs['collection'] = '<a href="' . self::get_tabs('collection')['url'] . '">
			<i class="fa fa-' . self::get_tabs('collection')['icon'] . ' fa-fw"></i> 
			' . self::get_tabs('collection')['text'] . '
		</a>';
		return $navs;
	}
	public static function get_cat_ids(){
		return self::get_options('cats');
	}
	public static function is_enabled(){
		return self::get_options('enabled') == 1;
	}
	public static function display_backend(){
		$opt = (array)self::get_options();
		?>
		<fieldset>
			<legend><i class="fa fa-fw fa-leanpub"></i> <?= ___('Collection settings');?></legend>
			<table class="form-table">
				<tr>
					<th><label for="<?= __CLASS__;?>-enabled"><?= ___('Enable or not?');?></label></th>
					<td>
						<select name="<?= __CLASS__;?>[enabled]" id="<?= __CLASS__;?>-enabled" class="widefat">
							<?php the_option_list(-1,___('Disable'),self::get_options('enabled'));?>
							<?php the_option_list(1,___('Enable'),self::get_options('enabled'));?>
						</select>
					</td>
				</tr>
				<tr>
					<th><?= ___('Which categories will be added after submit?');?></th>
					<td>
						<?php theme_features::cat_checkbox_list(__CLASS__,'cats');?>
					</td>
				</tr>
				<tr>
					<th><label for="<?= __CLASS__;?>-tags-number"><?= ___('Shows tags number');?></label></th>
					<td>
						<input class="short-text" type="number" name="<?= __CLASS__;?>[tags-number]" id="<?= __CLASS__;?>-tags-number" value="<?= (int)self::get_options('tags-number');?>" min="1">
					</td>
				</tr>
				<tr>
					<th><label for="<?= __CLASS__;?>-posts-min-number"><?= ___('Post boxes min number');?></label></th>
					<td>
						<input class="short-text" type="number" name="<?= __CLASS__;?>[posts-min-number]" id="<?= __CLASS__;?>-posts-min-number" value="<?= (int)self::get_options('posts-min-number');?>" min="1">
					</td>
				</tr>
				<tr>
					<th><label for="<?= __CLASS__;?>-posts-max-number"><?= ___('Post boxes max number');?></label></th>
					<td>
						<input class="short-text" type="number" name="<?= __CLASS__;?>[posts-max-number]" id="<?= __CLASS__;?>-posts-max-number" value="<?= (int)self::get_options('posts-max-number');?>" min="1">
					</td>
				</tr>
				<tr>
					<th><label for="<?= __CLASS__;?>-description"><?=esc_html(___('You can write some description for collection page header. Please use tag <p> to wrap your HTML codes.'));?></label></th>
					<td>
						<textarea name="<?= __CLASS__;?>[description]" id="<?= __CLASS__;?>-description" class="widefat" rows="5"><?= self::get_des();?></textarea>
					</td>
				</tr>
			</table>
		</fieldset>
		<?php
	}
	public static function get_list_tpl(array $args = []){
		$args = array_merge([
			'post_id' => null,
			'hash' => '%hash%',
			'url' => '%url%',
			'title' => '%title%',
			'thumbnail' => '%thumbnail%',
			'content' => '%content%',
			'preview' => true,
		],$args);
		
		if($args['preview'] === false){
			$args['url'] = theme_cache::get_permalink($args['post_id']);
		}
		
		$args['content'] = strip_tags($args['content'],'<b><strong><del><span><img><br><em><i>');

		$target = $args['preview'] === true ? ' target="_blank" ' : null;
		$href = $args['preview'] === true ? '#clt-list-' . $args['hash'] : $args['url'];

		$attr_title = $args['preview'] === true ? ___('Click to locate the source') : $args['title'];
		ob_start();
?>

<p class="list-group-item">
	<a href="<?= $href;?>" title="<?= $attr_title;?>">
		<span class="row">
			<span class="thumbnail-container" >
				<img src="<?= $args['thumbnail'];?>" width="<?= theme_functions::$thumbnail_size[1];?>" height="<?= theme_functions::$thumbnail_size[2];?>" alt="<?= $args['title'];?>" class="collection-list-thumbnail">
			</span>
			<span class="list-group-body">
				<span class="list-group-item-heading"><?= $args['title'];?></span>
				<span class="list-group-item-text"><?= $args['content'];?></span>
			</span>
		</span>
	</a>
</p>
		<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	public static function get_input_tpl($placeholder){
		ob_start();
		?>
<div class="clt-list row" id="clt-list-<?= $placeholder; ?>" data-id="<?= $placeholder;?>">
	<div class="g-tablet-1-3 g-desktop-1-6">
		<div class="clt-list-thumbnail-container">
			<div id="clt-list-thumbnail-preview-container-<?= $placeholder;?>" class="clt-list-thumbnail-preview-container">
				<img id="clt-list-thumbnail-<?= $placeholder;?>" src="<?= theme_functions::$thumbnail_placeholder;?>" title="<?= ___('Post preview');?>" alt="" class="clt-list-thumbnail-preview">
				<input type="hidden" id="clt-list-thumbnail-url-<?= $placeholder ;?>" name="clt[posts][<?= $placeholder;?>][thumbnail-url]" value="<?= theme_functions::$thumbnail_placeholder;?>">
			</div>
		</div>
		<a href="javascript:;" id="clt-list-del-<?= $placeholder;?>" class="clt-list-del btn btn-xs btn-danger btn-block"><i class="fa fa-trash"></i> <?= ___('Delete this item');?></a>
	</div>
	<div class="g-tablet-2-3 g-desktop-5-6 clt-list-area-tx">
		<div class="row">
			<div class="g-phone-1-3">
				<input type="number" class="form-control clt-list-post-id" id="clt-list-post-id-<?= $placeholder ;?>" name="clt[posts][<?= $placeholder;?>][post-id]" placeholder="<?= ___('Post ID');?>" title="<?= ___('Please write the post ID number, e.g. 4015.');?>" min="1" required >
			</div>
			<div class="g-phone-2-3">
				<input type="text" name="clt[posts][<?= $placeholder;?>][post-title]" id="clt-list-post-title-<?= $placeholder;?>" class="form-control clt-list-post-title" placeholder="<?= ___('The recommended post title');?>" title="<?= ___('Please write the recommended post title.');?>" required >
			</div>
		</div>

		<textarea name="clt[posts][<?= $placeholder;?>][post-content]" id="clt-list-post-content-<?= $placeholder;?>" rows="4" class="form-control clt-list-post-content" placeholder="<?= ___('Why recommend the post? Talking about your point.');?>" title="<?= ___('Why recommend the post? Talking about your point.');?>" required ></textarea>
	</div>
</div>
		<?php
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__]))
			$opts[__CLASS__] = $_POST[__CLASS__];
		return $opts;
	}
	public static function options_default(array $opts = []){
		$opts[__CLASS__] = [
			'enabled' => 1,
			'posts-min-number' => 5,
			'posts-max-number' => 10,
			'tags-number' => 10,
			'description' => '<p>' . ___('Welcome to collection page, you can fill in the post ID and make them as a collection to share you favorite posts.') . '</p>',
		];
		return $opts;
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
	public static function get_url(){
		static $cache = null;
		if($cache === null){
			$cache = theme_cache::get_permalink(theme_cache::get_page_by_path(self::$page_slug)->ID);
		}
		return $cache;
	}
	public static function get_des(){
		return stripslashes(self::get_options('description'));
	}
	public static function get_posts_number($type){
		return (int)self::get_options('posts-'. $type . '-number');
	}
	public static function get_tabs($key = null){
		$baseurl = self::get_url();
		$tabs = array(
			'collection' => array(
				'text' => ___('New collection'),
				'icon' => 'leanpub',
				'url' => esc_url(add_query_arg('tab','collection',$baseurl)),
				'filter_priority' => 25,
			),
		);
		if($key)
			return isset($tabs[$key]) ? $tabs[$key] : false;
		return $tabs;
	}
	public static function is_page(){
		static $cache = null;
		if($cache === null)
			$cache = theme_cache::is_page(self::$page_slug) && self::get_tabs(get_query_var('tab'));
			
		return $cache;
	}
	private static function wp_get_attachment_image_src(){
		static $caches = [];
		$cache_id = md5(serialize(func_get_args()));
		if(!isset($caches[$cache_id]))
			$caches[$cache_id] = call_user_func_array('wp_get_attachment_image_src',func_get_args());
		return $caches[$cache_id];
	}
	public static function process(){
		$output = [];
		
		theme_features::check_referer();
		theme_features::check_nonce();
		
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
		switch($type){
			/**
			 * case upload
			 */
			case 'add-cover':
				/** 
				 * if not image
				 */
				$filename = isset($_FILES['img']['name']) ? $_FILES['img']['name'] : null;
				$file_ext = $filename ? array_slice(explode('.',$filename),-1,1)[0] : null;
				$file_ext = strtolower($file_ext);
				if(!in_array($file_ext,self::$file_exts)){
					$output['status'] = 'error';
					$output['code'] = 'invaild_file_type';
					$output['msg'] = ___('Invaild file type.');
					die(theme_features::json_format($output));
				}
				/** rename file name */
				$_FILES['img']['name'] = theme_cache::get_current_user_id() . '-' . current_time('YmdHis') . '-' . rand(100,999). '.' . $file_ext;
				
				/** 
				 * pass
				 */
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				require_once( ABSPATH . 'wp-admin/includes/media.php' );

				$attach_id = media_handle_upload('img',0);
				if(is_wp_error($attach_id)){
					$output['status'] = 'error';
					$output['code'] = $attach_id->get_error_code();
					$output['msg'] = $attach_id->get_error_message();
					die(theme_features::json_format($output));
				}else{
					$output['status'] = 'success';
					$output['thumbnail'] = [
						'url' => 
						esc_url(self::wp_get_attachment_image_src($attach_id,'thumbnail')[0])
					];
				
					$output['attach-id'] = $attach_id;
					$output['msg'] = ___('Upload success.');
					die(theme_features::json_format($output));
				}
				break;
			/**
			 * post
			 */
			case 'post':
				$clt = isset($_POST['clt']) && is_array($_POST['clt']) ? $_POST['clt'] : null;
				if(is_null_array($clt)){
					$output['status'] = 'error';
					$output['code'] = 'invaild_ctb_param';
					$output['msg'] = ___('Invaild collection param.');
					die(theme_features::json_format($output));
				}
				/**
				 * get posts
				 */
				$posts = isset($clt['posts']) && is_array($clt['posts']) ? $clt['posts'] : null;
				if(empty($posts)){
					$output['status'] = 'error';
					$output['code'] = 'invaild_posts';
					$output['msg'] = ___('Sorry, posts can not be empty.');
					die(theme_features::json_format($output));
				}
				/**
				 * post title
				 */
				$post_title = isset($clt['post-title']) && is_string($clt['post-title']) ? esc_html(trim($clt['post-title'])) : null;
				if(empty($post_title)){
					$output['status'] = 'error';
					$output['code'] = 'invaild_post_title';
					$output['msg'] = ___('Please write the post title.');
					die(theme_features::json_format($output));
				}
				
				/**
				 * check thumbnail cover
				 */
				$thumbnail_id = isset($clt['thumbnail-id']) && is_numeric($clt['thumbnail-id']) ? (int)$clt['thumbnail-id'] : null;
				if(empty($thumbnail_id)){
					$output['status'] = 'error';
					$output['code'] = 'invaild_thumbnail_id';
					$output['msg'] = ___('Please set an image as post thumbnail');
					die(theme_features::json_format($output));
				}
				/**
				 * post content
				 */
				$post_content = isset($clt['post-content']) && is_string($clt['post-content']) ? strip_tags(trim($clt['post-content']),'<del><a><b><strong><em><i>') : null;
				if(empty($post_content)){
					$output['status'] = 'error';
					$output['code'] = 'invaild_post_content';
					$output['msg'] = ___('Please explain why you recommend this collection.');
					die(theme_features::json_format($output));
				}
				/**
				 * get posts template
				 */
				$post_content = '<p>' . $post_content . '</p>' . self::get_preview($posts);
				/**
				 * tags
				 */
				$tags = isset($clt['tags']) && is_array($clt['tags']) ? $clt['tags'] : [];
				if(!empty($tags)){
					$tags = array_map(function($tag){
						if(!is_string($tag)) return null;
						return $tag;
					},$tags);
				}
				/**
				 * post status
				 */
				if(theme_cache::current_user_can('moderate_comments')){
					$post_status = 'publish';
				}else{
					$post_status = 'pending';
				} 
				/**
				 * insert
				 */
				$post_id = wp_insert_post(array(
					'post_title' => $post_title,
					'post_content' => fliter_script($post_content),
					'post_status' => $post_status,
					'post_author' => theme_cache::get_current_user_id(),
					'post_category' => (array)self::get_options('cats'),
					'tags_input' => $tags,
				),true);
				if(is_wp_error($post_id)){
					$output['status'] = 'error';
					$output['code'] = $post_id->get_error_code();
					$output['msg'] = $post_id->get_error_message();
				}else{
					/** set post thumbnail */
					set_post_thumbnail($post_id,$thumbnail_id);
					/**
					 * pending status
					 */
					if($post_status === 'pending'){
						$output['status'] = 'success';
						$output['msg'] = sprintf(
							___('Your collection submitted successful, it will be published after approve in a while. Thank you very much! How about %s again?'),
							'<a href="' . self::get_tabs('collection')['url'] . '">' . ___('write a new collection') . '</a>'
						);
						die(theme_features::json_format($output));
					}else{
						$output['status'] = 'success';
						$output['msg'] = sprintf(
							___('Congratulation! Your post has been published. You can %s or %s.'),
							'<a href="' . theme_cache::get_permalink($post_id) . '" title="' . theme_cache::get_the_title($post_id) . '">' . ___('View it now') . '</a>',
							'<a href="' . self::get_tabs('collection')['url'] . '">' . ___('countinue to write a new collection') . '</a>'
						);

						/**
						 * add point
						 */
						if(class_exists('theme_custom_point')){
							$post_publish_point = theme_custom_point::get_point_value('post-publish');
							$output['point'] = array(
								'value' => $post_publish_point,
								'detail' => ___('Post published'),
							);
						}
						die(theme_features::json_format($output));
					}
					
				}
				break;
			/**
			 * get post
			 */
			case 'get-post':
				$post_id = isset($_REQUEST['post-id']) && is_numeric($_REQUEST['post-id']) ? $_REQUEST['post-id'] : null;
				if(!$post_id){
					$output['status'] = 'error';
					$output['code'] = 'invaild_post_id';
					$output['msg'] = ___('Sorry, the post id is invaild.');
					die(theme_features::json_format($output));
				}

				
				global $post;
				$post = theme_cache::get_post($post_id);
				if(!$post || $post->post_type !== 'post'){
					$output['status'] = 'error';
					$output['code'] = 'post_not_exist';
					$output['msg'] = ___('Sorry, the post do not exist, please type another post ID.');
				//echo(json_encode($output));
					die(theme_features::json_format($output));
				}
				setup_postdata($post);
				$output = [
					'status' 	=> 'success',
					'msg' 		=> ___('Finished get the post data.'),
					'thumbnail' => [
						'url' => theme_functions::get_thumbnail_src($post_id),
						'size' => [
							theme_functions::$thumbnail_size[1],
							theme_functions::$thumbnail_size[2],
						]
					],
					'title' 	=> theme_cache::get_the_title($post_id),
					'excerpt' 	=> html_minify(str_sub(strip_tags(trim($post->post_content)),120,'...')),
				];
				wp_reset_postdata();
				die(theme_features::json_format($output));
		}

		die(theme_features::json_format($output));
	}
	private static function get_preview(array $posts = []){
		
		/**
		 * check posts count number
		 */
		$count = count($posts);
		if($count < self::get_posts_number('min')){
			$output['status'] = 'error';
			$output['code'] = 'not_enough_posts';
			$output['msg'] = ___('Sorry, your posts are not enough, please add more posts.');
			die(theme_features::json_format($output));
		}
		if($count > self::get_posts_number('max')){
			$output['status'] = 'error';
			$output['code'] = 'too_many_posts';
			$output['msg'] = ___('Sorry, your post are too many, please reduce some posts and try again.');
			die(theme_features::json_format($output));
		}

		/**
		 * template
		 */
		$tpl = '';
		/**
		 * check each posts value
		 */
		foreach($posts as $k => $v){
			/** post id */
			$post_id = isset($v['post-id']) && is_string($v['post-id']) ? trim($v['post-id']) : null;
			if(empty($post_id)){
				$output['status'] = 'error';
				$output['code'] = 'invaild_post_content';
				$output['list-id'] = $k;
				$output['msg'] = ___('Sorry, the post id is invaild, please try again.');
				die(theme_features::json_format($output));
			}
			/** title */
			$title = isset($v['post-title']) && is_string($v['post-title']) ? strip_tags(trim($v['post-title'])) : null;
			if(empty($title)){
				$output['status'] = 'error';
				$output['code'] = 'invaild_post_title';
				$output['list-id'] = $k;
				$output['msg'] = ___('Sorry, the post title is invaild, please try again.');
				die(theme_features::json_format($output));
			}
			/** content */
			$content = isset($v['post-content']) && is_string($v['post-content']) ? trim($v['post-content']) : null;
			if(empty($content)){
				$output['status'] = 'error';
				$output['code'] = 'invaild_post_content';
				$output['list-id'] = $k;
				$output['msg'] = ___('Sorry, the post content is invaild, please try again.');
				die(theme_features::json_format($output));
			}
			/** thumbmail */
			$thumbnail = isset($v['thumbnail-url']) && is_string($v['thumbnail-url']) ? esc_url(trim($v['thumbnail-url'])) : null;
			if(empty($thumbnail)){
				$output['status'] = 'error';
				$output['code'] = 'invaild_post_thumbnail';
				$output['list-id'] = $k;
				$output['msg'] = ___('Sorry, the post thumbnail is invaild, please try again.');
				die(theme_features::json_format($output));
			}
			/** check post exists */
			$url = esc_url(theme_cache::get_permalink($v['post-id']));
			if(empty($url)){
				$output['status'] = 'error';
				$output['code'] = 'post_not_exist';
				$output['list-id'] = $k;
				$output['msg'] = ___('Sorry, the post do not exist, please try again.');
				die(theme_features::json_format($output));
			}
			/**
			 * create template
			 */
			$tpl .= self::get_list_tpl([
				'post_id' => $post_id,
				'preview' => false,
				'hash' => $k,
				'url' => $url,
				'thumbnail' => $thumbnail,
				'title' => $title,
				'content' => $content,
			]);
		}

		return '<div class="collection-list list-group">' . html_minify($tpl) . '</div>';
	}
	public static function frontend_js_config(array $config){
		if(!self::is_page()) 
			return $config;
			
		$config[__CLASS__] = [
			'process_url' => theme_features::get_process_url(array('action' => __CLASS__)),
			'min_posts' => self::get_posts_number('min'),
			'max_posts' => self::get_posts_number('max'),
			'tpl_input' => self::get_input_tpl('%placeholder%'),
			'tpl_preview' => self::get_list_tpl([
				'preview' => true,
			]),
			'lang' => [
				'M01' => ___('Loading, please wait...'),
				'M02' => ___('A item has been deleted.'),
				'M03' => ___('Getting post data, please wait...'),
				'M04' => ___('Previewing, please wait...'),
				'E01' => ___('Sorry, server is busy now, can not respond your request, please try again later.'),
				'E02' => sprintf(___('Sorry, the minimum number of posts is %d.'),self::get_posts_number('min')),
				'E03' => sprintf(___('Sorry, the maximum number of posts is %d.'),self::get_posts_number('max')),
				'E04' => ___('Sorry, the post id must be number, please correct it.')
			],
		];
		return $config;
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_custom_collection::init';
	return $fns;
});