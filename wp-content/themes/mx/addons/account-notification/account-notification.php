<?php
/** 
 * @version 1.0.0
 */
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_notification::init';
	return $fns;
});
class theme_notification{
	public static $iden = 'theme_notification';
	public static $page_slug = 'account';
	public static $user_meta_key = array(
		'key' => 'theme_noti',
		'count' => 'theme_noti_count',
		'unread_count' => 'theme_noti_unread_count',
	);
	
	public static function init(){
		/** filter */
		
		add_filter('query_vars',			__CLASS__ . '::filter_query_vars');
		
		add_action('wp_enqueue_scripts', 	__CLASS__ . '::frontend_css');

		
		/** action */
		
		add_filter('wp_title',				__CLASS__ . '::wp_title',10,2);
		
		foreach(self::get_tabs() as $k => $v){
			$nav_fn = 'filter_nav_' . $k; 
			add_filter('account_navs',__CLASS__ . "::$nav_fn",$v['filter_priority']);
		}

		/**
		 * add hook to comment publish and reply
		 */
		add_action('comment_post',__CLASS__ . '::action_add_noti_wp_new_comment_comment_publish',10,2);
		
		add_action('transition_comment_status',__CLASS__ . '::action_add_noti_transition_comment_status_comment_publish',10,3);

		/**
		 * add noti for special event
		 */
		add_action('added_user_meta',	__CLASS__ . '::action_add_noti_special_event',10,4);

		/**
		 * list notis
		 */
		$list_notis = [
			'list_noti_special_event',
			'list_noti_post_reply',
			'list_noti_comment_reply',
			'list_noti_follow'
		];
		foreach($list_notis as $v){
			add_action('list_noti' , __CLASS__ . '::' . $v);
		}
		/**
		 * clean unread notis
		 */
		add_action('wp_footer'		,__CLASS__ . '::clean_unread_notis');
	}
	public static function wp_title($title, $sep){
		/**
		 * check unread count
		 */
		if(theme_cache::is_user_logged_in()){
			$unread_count = (int)self::get_count(array(
				'type' => 'unread'
			));
		}else{
			$unread_count = 0;
		}

		if($unread_count === 0){
			if(!self::is_page())
				return $title;
			if(self::get_tabs(get_query_var('tab'))){
				$title = self::get_tabs(get_query_var('tab'))['text'];
			}			
		}else{
			if(!self::is_page()){
				return " ({$unread_count}) " . $title;
			}
			if(self::get_tabs(get_query_var('tab'))){
				$title = " ({$unread_count}) " . self::get_tabs(get_query_var('tab'))['text'];
			}	
		}

		return $title . $sep . theme_cache::get_bloginfo('name');
	}
	public static function filter_query_vars($vars){
		if(!in_array('page',$vars)) $vars[] = 'page';
		return $vars;
	}
	public static function get_tabs($key = null){
		$baseurl = self::get_url();
		$tabs = array(
			'notifications' => array(
				'text' => ___('My notifications'),
				'icon' => 'bell',
				'url' => esc_url(add_query_arg('tab','notifications',$baseurl)),
				'filter_priority' => 40,
			),
		);
		if($key){
			return isset($tabs[$key]) ? $tabs[$key] : false;
		}
		return $tabs;
	}
	public static function filter_nav_notifications($navs){
		$unread = self::get_count(array(
			'type' => 'unread'
		));
		if($unread !== 0){
			$unread_html = "<span class='badge'>{$unread}</span>";
		}else{
			$unread_html = null;
		}

		$navs['notifications'] = '<a href="' . esc_url(self::get_tabs('notifications')['url']) . '">
			<i class="fa fa-' . self::get_tabs('notifications')['icon'] . ' fa-fw"></i> 
			' . self::get_tabs('notifications')['text'] . '
			' . $unread_html . '
		</a>';
		return $navs;
	}
	/**
	 * Clean
	 * When user visit the noti page, clean unread notis
	 */
	public static function clean_unread_notis(){
		if(self::is_page()){
			$old_metas = get_user_meta(theme_cache::get_current_user_id(),self::$user_meta_key['unread_count'],true);
			if(!empty($old_metas))
				update_user_meta(theme_cache::get_current_user_id(),self::$user_meta_key['unread_count'],'');
		}
	}
	public static function get_url(){
		static $cache = null;
		if($cache === null)
			$cache = theme_cache::get_permalink(theme_cache::get_page_by_path(self::$page_slug)->ID);

		return $cache;
	}
	public static function is_page(){
		static $cache = null;
		if($cache === null)
			$cache = theme_cache::is_page(self::$page_slug) && self::get_tabs(get_query_var('tab'));

		return $cache;
	}

	public static function get_count(array $args = []){
		$defaults = array(
			'user_id' => theme_cache::get_current_user_id(),
			'type' => 'all',
		);
		$args = array_merge($defaults,$args);
		if(empty($args['user_id'])) return false;
		
		/**
		 * cache
		 */
		static $caches = [];
		$cache_id = md5(serialize($args));
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];
			
		switch($args['type']){
			case 'unread':
				$metas = array_filter((array)get_user_meta($args['user_id'],self::$user_meta_key['unread_count'],true));
				break;
			default:
				$metas = array_filter((array)get_user_meta($args['user_id'],self::$user_meta_key['key']));
		}
		$caches[$cache_id] = empty($metas) ? 0 : count($metas);
		return $caches[$cache_id];
	}
	public static function get_notifications(array $args = []){
		$defaults = array(
			'user_id' => theme_cache::get_current_user_id(),
			'type' => 'all',/** all / unread / read */
			'posts_per_page' => 20,
			'paged' => 1,
			'orderby' => 'desc',
		);
		$args = array_merge($defaults,$args);
		if(empty($args['user_id'])) return false;
		
		/**
		 * cache
		 */
		static $caches = [];
		$cache_id = md5(serialize($args));
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];

			
		$metas = (array)get_user_meta($args['user_id'],self::$user_meta_key['key']);

		if(empty($metas)){
			return null;
		}else{
			krsort($metas);
		}
		
		switch($args['type']){
			case 'unread':
				$unreads = (array)get_user_meta($args['user_id'],self::$user_meta_key['unread_count'],true);
				if(!empty($unreads)){
					$unread_count = count($unreads);
					$metas = array_slice($metas,0,$unread_count);
				}
			default:
				//return $metas;
		}
		/**
		 * check the paginavi
		 */
		if($args['posts_per_page'] > 0){
				
			$start = (($args['paged'] - 1) * 10) - 1;
			if($start < 0)
				$start = 0;
				
			$metas = array_slice(
				$metas,
				$start,
				$args['posts_per_page']
			);
		}
		$caches[$cache_id] = $metas;
		unset($metas);
		return $caches[$cache_id];
	}
	/**
	 * list special-event
	 */
	public static function list_noti_special_event($noti){
		if($noti['type'] !== 'special-event')
			return false;
		?>
		<div class="media">
			<div class="media-left">
				<i class="fa fa-bullhorn"></i>
			</div>
			<div class="media-body">
				<h4 class="media-heading">
					<span class="label label-default"><i class="fa fa-info-circle"></i> <?= ___('Special event');?></span> 
					<?= ___('Special event');?>
					<?php
					if($noti['point'] > 0){
						$tip_type = 'success';
						$sign = '+';
					}else{
						$tip_type = 'danger';
						$sign = '';
					}
					?>
					<span class="label label-<?= $tip_type;?>">
					<?= theme_custom_point::get_point_name();?>
					<?= $sign,$noti['point'];?></label>
				</h4>
				<div class="excerpt"><p><?= $noti['event'];?></p></div>
			</div>
		</div>
		<?php
	}
	/**
	 * list post-reply
	 */
	public static function list_noti_post_reply($noti){
		if($noti['type'] !== 'post-reply')
			return false;
			
		$comment = theme_notification::get_comment($noti['comment-id']);
		?>
		<div class="media">
			<div class="media-left">
				<a href="<?php comment_author_url($noti['comment-id']);?>">
				<img src="<?= theme_cache::get_avatar_url($comment->user_id);?>" class="avatar media-object" alt="avatar" width="60" height="60">
				</a>
			</div>
			<div class="media-body">
				<h4 class="media-heading">
					<span class="label label-default"><i class="fa fa-comments"></i> <?= ___('Post reply');?></span> 
					<?php
					echo sprintf(
						___('Your post %1$s has a comment by %2$s.'),
						'<a href="' . theme_cache::get_permalink($comment->comment_post_ID) . '#comment-' . $comment->comment_ID . '">' . theme_cache::get_the_title($comment->comment_post_ID) . '</a>',
						get_comment_author_link($noti['comment-id'])
					);
					?>
				</h4>
				<div class="excerpt"><?php comment_text($noti['comment-id']);?></div>
			</div><!-- /.media-body -->
		</div><!-- /.media -->
		<?php
	}
	/**
	 * list comment-reply
	 */
	public static function list_noti_comment_reply($noti){
		if($noti['type'] !== 'comment-reply')
			return false;
			
		$comment = theme_notification::get_comment($noti['comment-id']);
		?>
		<div class="media">
			<div class="media-left">
				<a href="<?= comment_author_url($noti['comment-id']);?>">
					<img src="<?= theme_functions::$avatar_placeholder;?>" data-src="<?= theme_cache::get_avatar_url($comment->user_id);?>" class="avatar media-object" alt="avatar" width="60" height="60">
				</a>
			</div>
			<div class="media-body">
				<h4 class="media-heading">
					<span class="label label-default"><i class="fa fa-comments-o"></i> <?= ___('Comment reply');?></span> 
					<?php
					echo sprintf(
						___('Your comment has a reply by %1$s in %2$s.'),
						get_comment_author_link($noti['comment-id']),
						'<a href="' . theme_cache::get_permalink($comment->comment_post_ID) . '#comment-' . $noti['comment-id'] . '">
							' . theme_cache::get_the_title($comment->comment_post_ID) . '
						</a>'
					);
					?>
				</h4>
				<div class="excerpt"><?php comment_text($noti['comment-id']);?></div>
			</div><!-- /.media-body -->
		</div><!-- /.media -->
		<?php
	}
	/**
	 * list follow
	 */
	public static function list_noti_follow($noti){
		if($noti['type'] !== 'follow')
			return false;
			
		$follower_id = $v['follower-id'];
		?>
		<div class="media">
			<div class="media-left">
				<a href="<?php comment_author_url();?>">
					<img src="<?= theme_cache::get_avatar_url($follower_id);?>" class="avatar media-object" alt="avatar" width="60" height="60">
				</a>
			</div>
			<div class="media-body">
				<h4 class="media-heading">
				<?php
				echo sprintf(
					___('%s is following you.'),
					esc_url(get_comment_author_link())
				);
				?>
				</h4>
			</div><!-- /.media-body -->
		</div><!-- /.media -->

		<?php
	}
	/**
	 * Get timestamp
	 *
	 * @param bool $rand True/get event id
	 * @return string
	 * @version 1.0.0
	 */
	private static function get_timestamp($rand = false){
		static $cache = null;
		
		if($rand)
			return current_time('timestamp') . '' . rand(100,999);
		
		if($cache)
			return $cache;
		$cache = current_time('timestamp');
		return $cache;
	}
	/**
	 * Add special event
	 *
	 * @param int $user_id
	 * @param int $point
	 * @param string $event Event description
	 * @version 1.0.0
	 */
	public static function action_add_noti_special_event($mid, $object_id, $meta_key, $_meta_value){
		
		if($meta_key !== theme_custom_point::$user_meta_key['history'])
			return false;

		if(!isset($_meta_value['type']) || $_meta_value['type'] !== 'special-event')
			return false;
			
		/**
		 * add history
		 */
		$_meta_value['id']  = self::get_timestamp(true);
		self::add_noti($object_id,$_meta_value);

		return true;

	}
	/**
	 * HOOK - When a comment becomes spam/disapprove to approve status, noti to post author
	 *
	 * @version 1.0.0
	 */
	public static function action_add_noti_transition_comment_status_comment_publish($new_status, $old_status, $comment){
		/**
		 * do NOT add noti if the comment is spam or hold
		 */
		if($old_status !== 'unapproved' && $old_status !== 'spam')
			return;
		
		if($new_status !== 'approved')
			return;
			
		/**
		 * 评论是子评论
		 */
		if($comment->comment_parent != 0){
			/** post author */
			$post_author_id = theme_cache::get_post($comment->comment_post_ID)->post_author;
			/**
			 * 评论是回复评论，父评论是文章作者时，仅给父评论作者添加评论回复事件，不添加文章评论事件
			 */
			if(get_comment($comment->comment_parent)->user_id == $post_author_id){
				self::action_add_noti_core_comment_reply($comment->comment_ID);
				return;
			}
			
		}
		/**
		 * noti for post author
		 */
		self::action_add_noti_core_post_reply($comment->comment_ID);
		/**
		 * noti for comment parent author
		 */
		self::action_add_noti_core_comment_reply($comment->comment_ID);
	}
	/**
	 * HOOK - When a comment publish noti to post author
	 *
	 * @param int $comment_id Comment ID
	 * @param string $comment_approved 0|1|spam
	 * @version 1.0.0
	 */
	public static function action_add_noti_wp_new_comment_comment_publish($comment_id,$comment_approved){
		/**
		 * do NOT add noti if the comment is spam or disapprove
		 */
		if((int)$comment_approved !== 1)
			return;
		
		$comment = get_comment($comment_id);
		
		/** post author */
		$post_author_id = theme_cache::get_post($comment->comment_post_ID)->post_author;
			
		/**
		 * 评论是子评论
		 */
		if($comment->comment_parent != 0){
			/**
			 * 评论是回复评论，父评论是文章作者时，仅给父评论作者添加评论回复事件，不添加文章评论事件
			 */
			if(get_comment($comment->comment_parent)->user_id == $post_author_id){
				self::action_add_noti_core_comment_reply($comment_id);
				return;
			}
			
		}
		/**
		 * noti for post author
		 */
		self::action_add_noti_core_post_reply($comment_id);
		
		/**
		 * noti for comment parent author
		 */
		self::action_add_noti_core_comment_reply($comment_id);
	}
	
	public static function action_add_noti_core_post_reply($comment_id){
		$comment = self::get_comment($comment_id);

		$post_author_id = theme_cache::get_post($comment->comment_post_ID)->post_author;

		/**
		 * if visitor is comment author, reuturn
		 */
		if($comment->user_id == 0)
			return false;
			
		/**
		 * if post author is current comment author, return
		 */
		if($post_author_id == $comment->user_id)
			return false;
			
		/**
		 * add noti for post author
		 */
		$meta = array(
			'id' 			=> self::get_timestamp(true),
			'type' 			=> 'post-reply',
			'comment-id' 	=> $comment->comment_ID,
			'timestamp' 	=> self::get_timestamp(),
		);
		self::add_noti($post_author_id,$meta);
	}
	/**
	 * add notifications for user
	 *
	 * @param int $user_id User ID
	 * @param array $meta Notification meta data
	 * @version 1.0.0
	 */
	public static function add_noti($user_id,$meta){
		add_user_meta($user_id,self::$user_meta_key['key'],$meta);
		/**
		 * update unread count
		 */
		$unread_count = (array)get_user_meta($user_id,self::$user_meta_key['unread_count'],true);
		$unread_count[$meta['timestamp']] = $meta;
		update_user_meta($user_id,self::$user_meta_key['unread_count'],$unread_count);
	}
	public static function get_comment($comment_id){
		static $caches = [];
		if(isset($caches[$comment_id]))
			return $caches[$comment_id];

		$caches[$comment_id] = get_comment($comment_id);
		return $caches[$comment_id];
	}
	/**
	 * comment reply noti
	 */
	public static function action_add_noti_core_comment_reply($comment_id){
		
		$comment = self::get_comment($comment_id);
		
		if($comment->comment_parent == 0)
			return false;
		
		/** get post author */
		$post_author_id = theme_cache::get_post($comment->comment_post_ID)->post_author;
		
		/**
		 * if post author is current comment author, return
		 */
		//if($post_author_id == $comment->user_id)
		//	return false;

		/**
		 * get parent comment author
		 */
		$parent_comment_author_id = self::get_comment($comment->comment_parent)->user_id;

		/**
		 * if parent comment author is visitor, return
		 */
		if($parent_comment_author_id == 0)
			return false;

		/**
		 * if parent comment author is current comment author, return
		 */
		if($parent_comment_author_id == $comment->user_id)
			return false;

		
		/**
		 * add noti for parent comment author
		 */
		$meta = array(
			'id' => self::get_timestamp(true),
			'type' => 'comment-reply',
			'comment-id' => $comment->comment_ID,
			'timestamp' => self::get_timestamp(),
		);
		self::add_noti($parent_comment_author_id,$meta);
	}
	public static function process(){
		$output = [];
		
		theme_features::check_referer();
		theme_features::check_nonce();
		
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
		

		die(theme_features::json_format($output));
	}
	public static function the_time($meta){
		if(!isset($meta['timestamp']))
			return false;
		?>
		<span class="noti-time event-time">
			<?= friendly_date($meta['timestamp']); ?>
		</span>
		<?php
	}
	public static function frontend_css(){
		if(!self::is_page()) 
			return false;
			
		wp_enqueue_style(
			self::$iden,
			theme_features::get_theme_addons_css(__DIR__),
			'frontend',
			theme_file_timestamp::get_timestamp()
		);
	}
}