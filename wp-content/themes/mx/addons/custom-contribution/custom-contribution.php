<?php
/** 
 * @version 1.0.4
 */
class theme_custom_contribution{
	public static $page_slug = 'account';
	public static $file_exts = ['png','jpg','gif'];
	public static $thumbnail_size = 'large';

	public static function init(){
	
		add_filter('frontend_js_config', __CLASS__ . '::frontend_js_config');

		add_filter('theme_options_save', __CLASS__ . '::options_save');
		add_filter('theme_options_default', __CLASS__ . '::options_default');
		
		add_action('wp_ajax_' . __CLASS__, __CLASS__ . '::process');

		// henson add
		if(current_user_can('manage_options') || current_user_can( 'publish_pages' )) {
		    foreach(self::get_tabs() as $k => $v){
		    	$nav_fn = 'filter_nav_' . $k; 
		    	add_filter('account_navs', __CLASS__ . "::$nav_fn",$v['filter_priority']);
		    }
		}

		add_filter('wp_title', __CLASS__ . '::wp_title',10,2);

		add_action('page_settings', __CLASS__ . '::display_backend');


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
	public static function filter_nav_post($navs){
		$navs['post'] = '<a href="' . esc_url(self::get_tabs('post')['url']) . '">
			<i class="fa fa-' . self::get_tabs('post')['icon'] . ' fa-fw"></i> 
			' . self::get_tabs('post')['text'] . '
		</a>';
		return $navs;
	}
	public static function get_des(){
		return stripslashes(self::get_options('description'));
	}
	public static function is_pending_after_edited(){
		return self::get_options('pending-after-edited') == 1;
	}
	public static function display_backend(){
		$opt = (array)self::get_options();
		?>
		<fieldset>
			<legend><i class="fa fa-fw fa-paint-brush"></i> <?= ___('Contribution settings');?></legend>
			<p class="description"><?= ___('About contribution setting.');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th><?= ___('Shows categories');?></th>
						<td>
							<?php theme_features::cat_checkbox_list(__CLASS__,'cats');?>
						</td>
					</tr>
					<tr>
						<th><label for="<?= __CLASS__;?>-tags-number"><?= ___('Shows tags number');?></label></th>
						<td>
							<input class="short-text" type="number" name="<?= __CLASS__;?>[tags-number]" id="<?= __CLASS__;?>-tags-number" value="<?= isset($opt['tags-number']) ?  $opt['tags-number'] : 6;?>">
						</td>
					</tr>
					<tr>
						<th><label for="<?= __CLASS__;?>-pending-after-edited"><?= ___('Pending after edited');?></label></th>
						<td>
							<select name="<?= __CLASS__;?>[pending-after-edited]" id="<?= __CLASS__;?>-pending-after-edited" class="widefat">
								<?php the_option_list(-1,___('Disable'),self::get_options('pending-after-edited'));?>
								<?php the_option_list(1,___('Enable'),self::get_options('pending-after-edited'));?>
							</select>
							<p class="description"><?= ___('After the post of contributor published, if contributor edit the post which post status will become to pending if enable.');?></p>
						</td>
					</tr>
					<tr>
						<th><label for="<?= __CLASS__;?>-description"><?= htmlentities(___('You can write some description for contribution page header. Please use tag <p> to wrap your HTML codes.'));?></label></th>
						<td>
							<textarea name="<?= __CLASS__;?>[description]" id="<?= __CLASS__;?>-description" class="widefat" rows="5"><?= self::get_des();?></textarea>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function options_save(array $opts = []){
		if(!isset($_POST[__CLASS__]))
			return $opts;

		$opts[__CLASS__] = $_POST[__CLASS__];
		return $opts;
	}
	public static function options_default(array $opts = []){
		$opts[__CLASS__]['tags-number'] = 6;
		return $opts;
	}
	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = (array)theme_options::get_options(__CLASS__);
			
		if($key)
			return isset($caches[$key]) ? $caches[$key] : false;
		return $caches;
	}
	public static function get_cat_ids(){
		return self::get_options('cats');
	}
	public static function get_url(){
		static $cache = null;
		if($cache === null){
			$cache = theme_cache::get_permalink(theme_cache::get_page_by_path(self::$page_slug)->ID);
		}
		return $cache;
	}
	public static function get_tabs($key = null){
		$baseurl = self::get_url();
		$tabs = array(
			'post' => array(
				'text' => isset($_GET['post']) ? ___('Edit post') : ___('Post contribution'),
				'icon' => 'paint-brush',
				'url' => esc_url(add_query_arg('tab','post',$baseurl)),
				'filter_priority' => 20,
			),
		);
		if($key){
			return isset($tabs[$key]) ? $tabs[$key] : false;
		}
		return $tabs;
	}
	public static function is_page(){
		static $cache = null;
		if($cache === null)
			$cache = theme_cache::is_page(self::$page_slug) && self::get_tabs(get_query_var('tab'));
			
		return $cache;
	}

	private static function wp_get_attachment_image_src($attachment_id, $size = 'thumbnail'){
		static $caches = [];
		$cache_id = $attachment_id . $size;
		if(!isset($caches[$cache_id]))
			$caches[$cache_id] = call_user_func_array('wp_get_attachment_image_src',func_get_args());

		return $caches[$cache_id];
	}
	public static function in_edit_post_status($post_status){
		return in_array($post_status, ['publish','pending']);
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
			case 'upload':
				self::process_upload();
				break;
			/**
			 * post
			 */
			case 'post':
				self::process_post();
				break;
			default:
		}

		die(theme_features::json_format($output));
	}
	private static function process_upload(){
		$output = [];
		/** 
		 * if not image
		 */
		$filename = isset($_FILES['img']['name']) ? $_FILES['img']['name'] : null;
		$file_ext = $filename ? array_slice(explode('.',$filename),-1,1)[0] : null;
		$file_ext = strtolower($file_ext);
		if(!$file_ext || !in_array($file_ext,self::$file_exts)){
			$output['status'] = 'error';
			$output['code'] = 'invaild_file_type';
			$output['msg'] = ___('Invaild file type.');
			die(theme_features::json_format($output));
		}
		/** rename file name */
		//$_FILES['img']['name'] = theme_cache::get_current_user_id() . '-' . current_time('YmdHis') . '-' . rand(100,999). '.' . $file_ext;
		
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
			$output = self::get_thumbnail_data($attach_id,$output);
			$output['status'] = 'success';
			$output['attach-id'] = $attach_id;
			$output['msg'] = ___('Upload success.');
			die(theme_features::json_format($output));
		}
	}
	private static function process_post(){
		$output = [];
		$ctb = isset($_POST['ctb']) && is_array($_POST['ctb']) ? array_filter($_POST['ctb']) : null;
		
		/** check ctb object */
		if(empty($ctb)){
			$output['status'] = 'error';
			$output['code'] = 'invaild_ctb_param';
			$output['msg'] = ___('Invaild contribution param.');
			die(theme_features::json_format($output));
		}
				
		$edit_post_id = isset($_POST['post-id']) && is_numeric($_POST['post-id']) ? (int)$_POST['post-id'] : 0;

		$edit_again = false;

		/**
		 * check edit
		 */
		if($edit_post_id != 0){
			/** set edit again */
			$edit_again = true;
			
			//self::set_once_published($edit_post_id);
			
			/**
			 * check post exists
			 */
			$old_post = theme_cache::get_post($edit_post_id);
			if(!$old_post || 
				$old_post->post_type !== 'post' || 
				!self::in_edit_post_status($old_post->post_status)
			){
				die(theme_features::json_format([
					'status' => 'error',
					'code' => 'post_not_exist',
					'msg' => ___('Sorry, the post does not exist.'),
				]));
			}
			/**
			 * check post author is myself
			 */
			if($old_post->post_author != theme_cache::get_current_user_id()){
				die(theme_features::json_format([
					'status' => 'error',
					'code' => 'post_not_exist',
					'msg' => ___('Sorry, you are not the post author, can not edit it.'),
				]));
			}
			/**
			 * check post edit lock status
			 */
			$lock_user_id = self::wp_check_post_lock($edit_post_id);
			if($lock_user_id){
				die(theme_features::json_format([
					'status' => 'error',
					'code' => 'post_not_exist',
					'msg' => ___('Sorry, the post does not exist.'),
				]));
			}
		}
		/**
		 * post title
		 */
		$post_title = isset($ctb['post-title']) && is_string($ctb['post-title']) ? trim($ctb['post-title']) : null;
		if(!$post_title){
			$output['status'] = 'error';
			$output['code'] = 'invaild_post_title';
			$output['msg'] = ___('Please write the post title.');
			die(theme_features::json_format($output));
		}
		/**
		 * post excerpt
		 */
		$post_excerpt = isset($ctb['post-excerpt']) && is_string($ctb['post-excerpt']) ? trim($ctb['post-excerpt']) : null;
		
		/**
		 * post content
		 */
		$post_content = isset($ctb['post-content']) && is_string($ctb['post-content']) ? trim($ctb['post-content']) : null;
		if(!$post_content){
			$output['status'] = 'error';
			$output['code'] = 'invaild_post_content';
			$output['msg'] = ___('Please write the post content.');
			die(theme_features::json_format($output));
		}
		/**
		 * check thumbnail cover
		 */
		$thumbnail_id = isset($ctb['thumbnail-id']) && is_numeric($ctb['thumbnail-id']) ? (int)$ctb['thumbnail-id'] : null;
		if(!$thumbnail_id){
			$output['status'] = 'error';
			$output['code'] = 'invaild_thumbnail_id';
			$output['msg'] = ___('Please set an image as post thumbnail');
			die(theme_features::json_format($output));
		}
		/**
		 * cats
		 */
		if($edit_post_id == 0){
			/** new post */
			$cat_ids = isset($ctb['cats']) && is_array($ctb['cats']) ? $ctb['cats'] : null;
			if(is_null_array($cat_ids)){
				$output['status'] = 'error';
				$output['code'] = 'invaild_cat_id';
				$output['msg'] = ___('Please select a category.');
				die(theme_features::json_format($output));
			}
			/** edit post */
		}else{
			/**
			 * get all cats
			 */
			$cat_id = isset($ctb['cat']) && is_numeric($ctb['cat']) ? (int)$ctb['cat'] : null;
			if(empty($cat_id)){
				$output['status'] = 'error';
				$output['code'] = 'invaild_cat_id';
				$output['msg'] = ___('Please select a category.');
				die(theme_features::json_format($output));
			}
			$cat_ids = [];
			theme_features::get_all_cats_by_child($cat_id,$cat_ids);
		}
		/**
		 * tags
		 */
		$tags = isset($ctb['tags']) && is_array($ctb['tags']) ? array_filter($ctb['tags']) : [];
		if(!empty($tags)){
			$tags = array_map(function($tag){
				if(!is_string($tag)) 
					return null;
				return $tag;
			},$tags);
		}
		/**
		 * post status
		 */
		if(theme_cache::current_user_can('publish_posts')){
			$post_status = 'publish';
		}else{
			$post_status = 'pending';
		} 
		
		/*****************************
		 * PASS ALL, WRITE TO DB
		 *****************************/
		/** edit post */
		if($edit_post_id != 0){
			$post_status = self::get_update_post_status($old_post->post_status);
			$post_id = wp_update_post([
				'ID' => $edit_post_id,
				'post_title' => $post_title,
				'post_status' => $post_status,
				'post_type' => $old_post->post_type,
				'post_excerpt' => fliter_script($post_excerpt),
				'post_content' => fliter_script($post_content),
				'post_category' => $cat_ids,
				'tags_input' => $tags,
			],true);
			
		/**
		 * insert post
		 */
		}else{
			$post_id = wp_insert_post([
				'post_title' => $post_title,
				'post_excerpt' => fliter_script($post_excerpt),
				'post_content' => fliter_script($post_content),
				'post_status' => $post_status,
				'post_author' => theme_cache::get_current_user_id(),
				'post_category' => $cat_ids,
				'tags_input' => $tags,
			],true);
		}
		/**
		 * check error
		 */
		if(is_wp_error($post_id)){
			$output['status'] = 'error';
			$output['code'] = $post_id->get_error_code();
			$output['msg'] = $post_id->get_error_message();
			die(theme_features::json_format($output));
		}/** end post error */
		
		/** set post thumbnail */
		set_post_thumbnail($post_id,$thumbnail_id);
			
		/**
		 * set attachment parent
		 */
		$attach_ids = isset($ctb['attach-ids']) && is_array($ctb['attach-ids']) ? array_map('intval',array_filter($ctb['attach-ids'])) : null;
		if($attach_ids){
			/** set attachment post parent */
			foreach($attach_ids as $attach_id){
				$post = theme_cache::get_post($attach_id);
				if(!$post || $post->post_type !== 'attachment')
					continue;
				wp_update_post([
					'ID' => $attach_id,
					'post_parent' => $post_id,
				]);
			}
		}/** end set post thumbnail */

		/**
		 * if new post
		 */
		if($edit_post_id == 0){
			/**
			 * pending status
			 */
			if($post_status === 'pending'){
				$output['status'] = 'success';
				$output['msg'] = ___('Your post submitted successful, it will be published after approve in a while.');
				die(theme_features::json_format($output));
			}else{
				$output['status'] = 'success';
				$output['msg'] = sprintf(
					___('Congratulation! Your post has been published. You can %s or %s.'),
					'<a href="' . theme_cache::get_permalink($post_id) . '" title="' . theme_cache::get_the_title($post_id) . '">' . ___('View it now') . '</a>',
					'<a href="javascript:location.href=location.href;">' . ___('countinue to write a new post') . '</a>'
				);

				/**
				 * add point
				 */
				if($edit_again && class_exists('theme_custom_point')){
					$post_publish_point = theme_custom_point::get_point_value('post-publish');
					$output['point'] = array(
						'value' => $post_publish_point,
						'detail' => ___('Post published'),
					);
				}/** end point */
			}/** end post status */
		}else{
			$output['status'] = 'success';
			if($old_post->post_status == 'publish'){
				$output['msg'] = ___('Your post has updated successful.') . ' <a href="' . theme_cache::get_permalink($post_id) . '" target="_blank">' . ___('Views it now') . '</a>';
			}else{
				$output['msg'] = ___('Your post has updated successful.');
			}
			die(theme_features::json_format($output));
		}/** end post edit */
			
		die(theme_features::json_format($output));
	}
	public static function set_once_published($post_id){
		if(self::is_once_published($post_id))
			return false;
		$cache_id = __CLASS__ . '-once_published';
		$post_ids = self::get_once_published();
		$post_ids[] = $post_id;
		wp_cache_set($cache_id,$post_ids);
		return true;
	}
	public static function get_once_published(){
		$cache_id = __CLASS__ . '-once_published';
		return array_filter((array)wp_cache_get($cache_id));
	}
	public static function is_once_published($post_id){
		$cache_id = __CLASS__ . '-once_published';
		$post_ids = self::get_once_published();
		return in_array($post_id,$post_ids);
	}
	public static function delete_once_published($post_id){
		$cache_id = __CLASS__ . '-once_published';
		$post_ids = self::get_once_published();
		if(!self::is_once_published($post_id))
			return false;
		$key = array_search($post_id,$post_ids);
		unset($post_ids[$key]);
		wp_cache_set($cache_id,$post_ids);
		return true;
	}
	private static function get_update_post_status($old_status){
		if($old_status === 'pending')
			return 'pending';
		/** if is editor, return publish status */
		if(theme_cache::current_user_can('edit_pages'))
			return 'publish';

		/** if is author, check the pending after edit status */
		if(theme_cache::current_user_can('publish_posts'))
			return self::is_pending_after_edited() ? 'pending' : 'publish';
		
		/** if is lower than author, return pending */
		return 'pending';
	}
	public static function get_order_cats(){
		if(!self::get_cats())
			return false;
			
		$cats = [];
		$order_cats = [];
		
		foreach(self::get_cats() as $cat)
			$cats[$cat->term_id] = [
				'term_id' => $cat->term_id,
				'parent' => $cat->parent,
				'name' => $cat->name,
				'description' => $cat->description,
			];
		
		foreach($cats as $cat){
			if (isset($cats[$cat['parent']])){
				$cats[$cat['parent']]['children'][] = &$cats[$cat['term_id']];
			}else{
				$order_cats[] = &$cats[$cat['term_id']];
			}
		}
		
		return $order_cats;
	}
	private static function get_has_parent_cat_ids(){
		$has_parent_cats = [];
		foreach(self::get_cats() as $k => $cat){
			if($cat->parent > 0 && !isset($has_parent_cats[$cat->parent]))
				$has_parent_cats[$cat->parent] = $cat->parent;
		}
		return $has_parent_cats;
	}
	public static function get_cats($array = false){
		static $cache = null;
		if($cache === null)
			$cache = theme_cache::get_categories([
				'include' => self::get_cat_ids(),
				'hide_empty' => false,
			]);
		if($array){
			$cats = [];
			foreach($cache as $cat)
				$cats[$cat->term_id] = [
					'term_id' => $cat->term_id,
					'parent' => $cat->parent,
					'name' => $cat->name,
					'description' => $cat->description,
				];
			return $cats;
		}
		return $cache;
	}
	public static function output_cats(){
		if(!self::get_cat_ids())
			return false;

		self::output_cat(0);

		foreach(self::get_has_parent_cat_ids() as $pid){
			self::output_cat($pid);
		}
	}
	private function output_cat($parent_cat_id){
		$cats = [];
		foreach(self::get_cats() as $cat){
			if($cat->parent == $parent_cat_id){
				$cats[] = '<option value="' . $cat->term_id . '" title="' . $cat->description . '">' . $cat->name . '</option>';
			}
		}
		if(!$cats)
			return false;
			
		?>
		<select 
			id="ctb-cat-<?= $parent_cat_id;?>" 
			name="ctb[cats][]" 
			class="ctb-cat form-control <?= $parent_cat_id == 0 ? null : 'ctb-cat-child';?>" 
			data-parent="<?= $parent_cat_id;?>" >
			<?php 
			if(count($cats) > 1){
				?><option value=""><?= ___('Select a category');?></option><?php
				echo implode('',$cats);
			}else{
				echo $cats[0];
			}
			?>
		</select>
		<?php
	}
	/**
	 * Get thumbnail data
	 *
	 * @param int $attach_id
	 * @param array $output
	 * @return array
	 * @version 1.0.0
	 */
	public static function get_thumbnail_data($attach_id, array $output = []){
		$output['attach-page-url'] = theme_cache::get_permalink($attach_id);
		foreach([ 'thumbnail' ,'medium','large','full' ] as $size){
			$output[$size] = [
				'url' => 
				self::wp_get_attachment_image_src($attach_id,$size)[0],
				'width' => self::wp_get_attachment_image_src($attach_id,$size)[1],
				'height' => self::wp_get_attachment_image_src($attach_id,$size)[2],
			];
		}
		return $output;
	}
	public static function wp_check_post_lock( $post_id ) {
	    if(function_exists('wp_check_post_lock'))
	    	return wp_check_post_lock($post_id);
 
        if ( !$lock = get_post_meta($post_id, '_edit_lock', true ) )
        return false;
 
	    $lock = explode( ':', $lock );
	    $time = $lock[0];
	    $user = isset( $lock[1] ) ? $lock[1] : get_post_meta( $post_id, '_edit_last', true );
	 
	    /** This filter is documented in wp-admin/includes/ajax-actions.php */
	    $time_window = apply_filters( 'wp_check_post_lock_window', 150 );
	 
	    if ( $time && $time > time() - $time_window && $user != theme_cache::get_current_user_id() )
	        return $user;
	    return false;
    }
	public static function is_edit(){
		static $cache = null;
		if($cache !== null)
			return $cache;
			
		$post_id = isset($_GET['post']) && is_numeric($_GET['post']) ? (int)$_GET['post'] : false;
		if(!$post_id){
			$cache = false;
			return false;
		}
			
		$post = theme_cache::get_post($post_id);
		$cache = $post && self::in_edit_post_status($post->post_status) && $post->post_type === 'post' ? $post_id : false;
		return $cache;
	}
	
	public static function get_post_attachs($post_id){
		$post = theme_cache::get_post($post_id);
		if(!$post)
			return false;

		return get_children([
			'post_parent' => $post_id,
			'post_type' => 'attachment',
			'post_mime_type' => 'image',
			'posts_per_page' => -1,
			'orderby' => 'ID',
			'order' => 'ASC',
		]);
	}
	public static function frontend_js_config(array $config){
		if(!self::is_page()) 
			return $config;

		$config[__CLASS__] = [
			'process_url' => theme_features::get_process_url(array('action' => __CLASS__)),
			'default_size' => self::$thumbnail_size,
			'lang' => [
				'M01' => ___('Loading, please wait...'),
				'M02' => ___('Uploading {0}/{1}, please wait...'),
				'M03' => ___('Click to delete'),
				'M04' => ___('{0} files have been uploaded, please insert to post content on demand.'),
				'M05' => ___('Source'),
				'M06' => ___('Click to view source'),
				'M07' => ___('Set as cover.'),
				'M08' => ___('Optional, some description'),
				'M09' => ___('Insert to content'),
				'M10' => ___('Preview'),
				'M11' => ___('Large size'),
				'M12' => ___('Medium size'),
				'M13' => ___('Small size'),
				'E01' => ___('Sorry, server is busy now, can not respond your request, please try again later.'),
			],
			'auto_save' => [
				'lang' => [
					'M01' => ___('You have a auto save version, do you want to restore? Auto save last time is {time}.'),
					'M02' => ___('Restore post data completed, please check it.'),
					'M03' => ___('The post data has saved your browser.'),
				],
			],
		];
		if(self::is_edit()){
			$thumbnail_id = (int)get_post_thumbnail_id(self::is_edit());
			$attachs = [];
			
			$attachs_data = self::get_post_attachs(self::is_edit());
			foreach($attachs_data as $v){
				$attachs[$v->ID] = self::get_thumbnail_data($v->ID);
				$attachs[$v->ID]['attach-id'] = $v->ID;
			}

			unset($attachs_data);
			$config[__CLASS__]['edit'] = 1;
			$config[__CLASS__]['thumbnail_id'] = $thumbnail_id;
			$config[__CLASS__]['attachs'] = $attachs;
		}else{
			$config[__CLASS__]['lang']['M14'] = ___('Please select a category');
			//$config[__CLASS__]['cats'] = self::get_order_cats();
			$config[__CLASS__]['cats'] = self::get_cats();
		}
		return $config;
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_custom_contribution::init';
	return $fns;
});