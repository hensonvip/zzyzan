<?php
/**
 * theme_page_rank
 *
 * @version 1.0.1
 */
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_page_rank::init';
	return $fns;
});
class theme_page_rank{
	
	public static $page_slug = 'rank';
	
	public static function init(){
		add_action('init', __CLASS__ . '::page_create');

		//add_action('wp_enqueue_scripts', __CLASS__  . '::frontend_enqueue_css');

		add_filter('query_vars', __CLASS__ . '::filter_query_vars');
		
	}

	public static function page_create(){
		if(!theme_cache::current_user_can('manage_options')) 
			return false;
		
		$page_slugs = array(
			self::$page_slug => array(
				'post_content' 	=> '',
				'post_name'		=> self::$page_slug,
				'post_title'	=> ___('Rank'),
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
	public static function is_page(){
		static $cache = null;
		if($cache === null)
			$cache = theme_cache::is_page(self::$page_slug);

		return $cache;
	}
	public static function filter_query_vars($vars = []){
		if(!in_array('filter',$vars))
			$vars[] = 'filter';
			
		if(!in_array('tab',$vars))
			$vars[] = 'tab';
			
		return $vars;
	}
	public static function get_tabs($key = null){
		static $base_url = null , $tabs = null;
		if($base_url === null)
			$base_url = theme_cache::get_permalink(theme_cache::get_page_by_path(self::$page_slug)->ID);

		if($tabs === null)
			$tabs = [
				'recommend' => [
					'tx' => ___('Recommend'),
					'icon' => 'star',
					'url' => esc_url(add_query_arg([
						'tab' => 'recommend'
					],$base_url)),
				],
				
				'popular' => [
					'tx' => ___('Popular'),
					'icon' => 'bar-chart',
					'url' => esc_url(add_query_arg([
						'tab' => 'popular'
					],$base_url)),
					'filters' => [
						'day' => [
							'tx' => ___('Daily popular'),
							'url' => esc_url(add_query_arg([
								'tab' => 'popular',
								'filter' => 'day',
							],$base_url)),
						],
						'week' => [
							'tx' => ___('Weekly popular'),
							'url' => add_query_arg([
								'tab' => 'popular',
								'filter' => 'week',
							],$base_url),
						],
						'month' => [
							'tx' => ___('Monthly popular'),
							'url' => esc_url(add_query_arg([
								'tab' => 'popular',
								'filter' => 'month',
							],$base_url)),
						],
					],/** end filter */
				],/** end popular */
				'latest' => [
					'tx' => ___('Latest'),
					'icon' => 'refresh',
					'url' => esc_url(add_query_arg([
						'tab' => 'latest'
					],$base_url)),
				],
				

				'users' => [
					'tx' => ___('Users'),
					'icon' => 'users',
					'url' => esc_url(add_query_arg([
						'tab' => 'users'
					],$base_url)),
					'filter' => [
						'me' => [
							'tx' => ___('Me'),
							'url' => esc_url(add_query_arg([
								'tab' => 'user',
								'filter' => 'me',
							],$base_url)),
						],/** end me */
					],/** end filters */
				],/** end users */
			];/** end types */
			
		if($key)
			return isset($tabs[$key]) ? $tabs[$key] : false;
			
		return $tabs;
	}
	public static function the_users_rank(){
		
	}
	public static function the_latest_posts(array $args = []){
		$cache = theme_cache::get('latest','page-rank');
		if(!empty($cache)){
			echo $cache;
			unset($cache);
			return;
		}
		global $post;
		$args = array_merge([
			'posts_per_page ' => 100,
			'paged' => 1,
			'ignore_sticky_posts' => true,
		],$args);

		$query = new WP_Query($args);
		
		ob_start();
		if($query->have_posts()){
			?>
			<div class="list-group">
				<?php
				$i = 1;
				foreach($query->posts as $post){
					setup_postdata($post);
					self::rank_img_content([
						'index' => $i,
					]);
					++$i;
				}
				?>
			</div>
			<?php
			wp_reset_postdata();
		}else{
			
		}
		$cache = html_minify(ob_get_contents());
		ob_end_clean();

		theme_cache::set('latest',$cache,'page-rank',3600);
		echo $cache;
		unset($cache);
	}
	public static function get_popular_posts(array $args = []){
		$active_filter_tab = get_query_var('filter');
		$filter_tabs = self::get_tabs('popular')['filters'];
		
		if(!isset($filter_tabs[$active_filter_tab]))
			$active_filter_tab = 'day';

		$cache_id = 'popular-' . $active_filter_tab; 
		$cache = theme_cache::get($cache_id,'page-rank');
		if(!empty($cache)){
			return $cache;
		}
		global $post;
		$args = array_merge([
			'posts_per_page ' => 30,
			'paged' => 1,
			'date_query' => [
				[
					'column' => 'post_date_gmt',
					'after'  => '1 ' . $active_filter_tab . ' ago',
				]
			],
			'ignore_sticky_posts' => true,
		],$args);
		/**
		 * orderby points
		 */
		//if(class_exists('custom_post_point')){
		//	$args['meta_key']  = custom_post_point::$post_meta_key['count_points'];
		//	$args['orderby'] = 'meta_value_num';
		/**
		 * orderby views
		 */
		//}else 
		if(class_exists('theme_post_views')){
			$args['meta_key']  = theme_post_views::$post_meta_key;
			$args['orderby'] = 'meta_value_num';
		/**
		 * orderby comment count
		 */
		}else{
			$args['orderby'] = 'comment_count';
		}
		
		$query = new WP_Query($args);
		
		ob_start();
		if($query->have_posts()){
			?>
			<div class="list-group">
				<?php
				$i = 1;
				foreach($query->posts as $post){
					setup_postdata($post);
					self::rank_img_content([
						'index' => $i,
						'lazyload' => $i <= 5 ? false : true,
					]);
					++$i;
				}
				?>
			</div>
			<?php
			wp_reset_postdata();
		}
		$cache = html_minify(ob_get_contents());
		ob_end_clean();

		theme_cache::set($cache_id,$cache,'page-rank',3600);
		return $cache;
	}
	public static function the_recommend_posts(array $args = []){
		$cache = theme_cache::get('recommend','page-rank');
		if(!empty($cache)){
			echo $cache;
			return $cache;
		}
		global $post;
		$args = array_merge([
			'posts_per_page ' => 100,
			'paged' => 1,
			'orderby' => 'rand',
			'post__in' => theme_recommended_post::get_ids(),
			'ignore_sticky_posts' => false,
		],$args);

		$query = new WP_Query($args);
		
		ob_start();
		if($query->have_posts()){
			?>
			<div class="list-group">
				<?php
				$i = 1;
				foreach($query->posts as $post){
					setup_postdata($post);
					self::rank_img_content([
						'index' => $i,
						'lazyload' => $i <= 5 ? false : true,
					]);
					++$i;
				}
				?>
			</div>
			<?php
			wp_reset_postdata();
		}else{
			
		}
		$cache = html_minify(ob_get_contents());
		ob_end_clean();

		theme_cache::set('recommend',$cache,'page-rank',3600);
		echo $cache;
		return $cache;
	}
	public static function rank_img_content($args = []){
		global $post;
		
		$args = array_merge([
			'classes' => '',
			'lazyload' => true,
			'excerpt' => true,
			'index' => false,
			'target' => theme_functions::$link_target,
		],$args);

		$post_title = theme_cache::get_the_title($post->ID);

		$excerpt = get_the_excerpt();
		if(!empty($excerpt))
			$excerpt = esc_html($excerpt);

		$thumbnail_real_src = theme_functions::get_thumbnail_src($post->ID);

		?>
		<div class="list-group-item <?= $args['classes'];?>">
			<div class="row">
				<div class="g-tablet-1-6">
					<a href="<?= theme_cache::get_permalink($post->ID);?>" title="<?= $post_title;?>" target="<?= $args['target'];?>" class="thumbnail-container">
						<?php if($args['lazyload'] === true){ ?>
							<img class="thumbnail" src="<?= theme_functions::$thumbnail_placeholder;?>" data-src="<?= $thumbnail_real_src;?>" alt="<?= $post_title;?>" width="<?= theme_functions::$thumbnail_size[1];?>" height="<?= theme_functions::$thumbnail_size[2];?>">
						<?php }else{ ?>
							<img class="thumbnail" src="<?= $thumbnail_real_src;?>" alt="<?= $post_title;?>" width="<?= theme_functions::$thumbnail_size[1];?>" height="<?= theme_functions::$thumbnail_size[2];?>">
						<?php } ?>
					</a>
				</div>
				<div class="g-tablet-5-6">
					<h3 class="media-heading">
						<a href="<?= theme_cache::get_permalink($post->ID);?>" title="<?= $post_title;?>" target="<?= $args['target'];?>" ><?= $post_title;?></a>
					</h3>
					<?php
					/**
					 * output excerpt
					 */
					if($args['excerpt'] === true){
						?>
						<div class="excerpt"><?= str_sub(strip_tags($excerpt),200);?></div>
					<?php } ?>
					<div class="extra">
						<div class="metas row">
							<!-- author -->
							<a class="author meta g-phone-1-2 g-tablet-1-4 g-desktop-1-5" href="<?= theme_cache::get_author_posts_url($post->post_author);?>" target="<?= $args['target'];?>" >
								<img src="<?= theme_functions::$avatar_placeholder;?>" data-src="<?= theme_cache::get_avatar_url($post->post_author);?>" alt="avatar" width="16" height="16" class="avatar"> 
								<?= theme_cache::get_the_author_meta('display_name',$post->post_author);?>
							</a>
							
							<!-- category -->
							<div class="category meta g-phone-1-2 g-tablet-1-4 g-desktop-1-5">
								<?php
								$cats = get_the_category_list('<i class="split"> / </i> ');
								if(!empty($cats)){
									?>
									<i class="fa fa-folder-open"></i> 
									<?= $cats;?>
								<?php } ?>
							</div>

							<!-- views -->
							<?php if(class_exists('theme_post_views') && theme_post_views::is_enabled()){ ?>
								<div class="view meta g-phone-1-2 g-tablet-1-4 g-desktop-1-5">
									<i class="fa fa-play-circle"></i> 
									<?= theme_post_views::get_views();?>
								</div>
							<?php } ?>

							<?php if(!wp_is_mobile()){ ?>
								<div class="comments meta g-phone-1-2 g-tablet-1-4 g-desktop-1-5">
									<i class="fa fa-comment"></i> 
									<?= (int)$post->comment_count;?>
								</div>
							<?php } ?>
							
							<?php
							/**
							 * point
							 */
							if(class_exists('custom_post_point')){
								?>
								<div class="point meta g-phone-1-2 g-tablet-1-4 g-desktop-1-5">
									<i class="fa fa-paw"></i>
									<?= (int)custom_post_point::get_post_points_count($post->ID);?>
								</div>
								<?php
							}
							?>


						</div><!-- /.metas -->
					</div>
					<?php if($args['index']){ ?>
						<i class="index"><?= $args['index'];?></i>
					<?php } ?>					
				</div>
			</div>
		</div>
		<?php
	}
	public static function frontend_enqueue_css(){
		if(!self::is_page())
			return false;
			
		wp_enqueue_style(
			__CLASS__,
			theme_features::get_theme_addons_css(__DIR__),
			'frontend',
			theme_file_timestamp::get_timestamp()
		);
	}
}