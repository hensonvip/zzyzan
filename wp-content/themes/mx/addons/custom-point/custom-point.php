<?php
/**
 * @version 1.0.2
 */

class theme_custom_point{
	public static $page_slug = 'account';
	
	public static $user_meta_key = array(
		'history' => 'theme_point_history',
		'point' => 'theme_point_count',
		'last-signin' => 'theme_last_signin',
	);
	public static function init(){

		include __DIR__ . '/widget.php';
		
		add_action('page_settings',__CLASS__ . '::display_backend');

		add_action('comment_post',__CLASS__ . '::action_add_history_wp_new_comment_comment_publish',10,2);
		
		add_action('transition_comment_status', __CLASS__ . '::action_add_history_transition_comment_status_comment_publish',10,3);

		
		add_action('transition_post_status', __CLASS__ . '::add_action_publish_post_history_post_publish',10,3);
		
		
		add_action('user_register', __CLASS__ . '::action_add_history_signup');

		/** post-delete */
		add_action('before_delete_post', __CLASS__ . '::action_add_history_post_delete');
		
		/** sign-in daily */
		add_filter('dynamic_request_process', __CLASS__ . '::filter_dynamic_request_process');
		
		add_filter('theme_options_default', __CLASS__ . '::options_default');
		add_filter('theme_options_save', __CLASS__ . '::options_save');

		/** ajax */
		add_action('wp_ajax_' . __CLASS__, __CLASS__ . '::process');

		add_filter('theme_api_theme_custom_sign_after_login_user_data', __CLASS__ . '::filter_theme_api_theme_custom_sign_after_login_user_data',10,2);

		add_action('backend_js_config', __CLASS__ . '::backend_js_config');

		/**
		 * list history hooks
		 */
		foreach([
			'list_history_sepcial_event',
			'list_history_comment_publish',
			'list_history_post_delete',
			'list_history_post_publish',
			'list_history_post_reply',
			'list_history_signup',
			'list_history_signin_daily'
		] as $v)
			add_action('list_point_histroy',__CLASS__ . '::' . $v);
	}

	public static function display_backend(){
		$points = self::get_options('points');
		?>
		<fieldset>
			<legend><i class="fa fa-fw fa-paw"></i> <?= ___('User point settings');?></legend>
			<p class="description"><?= ___('About user point settings.');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th><label for="<?= __CLASS__;?>-point-name"><?= ___('Point name');?></label></th>
						<td>
							<input type="text" name="<?= __CLASS__;?>[point-name]" class="widefat" id="<?= __CLASS__;?>-point-name" value="<?= esc_attr(self::get_point_name());?>">
						</td>
					</tr>
					<?php foreach(self::get_point_types() as $k => $type){ ?>
<tr>
	<th>
		<label for="<?= __CLASS__;?>-<?= $k;?>"><?= $type['text'];?></label>
	</th>
	<td>
		<input 
			type="<?= isset($type['type']) ? $type['type'] : 'text';?>" 
			name="<?= __CLASS__;?>[points][<?= $k;?>]" class="short-text" 
			id="<?= __CLASS__;?>-<?= $k;?>" 
			value="<?= isset($points[$k]) ? $points[$k] : self::get_point_value_default($k);?>"
		>
		<?php if(isset($type['des'])){ ?>
			<span class="description"><?= $type['des'];?></span>
		<?php } ?>
	</td>
</tr>
					<?php } ?>
					<tr>
						<th><label for="<?= __CLASS__;?>-point-des"><?= ___('Description on point history page');?></label></th>
						<td>
							<textarea name="<?= __CLASS__;?>[point-des]" id="<?= __CLASS__;?>-des" rows="3" class="widefat code"><?= self::get_point_des();?></textarea>
						</td>
					</tr>
					<tr>
						<th><label for="<?= __CLASS__;?>-point-img-url">
							<?= ___('Point image url');?>
							<?php if(self::get_point_img_url() !== ''){ ?>
								<br>
								<img src="<?= self::get_point_img_url();?>" alt="" width="50" height="50">
							<?php } ?>
						</label></th>
						<td>
							<input type="url" name="<?= __CLASS__;?>[point-img-url]" id="<?= __CLASS__;?>-img-url" class="widefat code" value="<?= self::get_point_img_url();?>">
						</td>
					</tr>
				</tbody>
			</table>

			<h3><?= ___('Add/Reduce point for user - special event');?></h3>
			<table class="form-table">
				<tbody>
					<tr>
						<th><?= ___('User ID');?></th>
						<td>
							<input class="short-text" type="number" id="<?= __CLASS__;?>-special-user-id" data-target="<?= __CLASS__;?>-special-tip-user-id" data-ajax-type="get-points" data-ajax-field="user-id">
							<span id="<?= __CLASS__;?>-special-tip-user-id"></span>
						</td>
					</tr>
					<tr>
						<th><?= ___('How many point to add/reduce');?></th>
						<td>
							<input class="short-text" type="number" id="<?= __CLASS__;?>-special-point" data-target="<?= __CLASS__;?>-special-tip-user-point" data-ajax-field="point">
							<span id="<?= __CLASS__;?>-special-tip-user-point"></span>
						</td>
					</tr>
					<tr>
						<th><?= ___('Event description');?></th>
						<td>
							<input class="widefat" type="text" id="<?= __CLASS__;?>-special-event" data-ajax-field="event">
						</td>
					</tr>
					<tr>
						<th><?= ___('Control');?></th>
						<td>
							<a href="javascript:;" class="button button-primary" id="<?= __CLASS__;?>-special-set" data-target="<?= __CLASS__;?>-special-tip-set">
								<i class="fa fa-pencil-square-o"></i> 
								<?= ___('Add/Reduce');?>
							</a>
						</td>
					</tr>
				</tbody>
			</table>
			<?php do_action(__CLASS__ . '_backend');?>
			<h3><?= ___('Restore point options');?></h3>
			<p class="description"><?= ___('You can restore the point options when you want.');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th><?= ___('Restore');?></th>
						<td>
							<label for="<?= __CLASS__;?>-restore">
								<input type="checkbox" name="<?= __CLASS__;?>[restore]" id="<?= __CLASS__;?>-restore" value="1"> 
								<?= ___('Restore');?>
							</label> 
							<span class="description"><?= ___('Check the box and save all settings to restore point options.');?></span>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function the_list_icon($type){
		?><span class="point-icon"><i class="fa fa-2x fa-fw fa-<?= $type;?>"></i></span><?php
	}
	public static function get_point_img_url(){
		static $cache = null;
		if($cache === null)
			$cache = esc_url(self::get_options('point-img-url'));

		return $cache;
	}
	public static function incr_user_points($user_id,$points){
		$old_points = (int)get_user_meta($user_id,self::$user_meta_key['point'],true);
		$old_points += $points;
		update_user_meta($user_id,self::$user_meta_key['point'],$old_points);
	}
	public static function decr_user_points($user_id,$points){
		$old_points = (int)get_user_meta($user_id,self::$user_meta_key['point'],true);
		$old_points -= $points;
		update_user_meta($user_id,self::$user_meta_key['point'],$old_points);
	}
	public static function process(){
		$output = [];

		
		$type = isset($_GET['type']) ? $_GET['type'] : null;

		switch($type){
			case 'get-points':
				if(!isset($_GET['user-id']) || !is_numeric($_GET['user-id'])){
					$output['status'] = 'error';
					$output['code'] = 'invaild_user_id';
					$output['msg'] = ___('Invaild user id.');
					die(theme_features::json_format($output));
				}
				$user = get_user_by('id',$_GET['user-id']);
				if(!$user){
					$output['status'] = 'error';
					$output['code'] = 'user_not_exist';
					$output['msg'] = ___('User does not exist.');
					die(theme_features::json_format($output));
				}
				$output['status'] = 'success';
				$output['points'] = self::get_point($user->ID);
				$output['msg'] = sprintf(
					___('The user %1$s has %2$d points now.'),
					esc_html($user->display_name),
					self::get_point($user->ID)
				);
				break;
			/**
			 * special
			 */
			case 'special':
				if(!theme_cache::current_user_can('create_users')){
					$output['status'] = 'error';
					$output['code'] = 'invaild_permission';
					$output['msg'] = ___('Your are not enough permission to modify user.');
					die(theme_features::json_format($output));
				}
				$special = isset($_GET['special']) && is_array($_GET['special']) ? $_GET['special'] : null;
				if(empty($special)){
					$output['status'] = 'error';
					$output['code'] = 'invaild_param';
					$output['msg'] = ___('Invaild param.');
					die(theme_features::json_format($output));
				}
				$invalidations = array(
					'user-id' => array(
						'msg' => ___('Invaild user ID.'),
						'code' => 'invaild_user_id'
					),
					'point' => array(
						'msg' => ___('Invaild point.'),
						'code' => 'invaild_point'
					),
					'event' => array(
						'msg' => ___('Invaild event.'),
						'code' => 'invaild_event'
					),
				);
				foreach($invalidations as $k => $v){
					if(!isset($special[$k]) || empty($special[$k])){
						$output['status'] = 'error';
						$output['code'] = $v['code'];
						$output['msg'] = $v['msg'];
						die(theme_features::json_format($output));
					}
				}
				/**
				 * check user exist
				 */
				$user = get_user_by('id',$special['user-id']);
				if(!$user){
					$output['status'] = 'error';
					$output['code'] = 'user_not_exist';
					$output['msg'] = ___('The user is not exist');
					die(theme_features::json_format($output));
				}
				/**
				 * pass, set the new point for user
				 */
				self::action_add_history_special_event($special['user-id'],$special['point'],$special['event']);
				$output['status'] = 'success';
				
				$sign = $special['point'] > 0 ? '+' : null;
				$output['msg'] = sprintf(
					___('The user %1$s(%2$d) point has set to %3$d.'),
					esc_html($user->display_name),
					$user->ID,
					self::get_point($user->ID) . $sign . $special['point'] . '=' . self::get_point($user->ID,true)
				);
				die(theme_features::json_format($output));
				break;

		}

		die(theme_features::json_format($output));
	}
	public static function is_page(){
		static $cache = null;
		if($cache === null)
			$cache = theme_cache::is_page(self::$page_slug) && get_query_var('tab') === 'history';
		return $cache;
	}
	public static function get_point_types($key = null){
		static $caches = null;
		if($caches === null){
			$caches = [
				'signup' => [
					'text' => ___('When sign-up'),
					'type' => 'number',
				],
				'signin-daily' => [
					'text' => ___('When sign-in daily'),
					'type' => 'number',
				],
				'comment-publish' => [
					'text' => ___('When publish comment'),
					'type' => 'number',
				],
				'comment-delete' => [
					'text' => ___('When delete comment'),
					'type' => 'number',
				],
				'post-publish' => [
					'text' => ___('When publish post'),
					'type' => 'number',
				],
				'post-reply' => [
					'text' => ___('When reply post'),
					'type' => 'number',
				],
				'post-delete' => [
					'text' => ___('When delete post'),
					'type' => 'number',
				],
				'aff-signup' => [
					'text' => ___('When aff sign-up'),
					'type' => 'number',
				],
			];
			$caches = apply_filters('custom_point_types',$caches);
		}
		if($key) 
			return isset($caches[$key]) ? $caches[$key] : false;
		return $caches;
	}
	public static function options_default(array $opts = []){
		$opts[__CLASS__] = [
			'points' 		=> self::get_point_value_default(),
			'point-name' 	=> '积分', /** 名称 */
			'point-des' 	=> ___('Point can exchange many things.'),
			'point-img-url' => 'http://ww1.sinaimg.cn/large/686ee05djw1epfzp00krfg201101e0qn.gif',
		];
		
		$opts[__CLASS__] = apply_filters('custom_point_options_default',$opts[__CLASS__]);
		return $opts;
	}
	/**
	 * get default point value
	 *
	 * @param string $key 
	 * @return array|int
	 * @version 1.0.0
	 */
	public static function get_point_value_default($key = null){
		$values = [
			'signup'				=> 20, /** 初始 */
			'signin-daily'			=> 2, /** 日登 */
			'comment-publish'		=> 1, /** 发表新评论 */
			'comment-delete'  		=> -3, /** 删除评论 */
			'post-publish' 			=> 3, /** 发表新文章 */
			'post-reply' 			=> 1, /** 文章被回复 */
			'post-delete'			=> -5,/** 文章被删除 */
			'aff-signup'			=> 5, /** 推广注册 */
		];
		$values = apply_filters('custom_point_value_default',$values);
		if($key)
			return isset($values[$key]) ? $values[$key] : false;
		return $values;
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__])){
			if(!isset($_POST[__CLASS__]['restore'])){
				$opts[__CLASS__] = $_POST[__CLASS__];
			}
		}
		return $opts;
	}
	public static function filter_theme_api_theme_custom_sign_after_login_user_data(array $user = [],$user_id){
		$user['points'] = self::get_point($user_id);
		return $user;
	}
	/**
	 * get point name
	 */
	public static function get_point_name(){
		return self::get_options('point-name') ? self::get_options('point-name') : '积分';
	}
	/**
	 * get point description
	 */
	public static function get_point_des(){
		return stripslashes(self::get_options('point-des'));
	}
	public static function get_options($key = null){
		static $caches;
		if(!is_array($caches))			
			$caches = (array)theme_options::get_options(__CLASS__);
			
		if($key){
			return isset($caches[$key]) ? $caches[$key] : null;
		}
		return $caches;
	}
	/**
	 * get point value number
	 */
	public static function get_point_value($type){
		static $caches;
		if(!$caches)
			$caches = self::get_options('points');

		return isset($caches[$type]) ? $caches[$type] : self::get_point_value_default($type);
	}
	/**
	 * Get user point
	 *
	 * @param int User id
	 * @param bool $force Force to get point value without cache.
	 * @version 1.0.2
	 * @return int
	 */
	public static function get_point($user_id,$force = false){
		static $caches = [];
		if(isset($caches[$user_id]) && !$force)
			return $caches[$user_id];

		$caches[$user_id] = (int)get_user_meta($user_id,self::$user_meta_key['point'],true);

		return $caches[$user_id];
	}
	/**
	 * Get user history
	 *
	 * @param array $args
	 * @param int $user_id
	 * @param int $paged
	 * @param int $posts_per_page
	 * @version 1.0.0
	 * @return array
	 */
	public static function get_history($args = []){
		$defaults = array(
			'user_id' => theme_cache::get_current_user_id(),
			'paged' => 1,
			'posts_per_page' => 20,
		);
		$r = array_merge($defaults,$args);
		extract($r);

		
		$metas = get_user_meta($user_id,self::$user_meta_key['history']);
		krsort($metas);
		/**
		 * check the paginavi
		 */
		if($posts_per_page > 0){
				
			$start = (($paged - 1) * 10) - 1;
			if($start < 0)
				$start = 0;
				
			$metas = array_slice(
				$metas,
				$start,
				(int)$posts_per_page
			);
		}
		return $metas;
	}
	public static function the_time($history){
		if(!isset($history['timestamp']))
			return false;
		?>
		<span class="history-time">
			<?= friendly_date($history['timestamp']); ?>
		</span>
		<?php
	}
	public static function list_history_sepcial_event($history){
		if($history['type'] !== 'special-event')
			return false;
		?>
		<li class="list-group-item list-group-item-warning">
			<?php theme_custom_point::the_list_icon('info-circle');?>
			<?php self::the_point_sign($history['point']);?>
			
			<span class="history-text">
				<?= sprintf(___('One special event happened: %s'),$history['event']);?>
			</span>
			
			<?php self::the_time($history);?>
		</li>
		<?php
	}
	public static function list_history_post_delete($history){
		if($history['type'] !== 'post-delete')
			return false;
		?>
		<li class="list-group-item">
			<?php theme_custom_point::the_list_icon('trash');?>
			<?php self::the_point_sign(self::get_point_value('post-delete'));?>
			
			<span class="history-text">
				<?= sprintf(___('Your post "%s" has been deleted.'),$history['post-title']);?>
			</span>
			
			<?php self::the_time($history);?>
		</li>
		</li>
		<?php
	}
	public static function list_history_signup($history){
		if($history['type'] !== 'signup')
			return false;
		?>
		<li class="list-group-item">
			<?php theme_custom_point::the_list_icon('user-plus');?>
			<?php self::the_point_sign(self::get_point_value('signup'));?>
			
			<span class="history-text">
				<?= sprintf(___('You registered %s.'),'<a href="' . theme_cache::home_url() . '">' . theme_cache::get_bloginfo('name') . '</a>');?>
			</span>
			
			<?php self::the_time($history);?>
		</li>
		<?php
	}
	public static function list_history_comment_publish($history){
		if($history['type'] !== 'comment-publish')
			return false;
		?>
		<li class="list-group-item">
			<?php theme_custom_point::the_list_icon('comment-o');?>
			<?php self::the_point_sign(self::get_point_value('comment-publish'));?>
			
			<span class="history-text">
				<?php 
				$comment = get_comment($history['comment-id']);
				if(!$comment){
					echo ___('The comment has been deleted.');
				}else{
					echo sprintf(___('You published a comment in %1$s.'),

					'<a href="' . theme_cache::get_permalink($comment->comment_post_ID) . '">' . theme_cache::get_the_title($comment->comment_post_ID) . '</a>'
					);
				}
				?>
			</span>
			
			<?php self::the_time($history);?>
		</li>
		<?php
	}
	public static function list_history_post_publish($history){
		if($history['type'] !== 'post-publish')
			return false;
		?>
		<li class="list-group-item">
			<?php theme_custom_point::the_list_icon('paint-brush');?>
			<?php self::the_point_sign(self::get_point_value('post-publish'));?>
			
			<span class="history-text">
				<?php
				$post = theme_cache::get_post($history['post-id']);
				if(!$post){
					echo ___('The post has been deleted.');
				}else{
					echo sprintf(___('You published a post %s.'),'<a href="' . theme_cache::get_permalink($history['post-id']) . '">' . theme_cache::get_the_title($history['post-id']) . '</a>');
				}
				?>
			</span>
			
			<?php self::the_time($history);?>
		</li>
		<?php
	}
	public static function list_history_post_reply($history){
		if($history['type'] !== 'post-reply')
			return false;
		?>
		<li class="list-group-item">
			<?php theme_custom_point::the_list_icon('comments-o');?>
			<?php self::the_point_sign(self::get_point_value('post-reply'));?>
			
			<span class="history-text">
				<?php 
				$comment = get_comment($history['comment-id']);
				if(!$comment){
					echo ___('The comment has been deleted.');
				}else{
					$post = theme_cache::get_post($comment->comment_post_ID);
					if(!$post){
						echo ___('The post has been deleted.');
					}else{
						echo sprintf(___('Your post %1$s has a new comment by %2$s.'),

						'<a href="' . theme_cache::get_permalink($post->ID) . '">' . theme_cache::get_the_title($post->ID) . '</a>',

						'<span class="comment-author">' . get_comment_author_link($history['comment-id']) . '</span>'
						);
					}
				}
				?>
			</span>
			
			<?php self::the_time($history);?>
		</li>
		<?php
	}
	public static function list_history_signin_daily($history){
		if($history['type'] !== 'signin-daily')
			return false;
		?>
		<li class="list-group-item">
			<?php theme_custom_point::the_list_icon('user');?>
			<?php self::the_point_sign(self::get_point_value('signin-daily'));?>
			
			<span class="history-text">
				<?= ___('Log-in daily reward.');?>
			</span>
			
			<?php self::the_time($history);?>
		</li>
		<?php
	}
	public static function the_point_sign($points){
		if(!is_numeric($points))
			return false;

		$class = null;
		if($points > 0){
			$points = '+ ' . $points;
			$class = 'plus';
		}else if($points < 0){
			$points = '- ' . abs($points);
			$class = 'minus';
		}
		?>
		<span class="point-value <?= $class;?>"><?= $points;?></span>
		<?php
	}
	public static function get_history_list(array $args = []){
		$histories = self::get_history($args);

		if(empty($histories))
			return false;
		ob_start();
		?>
		<ul class="list-group history-group">
			<?php foreach($histories as $history){ ?>
				<?php do_action('list_point_histroy',$history);?>
			<?php } ?>
		</ul>

		<?php
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}
	public static function filter_dynamic_request_process($output){
		/**
		 * signin daily
		 */
		if(!theme_cache::is_user_logged_in()) return $output;
		if(self::action_add_history_signin_daily() === true){
			$point = (int)theme_options::get_options(__CLASS__)['points']['signin-daily'];
			$output['signin-daily'] = array(
				'point' => $point,
				'msg' => sprintf(___('Sign-in daily points: +%s'),$point),
			);
		}else{
			$output['signin-daily'] = false;
		}
		return $output;
	}
	/**
	 * update user points
	 *
	 * @param int $user_id User ID
	 * @param int $points New points
	 */
	public static function update_user_points($user_id,$points){
		update_user_meta($user_id,self::$user_meta_key['point'],$points);
	}
	/**
	 * add history for user
	 *
	 * @param int $user_id User ID
	 * @param array $meta History meta data
	 * @version 1.0.0
	 */
	public static function add_history($user_id,$meta){
		add_user_meta($user_id,self::$user_meta_key['history'],$meta);
	}
	/**
	 * HOOK - Add post-delete history to user meta
	 *
	 * @version 1.0.0
	 */
	public static function action_add_history_post_delete($post_id){
		$post = theme_cache::get_post($post_id);
		if(!$post)
			return false;

		/** unset published */
		if(class_exists('theme_custom_contribution'))
			theme_custom_contribution::delete_once_published($post_id);
		
		if($post->post_type !== 'post')
			return false;
			
		$meta = array(
			'type'=> 'post-delete',
			'post-title' => theme_cache::get_the_title($post->ID),
			'timestamp' => current_time('timestamp'),
		);
		/**
		 * add to history
		 */
		self::add_history($post->post_author,$meta);

		/**
		 * point
		 */
		$old_point = self::get_point($post->post_author);
		update_user_meta($post->post_author,self::$user_meta_key['point'],$old_point - self::get_point_value('post-delete'));
	}
	/**
	 * HOOK - Add sign-up history to user meta
	 *
	 * @version 1.0.0
	 */
	public static function action_add_history_signup($user_id){
		$meta = array(
			'type'=> 'signup',
			'timestamp' => current_time('timestamp'),
		);
		/**
		 * add to history
		 */
		self::add_history($user_id,$meta);
		/**
		 * update point
		 */
		update_user_meta($user_id,self::$user_meta_key['point'],(int)self::get_options('points')['signup']);
	}
	/**
	 * Add special event
	 *
	 * @param int $user_id
	 * @param int $point
	 * @param string $event Event description
	 * @version 1.0.0
	 */
	public static function action_add_history_special_event($user_id,$point,$event = null){
		$point = (int)$point;
		$user_id = (int)$user_id;
		if($point === 0 || $user_id === 0)
			return false;
			
		if(!is_numeric($point))
			return false;
			
		$current_timestamp = current_time('timestamp');
		if(empty($event))
			$event = ___('Special event');

		/**
		 * add history
		 */
		$meta = array(
			'type' => 'special-event',
			'point' => $point,
			'event' => $event,
			'timestamp' => $current_timestamp
		);
		/**
		 * add to history
		 */
		self::add_history($user_id,$meta);
		/**
		 * update point
		 */
		$old_point = self::get_point($user_id,true);
		update_user_meta($user_id,self::$user_meta_key['point'],$old_point + $point);

		return true;

	}
	/**
	 * HOOK - Signin daily for user meta
	 *
	 * @version 1.0.0
	 */
	public static function action_add_history_signin_daily(){
		$user_id = theme_cache::get_current_user_id();
		$current_timestamp = current_time('timestamp');
		/**
		 * get the last sign-in time
		 */
		$last_signin_timestamp = get_user_meta($user_id,self::$user_meta_key['last-signin'],true);

		/**
		 * first sign-in
		 */
		if(empty($last_signin_timestamp)){
			update_user_meta($user_id,self::$user_meta_key['last-signin'],$current_timestamp);
			return;
		}

		
		$today_Ymd = date('Ymd',$current_timestamp);
		$last_signin_Ymd = date('Ymd',$last_signin_timestamp);
		
		/** IS logged today, return */
		if($today_Ymd == $last_signin_Ymd) 
			return false;
			
		/**
		 * update $last_signin_timestamp
		 */
		update_user_meta($user_id,self::$user_meta_key['last-signin'],$current_timestamp);

		/**
		 * add history
		 */
		$meta = array(
			'type' => 'signin-daily',
			'timestamp' => $current_timestamp
		);
		/**
		 * add to history
		 */
		self::add_history($user_id,$meta);
		/**
		 * update point
		 */
		$old_point = self::get_point($user_id);
		update_user_meta($user_id,self::$user_meta_key['point'],$old_point + (int)theme_options::get_options(__CLASS__)['points']['signin-daily']);

		return true;
	}
	/**
	 * Hook, when comment author's comment status has been updated
	 */
	public static function action_add_history_transition_comment_status_comment_publish($new_status, $old_status, $comment){
		
		/**
		 * do NOT add history if visitor
		 */
		if($comment->user_id == 0)
			return;
		
		/**
		 * do NOT add history if the comment is spam or hold
		 */
		if($old_status !== 'unapproved' && $old_status !== 'spam')
			return;
		
		if($new_status !== 'approved')
			return;
		/**
		 * add history for comment author
		 */
		self::action_add_history_core_comment_publish($comment->comment_ID);

		/**
		 * add history for post author
		 */
		self::action_add_history_core_post_reply($comment->comment_ID);
	}
	/**
	 * HOOK - Add comment publish history to user meta
	 *
	 * @param int $comment_id Comment ID
	 * @param string $comment_approved 0|1|spam
	 * @version 1.0.0
	 */
	public static function action_add_history_wp_new_comment_comment_publish($comment_id,$comment_approved){
		/**
		 * do NOT add history if the comment is spam or disapprove
		 */
		if((int)$comment_approved !== 1)
			return;
			
		/**
		 * do NOT add history if visitor
		 */
		$comment = get_comment($comment_id);
		if($comment->user_id == 0)
			return;
		
		/**
		 * add history for comment author
		 */
		self::action_add_history_core_comment_publish($comment_id);

		/**
		 * add history for post author
		 */
		self::action_add_history_core_post_reply($comment_id);
	}
	
	/**
	 * Add history when publish comment for comment author
	 *
	 * @param 
	 * @return 
	 * @version 1.0.0
	 */
	public static function action_add_history_core_comment_publish($comment_id){

		$comment = get_comment($comment_id);
		/**
		 * return if visitor comment
		 */
		if($comment->user_id == 0)
			return false;
			
		$comment_author_id = $comment->user_id;

		$post = theme_cache::get_post($comment->comment_post_ID);

		if($comment_author_id == $post->post_author) return false;
		$meta = array(
			'type' => 'comment-publish',
			'comment-id' => $comment_id,
			'timestamp' => current_time('timestamp'),
		);
		/**
		 * add to history
		 */
		self::add_history($comment_author_id,$meta);
		/**
		 * update point
		 */
		if($post->post_type !== 'post')
			return false;
			
		$old_point = self::get_point($comment_author_id);
		update_user_meta($comment_author_id,self::$user_meta_key['point'],$old_point + (int)theme_options::get_options(__CLASS__)['points']['comment-publish']);		
	}
	/**
	 * action_add_history_core_post_reply
	 *
	 * @param int $comment_id Comment ID
	 * @version 1.0.0
	 */
	public static function action_add_history_core_post_reply($comment_id){
		
		$comment = get_comment($comment_id);
		/**
		 * return if visitor comment
		 */
		if($comment->user_id == 0)
			return false;
			
		
		$post = theme_cache::get_post($comment->comment_post_ID);
		
		/** post author id */
		$post_author_id = $post->post_author;
		
		
		/** do not add history for myself post */
		if($post->post_author == $comment->user_id) return false;
		
		$meta = array(
			'type' => 'post-reply',
			'comment-id' => $comment_id,
			'timestamp' => current_time('timestamp'),
		);
		/**
		 * add to history
		 */
		self::add_history($post->post_author,$meta);
		/**
		 * update point
		 */
		/**
		 * if not post type, return false
		 */
		if($post->post_type !== 'post')
			return false;
			
		$old_point = self::get_point($post->post_author);
		update_user_meta($post->post_author,self::$user_meta_key['point'],$old_point + (int)self::get_point_value('post-reply'));
	}
	/**
	 * HOOK add history for post author when publish post
	 */
	public static function add_action_publish_post_history_post_publish($new_status, $old_status, $post){
		if($old_status == 'publish' || $new_status != 'publish')
			return false;
		
		if($post->post_type !== 'post')
			return false;
			
		/**
		 * add history for post author
		 */
		self::action_add_history_core_post_publish($post->ID,$post);
	}
	/**
	 * action_add_history_core_transition_post_status_post_publish
	 *
	 * @param int Post id
	 * @param object Post
	 * @version 1.0.1
	 */
	public static function action_add_history_core_post_publish($post_id,$post){
		
		/** if published, do not add point and history */
		if(class_exists('theme_custom_contribution') && theme_custom_contribution::is_once_published($edit_post_id))
			return false;
		
		$meta = array(
			'type' => 'post-publish',
			'post-id' => $post_id,
			'timestamp' => current_time('timestamp'),
		);
		/**
		 * add to history
		 */
		self::add_history($post->post_author,$meta);
		/**
		 * update point
		 */
		/**
		 * if is not post type, return false
		 */
		if($post->post_type !== 'post')
			return false;
			
		$old_point = self::get_point($post->post_author);
		update_user_meta($post->post_author,self::$user_meta_key['point'],$old_point + (int)theme_options::get_options(__CLASS__)['points']['post-publish']);
	}
	public static function backend_js_config(array $config){
		$config[__CLASS__] = [
			'process_url' => theme_features::get_process_url(array('action'=>__CLASS__)),
		];
		return $config;
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_custom_point::init';
	return $fns;
});