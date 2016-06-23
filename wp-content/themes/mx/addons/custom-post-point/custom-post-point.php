<?php
/**
 * @version 1.0.1
 */

class custom_post_point{
	public static $post_meta_key = [
		'users'				=> '_point_raters',
		'count_users' 		=> '_point_count_raters',
		'count_points' 		=> '_point_count_points',
	];
	public static $user_meta_key = [
		'posts' 		=> '_point_posts',
	];
	public static $error = [];
	
	public static function init(){
		add_action('wp_ajax_' . __CLASS__, __CLASS__ . '::process');
		add_action('wp_ajax_nopriv_' . __CLASS__, __CLASS__ . '::process');
		
		add_action('wp_ajax_backend_' . __CLASS__, __CLASS__ . '::process_backend');


		add_action('before_delete_post', __CLASS__ . '::sync_delete_post');

		add_filter('frontend_js_config', __CLASS__ . '::frontend_js_config');

		add_filter('custom_point_value_default', __CLASS__ . '::filter_custom_point_value_default');

		add_filter('custom_point_types', __CLASS__ . '::filter_custom_point_types');

		/**
		 * backend options
		 */
		add_action('theme_custom_point_backend' , __CLASS__ . '::theme_custom_point_backend');
		/**
		 * list history hooks
		 */
		foreach([
			'list_history_post_rate',
			'list_history_post_be_rate'
		] as $v)
			add_action('list_point_histroy', __CLASS__ . '::' . $v);

		//add_action('pre_get_posts' , __CLASS__ . '::pre_get_posts_in_rates');
	}
	public static function sync_delete_post($post_id){
		$post = theme_cache::get_post($post_id);
		if(!$post)
			return;
			
		if($post->post_type !== 'post')
			return;
			
		$rater_ids = (array)self::get_post_raters($post_id);
		if(!empty($raters)){
			foreach($rater_ids as $rater_id){
				
				self::decr_post_raters($post_id,$rater_id);
				self::decr_post_raters_count($post_id);
				self::decr_rater_posts($post_id,$rater_id);
			}
		}
		
	}
	public static function process_backend(){
		theme_features::check_referer();
		theme_features::check_nonce();
		
		if(!theme_cache::current_user_can('manage_options'))
			return false;
			
		$type = isset($_GET['type']) && is_string($_GET['type']) ? $_GET['type'] : false;
		
		switch($type){
			case 'recalculate':
				global $post;
				$query = new WP_Query([
					'nopaging' => true,
					'meta_key' => self::$post_meta_key['count_points'],
				]);
				if($query->have_posts()){
					foreach($query->posts as $post){
						setup_postdata($post);
						/** get points from db */
						$old_points = get_post_meta($post->ID,self::$post_meta_key['count_points'],true);
						$new_points = self::get_post_points_count_from_users($post->ID);

						/**
						 * skip if equal
						 */
						if($old_points == $new_points)
							continue;

						/**
						 * update new points
						 */
						update_post_meta($post->ID,self::$post_meta_key['count_points'],$new_points);
					}
				}
				header('location: ' . theme_options::get_url() . '&' . __CLASS__);
				die;
				
				break;
			default:
				die(theme_features::json_format([
					'status' => 'error',
					'code' => 'invaild_type',
					'msg' => ___('Sorry, type param is invaild.'),
				]));
		}
	}
	public static function theme_custom_point_backend(){
		?>
		<h3><?= ___('Recalculate');?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<th><?= ___('Recalculate all posts point');?></th>
					<td>
						<?php
						if(isset($_GET[__CLASS__])){
							echo status_tip('success',___('Operation completed.'));
						}
						?>
						<a href="<?= theme_features::get_process_url([
							'action' => 'backend_' . __CLASS__,
							'type' => 'recalculate',
							'theme-nonce' => theme_features::create_nonce(),
						]);?>" class="button">
							<i class="fa fa-refresh"></i> 
							<?= ___('Recalculate now');?>
						</a>
						<span class="description">
							<i class="fa fa-exclamation-circle"></i> 
							<?= ___('Please save your settings before operate. This operation requires a lot of server resources.');?>
						</span>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}
	/**
	 * 随机在总量中获取被投币最多的文章排行（最受欢迎的文章）
	 *
	 * @param array $args
	 * @return array
	 * @version 1.0.0
	 */
	public static function get_most_point_posts(array $args = []){
		$defaults = [
			'total_number' => 20,
			'posts_per_page' => 10,
			'paged' => 1,
			'orderby' => 'desc',
			'expire' => 3600*24,
		];
		$args = array_merge($defaults,$args);
		$cache_id = md5(serialize(func_get_args()));
		$caches = wp_cache_get('most_point_posts',__CLASS__);
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];

		/**
		 * get posts from database
		 */
		$query = new WP_Query([
			'posts_per_page' => (int)$args['posts_per_page'],
			'paged' => (int)$args['paged'],
			'meta_key' => self::$post_meta_key['count_users'],
			'orderby' => 'meta_value_num',
			'order' => 'DESC',
		]);
		if($args['orderby'] === 'rand' && !empty($query)){
			$rand_post_ids = [];
			foreach($query->posts as $post){
				setup_postdata($post);
				$rand_post_ids[] = $post->ID;
			}
			wp_reset_postdata();
			/**
			 * rand query
			 */
			$query = new WP_Query([
				'posts_per_page' => (int)$args['posts_per_page'],
				'paged' => (int)$args['paged'],
				'post__in' => $rand_post_ids,
				'orderby' => 'rand',
			]);
			
		}
		
		$caches[$cache_id] = $query;
		//wp_reset_postdata();
		wp_cache_set('most_point_posts',$caches,__CLASS__,$args['expire']);
		return $caches[$cache_id];
	}

	/**
	 * 获取文章被投币的统计数量
	 *
	 * @param int $post_id
	 * @return int
	 * @version 1.0.0
	 */
	public static function get_post_points_count($post_id){
		static $caches = [];
		if(isset($caches[$post_id]))
			return $caches[$post_id];

		$caches[$post_id] = self::get_post_meta($post_id,self::$post_meta_key['count_points'],true);

		return $caches[$post_id];
	}
	public static function get_post_raters_count($post_id){
		static $caches = [];
		if(isset($caches[$post_id]))
			return $caches[$post_id];

		$caches[$post_id] = (array)self::get_post_meta($post_id,self::$post_meta_key['users']);

		$caches[$post_id] = count($caches[$post_id]);
		
		return $caches[$post_id];
	}
	private static function get_post_meta($post_id,$meta_key){
		static $caches = [];
		$cache_id = md5($post_id . $meta_key);
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];

		$caches[$post_id] = get_post_meta($post_id,$meta_key,true);
		return $caches[$post_id];
	}
	/**
	 * 获取文章的投币用户
	 *
	 * @param int $post_id
	 * @return array Users
	 * @version 1.0.0
	 */
	public static function get_post_raters($post_id){
		if(!is_numeric($post_id))
			return false;
			
		static $caches = [];
		if(isset($caches[$post_id]))
			return $caches[$post_id];
			
		$caches[$post_id] = (array)self::get_post_meta($post_id,self::$post_meta_key['users']);
		return $caches[$post_id];
	}
	/**
	 * 通过计算文章投币用户数量来获取文章积分
	 */
	public static function get_post_points_count_from_users($post_id){
		$users = self::get_post_raters($post_id);

		if(!$users)
			return false;

		$points = 0;
		foreach($users as $user_point){
			$points += $user_point;
		}

		return $points;
	}
	public static function get_user_post_ids($user_id){
		static $caches = [];
		if(!isset($caches[$user_id]))
			$caches[$user_id] = get_user_meta($user_id,self::$user_meta_key['posts'],true);
		if(!$caches[$user_id] || !is_array($caches[$user_id]))
			return false;
		$caches[$user_id] = array_keys($caches[$user_id]);
		return $caches[$user_id];
	}
	/**
	 * 获取用户投币过的文章
	 *
	 * @param int $user_id
	 * @param array $query_args
	 * @return array
	 * @version 1.0.0
	 */
	public static function get_user_posts($user_id,array $query_args = []){
		static $caches = [];
		$query_args = array_merge([
			'posts_per_page' => 10,
			'paged' => 1,
		],$query_args);
		
		$cache_id = md5(json_encode($query_args));
		
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];
			
		$post_ids = self::get_user_post_ids($user_id);
		if(empty($post_ids)){
			$caches[$cache_id] = false;
			return false;
		}
		$query_args['post__in'] = $post_ids;

		$caches[$cache_id] = new WP_Query($query_args);
		return $caches[$cache_id];
	}
	public static function pre_get_posts_in_rates($query){
//var_dump($query);
		if($query->is_author && $query->is_main_query()){
			if(!get_query_var('tab') === 'rates')
				return false;
				
			$author = $query->query_vars['author'];
			if(!$author)
				return false;
				
			if(!self::get_user_post_ids($author))
				return false;
				
			$query->set('author',null);
			$query->set('post__in',self::get_user_post_ids($author));
			$query->set('ignore_sticky_posts',true);
		}
	}
	public static function filter_custom_point_types(array $types = []){
		$types['post-swap'] = [
			'text' => ___('When post point swap'),
			'type' => 'text',
			'des' => ___('Use commas to separate multiple point, first as the default.'),
		];
		return $types;
	}
	public static function filter_custom_point_value_default(array $opts = []){
		$opts['post-swap'] = self::get_point_values_default(true);
		return $opts;
	}
	public static function get_point_values_default($text = false){
		return $text === true ? '3,1,5' : [3,1,5];
	}
	public static function get_point_values(){
		static $cache = null;

		if($cache !== null)
			return $cache;

		$values = explode(',',theme_custom_point::get_point_value('post-swap'));
		if(!is_null_array($values)){
			$cache = array_map(function($v){
				$v = trim($v);
				if(is_numeric($v))
					return $v;
			},$values);
		}else{
			$cache = self::get_point_values_default();
		}
		return $cache;
	}
	public static function process(){
		$output = [];

		theme_features::check_referer();
		theme_features::check_nonce();

		
		$type = isset($_GET['type']) && is_string($_GET['type']) ? $_GET['type'] : null;

		$post_id = isset($_POST['post-id']) && is_numeric($_POST['post-id']) ? (int)$_POST['post-id'] : null;
		if(!$post_id){
			$output['status'] = 'error';
			$output['code'] = 'invaild_post_id';
			$output['msg'] = ___('Invaild post id param.');
			die(theme_features::json_format($output));
		}
		
		$post = theme_cache::get_post($post_id);
		if(!$post || $post->post_type !== 'post')
			die(theme_features::json_format([
				'status' => 'error',
				'code' => 'post_not_exist',
				'msg' => ___('Post does not exist.'),
			]));
		/**
		 * check user logged
		 */
		if(!theme_cache::is_user_logged_in()){
			$output['status'] = 'error';
			$output['code'] = 'need_login';
			$output['msg'] = '<a href="' . wp_login_url(theme_cache::get_permalink($post->ID)) . '" title="' . ___('Go to log-in') . '">' . ___('Sorry, please log-in.') . '</a>';
			die(theme_features::json_format($output));
		}
		
		$rater_id = theme_cache::get_current_user_id();

		switch($type){
			/**
			 * incr point
			 */
			case 'incr':
				/**
				 * points
				 */
				$points = isset($_POST['points']) && is_numeric($_POST['points']) ? (int)$_POST['points'] : null;
				if(!in_array($points,self::get_point_values())){
					$output['status'] = 'error';
					$output['code'] = 'invaild_point_value';
					$output['msg'] = ___('Invaild point value.');
					die(theme_features::json_format($output));
				}
				/**
				 * incr post raters
				 */
				$post_raters = self::incr_post_raters($post_id,$rater_id,$points);
				
				if($post_raters !== true){
					die(theme_features::json_format($post_raters));
				}else{
					/**
					 * incr post points
					 */
					$points_count = self::incr_post_points_count($post_id,$points);
					if(!$points_count){
						$output['status'] = 'error';
						$output['code'] = 'error_incr_points_count';
						$output['msg'] = ___('Sorry, system can not increase post points count.');
						die(theme_features::json_format($output));
					}
					/**
					 * incr rater posts
					 */
					$rater_posts = self::incr_rater_posts($post_id,$rater_id,$points);
					if($rater_posts !== true){
						$output['status'] = 'error';
						$output['code'] = 'error_incr_rater_posts';
						$output['msg'] = ___('System can not increase rater posts.');
						die(theme_features::json_format($output));
					}
					/**
					 * increase post author points
					 */
					theme_custom_point::incr_user_points($post->post_author,$points);
					/**
					 * add point history for rater
					 */
					self::add_history_for_rater($post_id,$rater_id,$points);
					/**
					 * add point history for post author
					 */
					self::add_history_for_post_author($post_id,$rater_id,$points);
					/**
					 * decrease rater points
					 */
					theme_custom_point::decr_user_points($rater_id,$points);
					
					/**
					 * success
					 */
					$output['status'] = 'success';
					$output['points'] = (int)self::get_post_points_count($post_id);
					$output['msg'] = ___('Operation successful, thank you for your participation.');
					die(theme_features::json_format($output));
				}
				break;
			default:
				$output['status'] = 'error';
				$output['code'] = 'invaild_type';
				$output['msg'] = ___('Invaild type param.');
				die(theme_features::json_format($output));
		}
			

		die(theme_features::json_format($output));
	}
	public static function list_history_post_rate($history){
		if($history['type'] !== 'post-rate')
			return false;
		?>
		<li class="list-group-item">
			<?php theme_custom_point::the_list_icon('thumbs-o-up');?>
			<?php theme_custom_point::the_point_sign($history['points']);?>
			
			<span class="history-text">
				<?php
				$post = theme_cache::get_post($history['post-id']);
				if(!$post){
					echo ___('The post has been deleted.');
				}else{
					echo sprintf(
						___('You rated %1$d %2$s for the post %3$s.'),
						abs($history['points']),
						theme_custom_point::get_point_name(),
						'<a href="' . theme_cache::get_permalink($history['post-id']) . '">' . theme_cache::get_the_title($history['post-id']) . '</a>'
					);
				}
				?>
			</span>
			
			<?php theme_custom_point::the_time($history);?>
		</li>
		<?php
	}
	public static function list_history_post_be_rate($history){
		if($history['type'] !== 'post-be-rate')
			return false;
		?>
		<li class="list-group-item">
			<?php theme_custom_point::the_list_icon('thumbs-up');?>
			<?php theme_custom_point::the_point_sign($history['points']);?>
			
			<span class="history-text">
				<?php
				$post = theme_cache::get_post($history['post-id']);
				if(!$post){
					echo ___('The post has been deleted.');
				}else{
					echo sprintf(
						___('You post %1$s has been rated %2$d %3$s by %4$s.'),
						'<a href="' . theme_cache::get_permalink($history['post-id']) . '">' . theme_cache::get_the_title($history['post-id']) . '</a>',
						abs($history['points']),
						theme_custom_point::get_point_name(),
						esc_html(get_author_meta('display_name',$history['rater-id']))
					);
				}
				?>
			</span>
			
			<?php theme_custom_point::the_time($history);?>
		</li>
		<?php
	}
	
	private static function get_timestamp(){
		static $cache = null;
		if($cache === null)
			$cache = current_time('timestamp');
		return $cache;
	}
	public static function add_history_for_rater($post_id,$rater_id,$points){

		$meta = [
			'type'=> 'post-rate',
			'timestamp' => self::get_timestamp(),
			'post-id' => $post_id,
			'points' => 0 - $points,
		];
		add_user_meta($rater_id,theme_custom_point::$user_meta_key['history'],$meta);
		
	}
	public static function add_history_for_post_author($post_id,$rater_id,$points){

		$meta = [
			'type'=> 'post-be-rated',
			'timestamp' => self::get_timestamp(),
			'rater-id' => $rater_id,
			'post-id' => $post_id,
			'points' => $points,
		];
		add_user_meta(theme_cache::get_post($post_id)->post_author,theme_custom_point::$user_meta_key['history'],$meta);
		
	}

	/**
	 * 递增文章积分统计
	 *
	 * @param int $post_id
	 * @param int $points
	 * @return bool/int
	 * @version 1.0.0
	 */
	public static function incr_post_points_count($post_id,$points){
		if(!is_numeric($post_id) || (int)$post_id === 0)
			return [
				'status' => 'error',
				'code' => 'invaild_post_id',
				'msg' => ___('Invaild post id.'),
			];

		if(!is_numeric($points) || (int)$points === 0)
			return [
				'status' => 'error',
				'code' => 'invaild_point_value',
				'msg' => ___('Invaild points value.'),
			];
			
		$count = (int)get_post_meta($post_id,self::$post_meta_key['count_points'],true);
		
		$count += $points;
		
		update_post_meta($post_id,self::$post_meta_key['count_points'],$count);
		return true;
	}
	/**
	 * 递增文章用户统计
	 *
	 * @param int $post_id
	 * @return bool/int
	 * @version 1.0.0
	 */
	public static function incr_post_raters_count($post_id){
		if(!is_numeric($post_id) || !(int)$post_id)
			return false;
			
		$count = (int)get_post_meta($post_id,self::$post_meta_key['count_users'],true);
		
		$count++;
		
		update_post_meta($post_id,self::$post_meta_key['count_users'],$count);
		return $count;
	}
	/**
	 * 递减文章用户统计
	 *
	 * @param int $post_id
	 * @return bool/int
	 * @version 1.0.0
	 */
	public static function decr_post_raters_count($post_id){
		if(!is_numeric($post_id) || !(int)$post_id)
			return false;
			
		$count = (int)get_post_meta($post_id,self::$post_meta_key['count_users'],true);

		if($count === 0)
			return false;
			
		$count--;
		
		update_post_meta($post_id,self::$post_meta_key['count_users'],$count);
		return $count;
	}
	/**
	 * 递增文章的送分用户
	 *
	 * @param int post id
	 * @param int user id
	 * @param int points
	 * @return bool/array True is success, array is error
	 * @version 1.0.0
	 */
	public static function incr_post_raters($post_id,$rater_id,$points){
			
		if(!is_numeric($post_id) || (int)$post_id === 0)
			return [
				'status' => 'error',
				'code' => 'invaild_post_id',
				'msg' => ___('Invaild post id.'),
			];
			
		if(!is_numeric($rater_id) || (int)$rater_id === 0)
			return [
				'status' => 'error',
				'code' => 'invaild_rater_id',
				'msg' => ___('Invaild rater id.'),
			];

		if(!is_numeric($points) || (int)$points === 0)
			return [
				'status' => 'error',
				'code' => 'invaild_points',
				'msg' => ___('Invaild points.'),
			];

		$post = theme_cache::get_post($post_id);
		if(!$post)
			return [
				'status' => 'error',
				'code' => 'post_not_exist',
				'msg' => ___('Post is not exist.'),
			];
		/**
		 * if post author is rater, return
		 */
		if($post->post_author == $rater_id)
			return [
				'status' => 'error',
				'code' => 'rate_myself',
				'msg' => ___('Sorry, you can not rate your post.'),
			];
		
		$rater = get_user_by('id',$rater_id);
		if(!$rater)
			return [
				'status' => 'error',
				'code' => 'rater_not_exist',
				'msg' => ___('Rater is not exist.'),
			];

			
		$raters = (array)get_post_meta($post_id,self::$post_meta_key['users'],true);
		/**
		 * already point, return false
		 */
		if(isset($raters[$rater_id]))
			return [
				'status' => 'error',
				'code' => 'rated',
				'msg' => ___('You had rated this post.'),
			];

	
		$raters[$rater_id] = $points;
		update_post_meta($post_id,self::$post_meta_key['users'],$raters);
		
		return true;
	}
	/**
	 * 递增投币用户的文章
	 *
	 * @param int $post_id Post id
	 * @param int $user_id rater id
	 * @param int $points $points
	 * @return 
	 * @version 1.0.0
	 */
	public static function incr_rater_posts($post_id,$rater_id,$points){
		if(!is_numeric($post_id) || (int)$post_id === 0)
			return [
				'status' => 'error',
				'code' => 'invaild_post_id',
				'msg' => ___('Invaild post id.'),
			];

		if(!is_numeric($rater_id) || (int)$rater_id === 0)
			return [
				'status' => 'error',
				'code' => 'invaild_rater_id',
				'msg' => ___('Invaild rater id.'),
			];
			
		if(!is_numeric($points) || (int)$points === 0)
			return [
				'status' => 'error',
				'code' => 'invaild_point_value',
				'msg' => ___('Invaild points value.'),
			];
			
		$post = theme_cache::get_post($post_id);
		if(!$post)
			return [
				'status' => 'error',
				'code' => 'post_not_exist',
				'msg' => ___('Post is not exist.'),
			];

		$rater = get_user_by('id',$rater_id);
		if(!$rater)
			return [
				'status' => 'error',
				'code' => 'rater_not_exist',
				'msg' => ___('Rater is not exist.'),
			];
			
		$posts = (array)get_user_meta($rater_id,self::$user_meta_key['posts'],true);
		
		if(isset($posts[$post_id]))
			return [
				'status' => 'error',
				'code' => 'rated',
				'msg' => ___('You had rated this post.'),
			];

		$posts[$post_id] = $points;
		update_user_meta($rater_id,self::$user_meta_key['posts'],$posts);

		return true;
	}
	/**
	 * 递减投币该文章的用户
	 *
	 * @return 
	 * @version 1.0.0
	 */
	public static function decr_post_raters($post_id,$rater_id){
		if(!is_numeric($post_id) || (int)$post_id === 0)
			return [
				'status' => 'error',
				'code' => 'invaild_post_id',
				'msg' => ___('Invaild post id.'),
			];
			
		if(!is_numeric($rater_id) || (int)$rater_id === 0)
			return [
				'status' => 'error',
				'code' => 'invaild_rater_id',
				'msg' => ___('Invaild rater id.'),
			];
			

		$raters = (array)get_post_meta($post_id,self::$post_meta_key['users'],true);
		
		/**
		 * if not rated, return error
		 */
		if(!isset($raters[$user_id]))
			return [
				'status' => 'error',
				'code' => 'no_rated',
				'msg' => ___('You did not rate this post yet.'),
			];

		/**
		 * if already exist, just remove
		 */
		unset($raters[$user_id]);
		update_post_meta($post_id,self::$post_meta_key['users'],$raters);
		
		return true;
	}
	public static function decr_rater_posts($post_id,$rater_id){
		if(!is_numeric($post_id) || (int)$post_id === 0)
			return [
				'status' => 'error',
				'code' => 'invaild_post_id',
				'msg' => ___('Invaild post id.'),
			];
			
		if(!is_numeric($rater_id) || (int)$rater_id === 0)
			return [
				'status' => 'error',
				'code' => 'invaild_rater_id',
				'msg' => ___('Invaild rater id.'),
			];

		$posts = (array)get_user_meta($rater_id,self::$user_meta_key['posts'],true);

		if(!isset($posts[$post_id]))
			return [
				'status' => 'error',
				'code' => 'no_rated',
				'msg' => ___('You did not rate this post yet.'),
			];

		unset($posts[$post_id]);
		update_user_meta($rater_id,self::$user_meta_key['posts'],$posts);

		return true;
	}
	public static function frontend_js_config(array $config){
		if(!theme_cache::is_singular('post'))
			return $config;
			
		$config[__CLASS__] = [
			'process_url' => theme_features::get_process_url([
				'action' => __CLASS__,
				'type' => 'incr'
			]),
		];
		return $config;
	}
	public static function post_btn($post_id){
			
		$point_img = theme_custom_point::get_point_img_url();
		$point_values = array_filter((array)self::get_point_values());

		$count_point_values = count($point_values);
		$default_point_value = $point_values[0];
		if($count_point_values > 1){
			sort($point_values);
		}
		?>
		<div class="meta meta-post-point">
			<a 
				href="javascript:;" 
				class="post-point-btn" 
				title="<?= sprintf(__x('Rate %d %s.','E.g. Rate 3 points'),$default_point_value,theme_custom_point::get_point_name());?>" 
				data-post-id="<?= $post_id;?>" 
				data-points="<?= $default_point_value;?>" 
			>
				<div id="post-point-number-<?= $post_id;?>" class="number"><?= number_format((int)self::get_post_points_count($post_id));?></div>
				<div class="tx"><?= ___('Rate it');?></div>
			</a>
			<?php if(!wp_is_mobile() && $count_point_values > 1){ ?>
				<div class="box">
					<?php 
					foreach($point_values as $v){ 
						$class = $v == $default_point_value ? 'active' : null;
						?><a 
							href="javascript:;" 
							class="post-point-btn <?= $class;?>" 
							title="<?= sprintf(__x('Rate %d %s.','E.g. Rate 3 points'),$v,theme_custom_point::get_point_name());?>" 
							data-post-id="<?= $post_id;?>" 
							data-points="<?= $v;?>" 
						><?= $v;?></a><?php } ?>
				</div>
			<?php } ?>
		</div>
		<?php
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'custom_post_point::init';
	return $fns;
});