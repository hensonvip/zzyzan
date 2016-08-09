<?php
/** Theme options */
include __DIR__ . '/core/core-options.php';

/** Theme features */
include __DIR__ . '/core/core-features.php';

/** Theme functions */
include __DIR__ . '/core/core-functions.php';

/** 
 * theme_functions
 */
class theme_functions{
	public static $iden = 'mx';
	public static $basename;
	public static $theme_edition = 1;
	public static $theme_date = '2015-02-01 00:00';
	public static $thumbnail_size = ['thumbnail',320,200,true];
	public static $medium_size = ['medium',600,600,false];
	public static $large_size = ['large',1024,1024,false];
	public static $comment_avatar_size = 60;
	public static $thumbnail_placeholder = 'http://ww4.sinaimg.cn/large/686ee05djw1ew56itdn2nj208w05k0sp.jpg';
	public static $avatar_placeholder = 'http://ww2.sinaimg.cn/large/686ee05djw1ew5767l9voj2074074dfn.jpg';
	public static $cache_expire = 3600;
	public static $link_target = '_blank';
	public static $colors = [
		'61b4ca',	'e1b32a',	'ee916f',	'a89d84',
		'86b767',	'6170ca',	'c461ca',	'ca6161',
		'ca8661',	'333333',	'84a89e',	'a584a8'
	];
	/** 
	 * theme_meta_translate(
	 */
	public static function theme_meta_translate($key = null){
		$data = [
			'name' => ___('MX'),
			'theme_url' => ___('http://inn-studio.com/mx'),
			'author_url' => ___('http://inn-studio.com'),
			'author' => ___('INN STUDIO'),
			'qq' => [
				'number' => '249411513',
				'link' => 'http://wpa.qq.com/msgrd?v=3&amp;uin=249411513&amp;site=qq&amp;menu=yes',
			],
			'qq_group' => [
				'number' => '170306005',
				'link' => 'http://wp.qq.com/wpa/qunwpa?idkey=d8c2be0e6c2e4b7dd2c0ff08d6198b618156d2357d12ab5dfbf6e5872f34a499',
			],
			'email' => 'kmvan.com@gmail.com',
			'edition' => ___('Professional edition'),
			'des' => ___('MX - Dream starts'),
		];
		if(!$key)
			return $data;
		return isset($data[$key]) ? $data[$key] : false;
	}
	/** 
	 * init
	 */	
	public static function init(){
		/** 
		 * register menu
		 */
		register_nav_menus([
			'menu-header' 			=> ___('Header menu'),
			'menu-header-login' 	=> ___('Login header menu'),
			'menu-mobile' 			=> ___('Mobile menu'),
			'menu-mobile-login' 	=> ___('Login mobile menu'),
			//'menu-top-bar' 			=> ___('Top bar menu'),
			//'menu-top-bar-login'	=> ___('Login top bar menu'),
			'links-footer'			=> ___('Footer links'),
		]);
		/** 
		 * other
		 */
		add_action('widgets_init', __CLASS__ . '::widget_init');
		add_filter('use_default_gallery_style','__return_false');
		add_theme_support('html5',['comment-list','comment-form','search-form']);

		add_image_size(self::$thumbnail_size[0],self::$thumbnail_size[1],self::$thumbnail_size[2],self::$thumbnail_size[3]);
		
		add_image_size(self::$medium_size[0],self::$medium_size[1],self::$medium_size[2],self::$medium_size[3]);
		
		add_image_size(self::$large_size[0],self::$large_size[1],self::$large_size[2],self::$large_size[3]);

		add_theme_support('title-tag');
		/** 
		 * bg
		 */
		add_theme_support('custom-background',[
			'default-color'			=> 'eeeeee',
			'default-image'			=> '',
			'default-position-x'	=> 'center',
			'default-attachment'	=> 'fixed',
			'wp-head-callback'		=> 'theme_features::_fix_custom_background_cb',
		]);
	}
	/** 
	 * widget_init
	 */
	public static function widget_init(){
		$sidebar = [
			'widget-area-home' => [
				'name' 			=> ___('Home widget area'),
				'description' 	=> ___('Appears on home in the sidebar.'),
			],
			'widget-area-archive' => [
				'name' 			=> ___('Archive page widget area'),
				'description' 	=> ___('Appears on archive page in the sidebar.'),
			],
			'widget-area-footer' => [
				'name' 			=> ___('Footer widget area'),
				'description' 	=> ___('Appears on all page in the footer.'),
				'before_widget' => '<div class="g-desktop-1-4"><aside id="%1$s"><div class="widget %2$s">',
				'after_widget'		=> '</div></aside></div>',
			],
			'widget-area-post' => [
				'name' 			=> ___('Singular post widget area'),
				'description' 	=> ___('Appears on post in the sidebar.')
			],
			'widget-area-page' => [
				'name' 			=> ___('Singular page widget area'),
				'description' 	=> ___('Appears on page in the sidebar.'),
			],
			'widget-area-404' => [
				'name' 			=> ___('404 page widget area'),
				'description' 	=> ___('Appears on 404 no found page in the sidebar.'),
			],
		];
		foreach($sidebar as $k => $v){
			register_sidebar([
				'id'				=> $k,
				'name'				=> $v['name'],
				'description'		=> $v['description'],
				'before_widget'		=> isset($v['before_widget']) ? $v['before_widget'] : '<aside id="%1$s"><div class="widget %2$s">',
				'after_widget'		=> isset($v['after_widget']) ? $v['after_widget'] : '</div></aside>',
				'before_title'		=> isset($v['before_title']) ? $v['before_title'] : '<div class="heading "><h2 class="widget-title">',
				'after_title'		=> isset($v['after_title']) ? $v['after_widget'] : '</h2></div>',
			]);
		}
	}
	public static function get_posts_query(array $args = [],array $query_args = []){
		global $paged;
		$args = array_merge([
			'orderby' => 'views',
			'order' => 'desc',
			'posts_per_page' => theme_cache::get_option('posts_per_page'),
			'paged' => 1,
			'category__in' => [],
			'date' => 'all',
			
		],$args);
		
		$query_args = array_merge([
			'posts_per_page' => $args['posts_per_page'],
			'paged' => $args['paged'],
			'ignore_sticky_posts' => true,
			'category__in' => $args['category__in'],
			'post_status' => 'publish',
			'post_type' => 'post',
			'has_password' => false,
		],$query_args);
		
		switch($args['orderby']){
			case 'views':
				$query_args['meta_key'] = 'views';
				$query_args['orderby'] = 'meta_value_num';
				break;
			case 'thumb-up':
			case 'thumb':
				$query_args['meta_key'] = 'post_thumb_count_up';
				$query_args['orderby'] = 'meta_value_num';
				break;
			case 'rand':
			case 'random':
				$query_args['orderby'] = 'rand';
				break;
			case 'latest':
				$query_args['orderby'] = 'date';
				break;
			case 'comment':
				$query_args['orderby'] = 'comment_count';
				break;
			case 'recomm':
			case 'recommended':
				if(class_exists('theme_recommended_post')){
					$query_args['post__in'] = (array)theme_recommended_post::get_ids();
				}else{
					$query_args['post__in'] = (array)theme_cache::get_option( 'sticky_posts' );
					unset($query_args['ignore_sticky_posts']);
				}
				unset($query_args['post__not_in']);
				break;
			default:
				$query_args['orderby'] = 'date';
		}
		if($args['date'] && $args['date'] != 'all'){
			/** 
			 * date query
			 */
			switch($args['date']){
				case 'daily' :
					$after = 'day';
					break;
				case 'weekly' :
					$after = 'week';
					break;
				case 'monthly' :
					$after = 'month';
					break;
				default:
					$after = 'day';
			}
			$query_args['date_query'] = [[
				'column' => 'post_date_gmt',
				'after'  => '1 ' . $after . ' ago',
			]];
		}
		return new WP_Query($query_args);
	}
	public static function archive_card_text(array $args = []){
		global $post;
		$args = array_merge([
			'classes' => '',
			'lazyload' => false,
			'excerpt' => true,
			'target' => theme_functions::$link_target,
			'children' => 3,
		]);
		$args['classes'] .= ' card text ';
		$thumbnail_real_src = theme_functions::get_thumbnail_src($post->ID);
		$post_title = theme_cache::get_the_title($post->ID);
		$excerpt = get_the_excerpt();
		if(!empty($excerpt))
			$excerpt = esc_html($excerpt);
		?>
		<article class="<?= $args['classes'];?>">
			<div class="card-bg">
				<div class="media">
					<a 
						class="media-left" 
						href="<?= theme_cache::get_permalink($post->ID);?>" 
						title="<?= $post_title;?>" 
						target="<?= $args['target'];?>" 
						style="width:20%;"
					>
						<div class="thumbnail-container">
							<?php
							/**
							 * lazyload img
							 */
							if($args['lazyload']){
								?>
								<img class="thumbnail" src="<?= theme_functions::$thumbnail_placeholder;?>" data-src="<?= $thumbnail_real_src;?>" alt="<?= $post_title;?>" width="<?= self::$thumbnail_size[1];?>" height="<?= self::$thumbnail_size[2];?>" >
							<?php }else{ ?>
								<img class="thumbnail" src="<?= $thumbnail_real_src;?>" alt="<?= $post_title;?>" width="<?= self::$thumbnail_size[1];?>" height="<?= self::$thumbnail_size[2];?>" >
							<?php } ?>
						</div>
					</a>
					<div class="media-body">
						<a title="<?= $post_title;?>" class="media-heading" href="<?= theme_cache::get_permalink($post->ID);?>" target="<?= $args['target'];?>" >
							<h3 class="title"><?= $post_title;?></h3>
						</a>
						<?php
						/**
						 * output excerpt
						 */
						if($args['excerpt'] === true){
							?>
							<div class="excerpt"><?= str_sub(strip_tags($excerpt),200);?></div>
						<?php } ?>
						<div class="media-excerpt">
							<time class="time meta" datetime="<?= get_the_time('Y-m-d H:i:s',$post->ID);?>" title="<?= get_the_time(___('M j, Y'),$post->ID);?>">
								<i class="fa fa-clock-o"></i> <?= friendly_date(get_the_time('U',$post->ID));?>
							</time>

							<!-- author -->
							<a 
								href="<?= theme_cache::get_author_posts_url($post->post_author);?>" 
								class="meta author" 
								title="<?= $author_display_name;?>" 
								target="<?= $args['target'];?>" 
							><i class="fa fa-user"></i> <?= theme_cache::get_the_author_meta('display_name',$post->post_author);?></a>

							<!-- views -->
							<?php if(class_exists('theme_post_views') && theme_post_views::is_enabled()){ ?>
								<span class="view meta">
									<i class="fa fa-play-circle"></i> 
									<?= theme_post_views::get_views();?>
								</span>
							<?php } ?>
						</div>
					</div>
				</div><!-- .media -->
			</div>
		</article>
		<?php
	}
	public static function get_attachments_html(array $args, $expire = 3600){
		$args = array_merge([
			'post_status' => 'inherit',
			'post_type' => 'attachment',
			'order' => 'ASC',
		],$args);
		$cache_id = md5(json_encode($args));
		$cache = theme_cache::get($cache_id);
		if($cache){
			return $cache;
		}
		$children = get_children($args);
		$children = array_values($children);
		if($children){
			ob_start();
			?>
			<div class="row">
			<?php
			foreach($children as $child){
				$child_img = wp_get_attachment_image_src($child->ID, 'thumbnail');
				?>
				<div class="g-phone-1-<?= $args['children'];?>">
					<?php if($args['lazyload']){ ?>
						<img class="thumbnail" src="<?= theme_functions::$thumbnail_placeholder;?>" data-src="<?= $child_img[0];?>" alt="<?= $post_title;?>" width="<?= self::$thumbnail_size[1];?>" height="<?= self::$thumbnail_size[2];?>" >
					<?php }else{ ?>
						<img class="thumbnail" src="<?= $child_img[0];?>" alt="<?= $post_title;?>" width="<?= self::$thumbnail_size[1];?>" height="<?= self::$thumbnail_size[2];?>" >
					<?php } ?>
				</div>
				<?php
			}/** end loop */
			?>
			</div>
			<?php
			$cache = html_minify(ob_get_contents());
			ob_end_clean();
			theme_cache::set($cache_id,$cache,null,$expire);
			return $cache;
		}
	}
	/**
	 * archive_card_xs
	 *
	 * @return
	 * @version 1.0.0
	 */
	public static function archive_card_xs(array $args = []){
		global $post;
		$args = array_merge([
			'classes' => 'g-desktop-1-2',
			'lazyload' => true,
			'target' => theme_functions::$link_target,
		],$args);

		$args['classes'] .= ' card xs ';
			
		$thumbnail_real_src = theme_functions::get_thumbnail_src($post->ID);
		$post_title = theme_cache::get_the_title($post->ID);
		?>
		<article class="<?= $args['classes'];?>">
			<a 
			class="card-bg" 
			href="<?= theme_cache::get_permalink($post->ID);?>" 
			title="<?= $post_title;?>" 
			target="<?= $args['target'];?>" 
			>
				<div class="thumbnail-container">
					<?php
					/**
					 * lazyload img
					 */
					if($args['lazyload']){
						?>
						<img class="thumbnail" src="<?= theme_functions::$thumbnail_placeholder;?>" data-src="<?= $thumbnail_real_src;?>" alt="<?= $post_title;?>" width="<?= self::$thumbnail_size[1];?>" height="<?= self::$thumbnail_size[2];?>" >
					<?php }else{ ?>
						<img class="thumbnail" src="<?= $thumbnail_real_src;?>" alt="<?= $post_title;?>" width="<?= self::$thumbnail_size[1];?>" height="<?= self::$thumbnail_size[2];?>" >
					<?php } ?>
				</div>
				<h3 class="title"><?= $post_title;?></h3>
			</a>
		</article>
		<?php
	}
	public static function archive_tx_content($args = []){
		global $post;
		$args = array_merge([
			'classes'			=> '',
			'meta_type'			=> 'latest',
		],$args);
		
		$post_title =  theme_cache::get_the_title($post->ID);
		?>
		<li class="<?= $args['classes'];?>">
			<a href="<?= theme_cache::get_permalink($post->ID);?>" title="<?=$post_title;?>">
				<?= $post_title;?>
			</a>
		</li>
		<?php
	}
	
	public static function widget_rank_tx_content($args){
		self::archive_tx_content($args);
	}
	public static function widget_rank_img_content($args = []){
		global $post;
		
		$args = array_merge([
			'classes' => '',
			'lazyload' => true,
			'excerpt' => false,
			'target' => theme_functions::$link_target,
		],$args);

		$thumbnail_real_src = theme_functions::get_thumbnail_src($post->ID);
		$post_title = theme_cache::get_the_title($post->ID);

		?>
		<li class="list-group-item <?= $args['classes'];?>">
			<a 
				class="list-group-item-bg media" 
				href="<?= theme_cache::get_permalink($post->ID);?>" 
				title="<?= $post_title;?>" 
				target="<?= $args['target'];?>" 
			>
				<div class="media-left">
					<div class="thumbnail-container">
						<img 
							class="thumbnail" 
							src="<?= theme_functions::$thumbnail_placeholder;?>" 
							data-src="<?= $thumbnail_real_src;?>" 
							alt="<?= $post_title;?>" 
							width="<?= theme_functions::$thumbnail_size[1];?>" 
							height="<?= theme_functions::$thumbnail_size[2];?>" 
						>
					</div>
				</div>
				<div class="media-body">
					<h3 class="media-heading"><?= $post_title;?></h3>
					<div class="metas row">
						<?php if(class_exists('theme_post_views') && theme_post_views::is_enabled()){ ?>
							<div class="view meta g-phone-1-2">
								<i class="fa fa-play-circle"></i> 
								<?= theme_post_views::get_views();?>
							</div>
						<?php } ?>

						<div class="comments meta g-phone-1-2">
							<i class="fa fa-comment"></i> 
							<?= (int)$post->comment_count;?>
						</div>
					</div>
				</div>			
			</a>
		</li>
		<?php
	}
	public static function page_content($args = []){
		global $post;

		$args = array_merge(array(
			'classes'			=> '',
			'lazyload'			=> true,
			
		),$args);
		
		/** 
		 * classes
		 */
		$args['classes'] .= ' singular-post panel ';

		$author_display_name = theme_cache::get_the_author_meta('display_name',$post->post_author);

		$author_url = theme_cache::get_author_posts_url($post->post_author);
		?>
		<article id="post-<?= $post->ID;?>" <?php post_class($args['classes']);?>>
			<h2 class="entry-title"><?= theme_cache::get_the_title($post->ID);?></h2>
			
			<div class="entry-body">
				
				<!-- post-content -->
				<div class="entry-content content-reset">
					<?php the_content();?>
				</div>

				<?php self::the_page_pagination();?>

				<!-- entry-circle -->
				<div class="entry-circle">
					<a class="meta meta-post-comments" href="<?= $post->comment_count == 0 ? '#respond' : '#comments' ;?>" id="post-comments-btn" title="<?= ___('Comments');?>">
						<div id="post-comments-number-<?= $post_id;?>" class="number">
							<?= (int)$post->comment_count;?>
						</div>
						<div class="tx"><?= ___('Comments');?></div>
					</a>
				</div>
				
				<!-- post-footer -->
				<footer class="entry-footer">
					<?php
					/** 
					 * post-share
					 */
					if(class_exists('theme_post_share') && theme_post_share::is_enabled()){
						?>
						<div class="entry-share">
							<?= theme_post_share::display();?>
						</div>
						<?php
					} /** end post-share */
					?>
				</footer>
			</div><!-- /.entry-body -->
		</article>
		<?php
	}
	/** 
	 * singular_content
	 */
	public static function singular_content(array $args = []){
		global $post;

		$args = array_merge(array(
			'classes'			=> '',
			'lazyload'			=> true,
			
		),$args);
		
		/** 
		 * classes
		 */
		$args['classes'] .= ' singular-post panel ';

		$author_display_name = theme_cache::get_the_author_meta('display_name',$post->post_author);

		$author_url = theme_cache::get_author_posts_url($post->post_author);
		?>
		<article id="post-<?= $post->ID;?>" <?php post_class($args['classes']);?>>
			<h2 class="entry-title"><?= theme_cache::get_the_title($post->ID);?></h2>
			<header class="entry-header">
				<!-- category -->
				<?php
				$cats = get_the_category_list('<i class="split"> / </i> ');
				if(!empty($cats)){
					?>
					<span class="entry-meta post-category" title="<?= ___('Category');?>">
						<i class="fa fa-folder-open"></i>
						<?= $cats;?>
					</span>
				<?php } ?>
				
				<!-- time -->
				<time class="entry-meta post-time" datetime="<?= get_the_time('Y-m-d H:i:s');?>" title="<?= get_the_time(___('M j, Y'));?>">
					<i class="fa fa-clock-o"></i>
					<?= friendly_date(get_the_time('U'));?>
				</time>
				<!-- author link -->
				<a class="entry-meta post-author" href="<?= $author_url;?>" title="<?= sprintf(___('Views all post by %s'),$author_display_name);?>">
					<i class="fa fa-user"></i> 
					<?= $author_display_name;?>
				</a>
				
				<!-- views -->
				<?php if(class_exists('theme_post_views') && theme_post_views::is_enabled()){ ?>
					<span class="entry-meta post-views" title="<?= ___('Views');?>">
						<i class="fa fa-play-circle"></i>
						<span class="number" id="post-views-number-<?= $post->ID;?>">-</span>
					</span>
				<?php } ?>
				<?php
				/** 
				 * comment
				 */
				$comment_count = (int)get_comments_number() . '';
				?>
				<a href="#comments" class="entry-meta quick-comment comment-count" data-post-id="<?= $post->ID;?>">
					<i class="fa fa-comment"></i>
					<span class="comment-count-number"><?= $comment_count;?></span>
				</a>
				<?php
				/**
				 * edit
				 */
				if(class_exists('theme_custom_edit') &&  $post->post_author == theme_cache::get_current_user_id()){
					?>
					<a class="post-meta edit-post" href="<?= theme_custom_edit::get_edit_post_link($post->ID);?>">
						<i class="fa fa-edit"></i> <?= ___('Edit');?>
					</a>
				<?php } ?>
				
				
			</header>
			<div class="entry-body">
				<?php
				/**
				 * ad
				 */
				if(class_exists('theme_adbox') && !empty(theme_adbox::display_frontend('below-post-title'))){
					?>
					<div class="ad-container ad-below-post-title"><?= theme_adbox::display_frontend('below-post-title');?></div>
					<?php
				}
				?>
				<!-- entry-excerpt -->
				<?php 
				$excerpt = $post->post_excerpt;
				if($excerpt !== ''){ 
					?>
					<blockquote class="entry-excerpt">
						<?= $excerpt;?>
					</blockquote>
				<?php } ?>
				<!-- post-content -->
				<div class="entry-content content-reset">
					<?php the_content();?>
				</div>

				<?php self::the_page_pagination();?>

				<!-- entry-circle -->
				<div class="entry-circle">
					<?php
					/** post points */
					/*if(class_exists('custom_post_point') && class_exists('theme_custom_point')){
						custom_post_point::post_btn($post->ID);
					}*/
					
					/** theme_custom_storage */
					if(class_exists('theme_custom_storage') && theme_custom_storage::is_enabled()){
						theme_custom_storage::display_frontend($post->ID);
					}

					/** theme_custom_download_demourl henson add */
					if(class_exists('theme_custom_download_demourl') && theme_custom_download_demourl::is_enabled() && class_exists('theme_custom_storage') && theme_custom_storage::is_enabled()){
						$storage_meta = theme_custom_storage::get_post_meta($post->ID);
						if($storage_meta){
							theme_custom_download_demourl::download_demourl_display($post->ID);
						}
					}

					/** theme_custom_download_point henson add */
					if(class_exists('theme_custom_download_point') && theme_custom_download_point::is_enabled() && class_exists('theme_custom_storage') && theme_custom_storage::is_enabled()){
						$storage_meta = theme_custom_storage::get_post_meta($post->ID);
						if($storage_meta){
							theme_custom_download_point::download_point_display($post->ID);
						}
					}
					
					?>
					<a class="meta meta-post-comments" style="background:#f9bb43;" href="<?= $post->comment_count == 0 ? '#respond' : '#comments' ;?>" id="post-comments-btn" title="<?= ___('Comments');?>">
						<span class="tx"><?= __x('Comments','Tucao');?></span>
						<span id="post-comments-number-<?= $post_id;?>" class="number">
							<?= '(' . (int)$post->comment_count . ')';?>
						</span>
					</a>
				</div>
				
				<!-- theme_custom_post_source -->
				<?php if(class_exists('theme_custom_post_source') && theme_custom_post_source::is_enabled()){?>
					<ul class="entry-source">
						<li>本站所有资源从网络收集整理，仅供学习和研究使用。如有侵犯您的版权，请来信（邮箱:249411513@qq.com）指出，本站将立即改正。</li>
						<?php theme_custom_post_source::display_frontend($post->ID);?>
					</ul>
					<?php } ?>
					
				<!-- post-footer -->
				<footer class="entry-footer">
					<?php
					/** 
					 * tags
					 */
					$tags = get_the_tags();
					if(!empty($tags)){
						?>
						<div class="entry-tags">
							<?php the_tags('',''); ?>
						</div>
						<?php
					}
					?>
					<?php
					/** 
					 * post-share
					 */
					if(class_exists('theme_post_share') && theme_post_share::is_enabled()){
						?>
						<div class="entry-share">
							<?= theme_post_share::display();?>
						</div>
						<?php
					} /** end post-share */
					?>

					<?php
					/**
					 * report
					 */
					if(class_exists('theme_custom_report') && theme_custom_report::is_enabled()){
						?>
						<div class="entry-report">
							<?= theme_custom_report::display_frontend();?>
						</div>
						<?php
					}
					?>
				</footer>
			</div><!-- /.entry-body -->
			
		</article>
		<?php
	}
	/**
	 * get_thumbnail_src
	 *
	 * @return 
	 * @version 1.1.0
	 */
	public static function get_thumbnail_src($post_id,$size = 'thumbnail',$placeholder = null){
		
		if(!$placeholder)
			$placeholder = self::$thumbnail_placeholder;
			
		if(!$size)
			$size = self::$thumbnail_size[0];

		$src = null;
		
		if(has_post_thumbnail($post_id)){
			$src = wp_get_attachment_image_src(get_post_thumbnail_id($post_id),$size)[0];
		}

		/** get img src from post content */
		if(!$src){
			$post = theme_cache::get_post($post_id);
			$src = $post ? get_img_source($post->post_content) : false;
			unset($post);
		}

		if(!$src)
			$src = $placeholder;
		
		return esc_url($src);
	}
   /**
	 * get_crumb
	 * 
	 * 
	 * @return string The html code
	 * @version 2.0.7
	 * 
	 */
	public static function get_crumb(array $args = []){
		$args = array_merge([
			'header' => null,
			'footer' => null,
		],$args);
		
		$links = [];
		
		if(theme_cache::is_home())
			return null;
		
		$links['home'] = '<a href="' . theme_cache::home_url() . '" class="home" title="' . ___('Back to Homepage') . '">
			<i class="fa fa-home fa-fw"></i>
			<span class="tx">' . ___('Back to Homepage') . '</span>
		</a>';
		
		$split = '<span class="split"><i class="fa fa-angle-right"></i></span>';
		
		/* category */
		if(theme_cache::is_category()){
			$cat_curr = theme_features::get_current_cat_id();
			if($cat_curr >= 1){
				$links_cat = get_category_parents($cat_curr,true,'%split%');
				$links_cats = explode('%split%',$links_cat);
				array_pop($links_cats);
				$links['category'] = implode($split,$links_cats);
				$links['curr_text'] = ___('Category Browser');
			}
		/* tag */
		}else if(theme_cache::is_tag()){
			$tag_id = theme_features::get_current_tag_id();
			$tag_obj = get_tag($tag_id);
			$links['tag'] = '<a href="'. esc_url(get_tag_link($tag_id)).'">' . esc_html(theme_features::get_current_tag_name()).'</a>';
			$links['curr_text'] = ___('Tags Browser');
			/* date */
		}else if(theme_cache::is_date()){
			global $wp_query;
			$day = $wp_query->query_vars['day'];
			$month = $wp_query->query_vars['monthnum'];
			$year = $wp_query->query_vars['year'];
			/* day */
			if(theme_cache::is_day()){
				$date_link = get_day_link(null,null,$day);
			/* month */
			}else if(theme_cache::is_month()){
				$date_link = get_month_link($year,$month);
			/* year */
			}else if(theme_cache::is_year()){
				$date_link = get_year_link($year);
			}
			$links['date'] = '<a href="'.$date_link.'">' . theme_cache::wp_title('',false).'</a>';
			$links['curr_text'] = ___('Date Browser');
		/* search*/
		}else if(theme_cache::is_search()){
			// $nav_link = null;
			$links['curr_text'] = sprintf(___('Search Result: %s'),esc_html(get_search_query()));
		/* author */
		}else if(theme_cache::is_author()){
			global $author;
			$user = get_user_by('id',$author);
			$links['author'] = '<a href="'.theme_cache::get_author_posts_url($author).'">' . theme_cache::get_the_author_meta('display_name',$user->ID) . '</a>';
			$links['curr_text'] = ___('Author posts');
		/* archive */
		}else if(theme_cache::is_archive()){
			$links['archive'] = '<a href="'.get_current_url().'">' . theme_cache::wp_title('',false) . '</a>';
			$links['curr_text'] = ___('Archive Browser');
		/* Singular */
		}else if(theme_cache::is_singular()){
			global $post;
			/* The page parent */
			if($post->post_parent){
				$links['singular'] = '<a href="' . theme_cache::get_permalink($post->post_parent) . '">' . theme_cache::get_the_title($post->post_parent) . '</a>';
			}
			/**
			 * post / page
			 */
			if(theme_features::get_current_cat_id() > 1){
				$categories = get_the_category($post->ID);
				foreach ($categories as $key => $row) {
					$parent_id[$key] = $row->category_parent;
				}
				array_multisort($parent_id, SORT_ASC,$categories);
				foreach($categories as $cat){
					$cat_name = esc_html($cat->name);
					$links['singular'] = '<a href="' . esc_url(get_category_link($cat->cat_ID)) . '" title="' . sprintf(___('View all posts in %s'),$cat_name) . '">' . $cat_name . '</a>';
				}
			}
			//$links['curr_text'] = esc_html(theme_cache::get_the_title($post->ID));
		/* 404 */
		}else if(theme_cache::is_404()){
			// $nav_link = null;
			$links['curr_text'] = ___('Not found');
		}
	
	return '<div class="crumb-container">
		' . $args['header'] . '
		<nav class="crumb">
			' . implode($split,apply_filters('crumb_links',$links)) . '
		</nav>
		' . $args['footer'] . '
	</div>';
	}
	/**
	 * get_post_pagination
	 * show pagination in archive or searching page
	 * 
	 * @param string The class of molude
	 * @return string
	 * @version 1.0.1
	 * 
	 */
	public static function get_post_pagination( $class = 'posts-pagination') {
		global $wp_query,$paged;
		if ( $wp_query->max_num_pages > 1 ){
			$big = 9999999;
			$args = array(
				'base'			=> str_replace( $big, '%#%', get_pagenum_link( $big ) ),
				'echo'			=> false, 
				'current' 		=> max( 1, get_query_var('paged') ),
				'prev_text'		=> ___('&laquo;'),
				'next_text'		=> ___('&raquo;'),
				'total'			=> $wp_query->max_num_pages,
			);
			$posts_page_links = paginate_links($args);
			
			$output = '<nav class="'.$class.'">'.$posts_page_links.'</nav>';
			return $output;
		}
	}
	public static function pagination( array $args = [] ) {
		$args = array_merge([
			'custom_query'		=> false,
			'previous_string' 	=> '<i class="fa fa-arrow-left"></i>',
			'next_string'	 	=> '<i class="fa fa-arrow-right"></i>',
			'before_output'   	=> '<div class="pager" aria-label="' . ___('Posts pagination navigation') . '">',
			'after_output'		=> '</div>'
		],$args);

		$rand_id = rand(1000,9999);
		
		if ( !$args['custom_query'] )
			$args['custom_query'] = @$GLOBALS['wp_query'];
			
		$count = (int) $args['custom_query']->max_num_pages;
		$page  = intval( get_query_var( 'paged' ) );
	
		if ( $count <= 1 )
			return false;
		
		if ( !$page )
			$page = 1;
	   
		/**
		 * output before_output;
		 */
		echo $args['before_output'];
		
		/**
		 * prev page
		 */
		if ( $page > 1 ){
			$previous = intval($page) - 1;
			$previous_url = get_pagenum_link($previous);
			
		   echo '<a class="prev" href="' . esc_url($previous_url) . '" title="' . ___( 'Previous page') . '">' . $args['previous_string'] . '</a>';
		}
		/**
		 * middle
		 */
		if ( $count > 1 ) {
			?>
			<label for="pagination-<?= $rand_id;?>" class="middle">
				<select id="pagination-<?= $rand_id;?>" class="form-control">
					<?php
					/**
					 * Previous 5 page
					 */
					for( $i = $page - 3; $i < $page; ++$i ){
						if($i < 1 )
							continue;
						?>
						<option value="<?= esc_url(get_pagenum_link($i));?>">
							<?= sprintf(___('Page %d'),$i);?>
						</option>
						<?php
					}
					?>
					<option selected value="<?= esc_url( get_pagenum_link($page) );?>">
						<?= sprintf(___('Page %d'),$page);?>
					</option>
					<?php
					for( $i = $page + 1; $i < $page + 4; ++$i ) {
						if($i > $count)
							break;
						?>
						<option value="<?= esc_url(get_pagenum_link($i));?>">
							<?= sprintf(___('Page %d'),$i);?>
						</option>
						<?php
					}
					?>
				</select>
			</label>
			<?php
		}
		
		/**
		 * next page
		 */
		if ($page < $count ){
			$next = intval($page) + 1;
	   		$next_url = get_pagenum_link($next);
			echo '<a class="next" href="' . esc_url($next_url) . '" title="' . __( 'Next page') . '">' . $args['next_string'] . '</a>';
		}

		/**
		 * output
		 */
		echo $args['after_output'];

	}
	/**
	 * get the comment pagenavi
	 * 
	 * 
	 * @param string $class Class name
	 * @param bool $below The position where show.
	 * @return string
	 * @version 1.0.0
	 * 
	 */
	public static function get_comment_pagination(array $args = []) {
		global $post;
		/**
		 * post comment status
		 */
		static $page_comments = null,
			$cpp = null,
			$thread_comments = null,
			$max_pages = null;
			
		if($page_comments === null)
			$page_comments = theme_cache::get_option('page_comments');
		/** if comment is closed, return */
		if(!$page_comments) 
			return false;

		/**
		 * comments per page
		 */
		if(!$cpp === null)
			$cpp = theme_cache::get_option('comments_per_page');

		/**
		 * thread_comments
		 */
		if($thread_comments === null)
			$thread_comments = get_option('thread_comments');

		if($max_pages === null)
			$max_pages = get_comment_pages_count(null,get_option('comments_per_page'),theme_cache::get_option('thread_comments'));
			
		/** 
		 * defaults args
		 */
		$defaults = [
			'classes'			=> 'comment-pagination',
			'cpaged'			=> max(1,get_query_var('cpage')),
			'cpp' 				=> $cpp,
			'thread_comments'	=> $thread_comments ? true : false,
			// 'default_comments_page' => get_option('default_comments_page'),
			'default_comments_page' => 'oldest',
			'max_pages' 		=> $max_pages,
			
		];
		$r = array_merge($defaults,$args);
		extract($r,EXTR_SKIP);
				
		/** If has page to show me */
		if ( $max_pages > 1 ){
			$big = 999;
			$args = array(
				'base' 			=> str_replace($big,'%#%',get_comments_pagenum_link($big)), 
				'total'			=> $max_pages,
				'current'		=> $cpaged,
				'echo'			=> false, 
				'prev_text'		=> '<i class="fa fa-angle-left"></i>',
				'next_text'   	=> '<i class="fa fa-angle-right"></i>',
			);
			$comments_page_links = paginate_links($args);
			/**
			 * add data-* attribute
			 */
			$comments_page_links = str_replace(
				' href=',
				' data-post-id="' . $post->ID . '" data-cpage="' . $cpaged . '" href=',
				$comments_page_links
			);
			
			return '<div class="'. $classes .'">'.$comments_page_links.'</div>';
		}
	}
	
	/** 
	 * the_post_0
	 */ 
	public static function the_post_0(){
		global $post;
		?>
		<div id="post-0"class="post no-results not-found mod">
			<?= status_tip('info','large',___( 'Sorry, I was not able to find what you need, what about look at other content :)')); ?>
		</div><!-- #post-0 -->

	<?php
	}

	/** 
	 * smart_page_pagination
	 */
	public static function smart_page_pagination($args = []){
			
		global $post, $page, $numpages;

		$output = [];
	
		$args = array_merge([
			'add_fragment' => 'post-' . $post->ID,
			'same_category' => false,
		],$args);
		
		$output['numpages'] = $numpages;
		$output['page'] = $page;
		
		/** rand posts */
		$get_rand_post = function(){
			$query = theme_functions::get_posts_query([
				'posts_per_page' => 1,
				'ignore_sticky_posts' => true,
				'post_type' => 'post',
				'orderby' => 'rand',
			]);
			return $query->have_posts() ? $query->posts[0] : false;
		};
		/** 
		 * prev post
		 */
		$prev_post = get_previous_post(true);
		
		if(!$prev_post && $args['same_category'] === false)
			$prev_post = get_previous_post();
			
		/** random */
		if(!$prev_post){
			$prev_post = $get_rand_post();
			$output['rand'] = 'prev-post';
		}
			
		if($prev_post){
			$output['prev_post'] = $prev_post;
		}
		/** 
		 * next post
		 */
		$next_post = get_next_post(true);

		if(!$next_post && $args['same_category'] === false)
			$next_post = get_next_post();
		/** random */
		if(!$next_post){
			$next_post = $get_rand_post();
			$output['rand'] = 'next-post';
		}
			
		if($next_post){
			$output['next_post'] = $next_post;
		}		
		/** 
		 * exists multiple page
		 */
		if($numpages != 1){
			/** 
			 * if has prev page
			 */
			if($page > 1){
				$prev_page_number = $page - 1;
				$output['prev_page']['url'] = theme_features::get_link_page_url($prev_page_number,$args['add_fragment']);
				$output['prev_page']['number'] = $prev_page_number;
			}
			/** 
			 * if has next page
			 */
			if($page < $numpages){
				$next_page_number = $page + 1;
				$output['next_page']['url'] = theme_features::get_link_page_url($next_page_number,$args['add_fragment']);
				$output['next_page']['number'] = $next_page_number;
			}
		}
		//var_dump($output);
		return array_filter($output);
	}

	
	public static function the_page_pagination(){
		global $post,$page,$numpages;
		$cache_id = $post->ID . $page . $numpages;
		$cache_group = 'page-pagi';

		$cache = theme_cache::get($cache_id,$cache_group);
		if(!empty($cache)){
			echo $cache;
			unset($cache);
			return;
		}
		$page_pagination = self::smart_page_pagination([
			'same_category' => true,
		]);
		
		if(!isset($page_pagination['numpages']) || $page_pagination['numpages'] <= 1)
			return false;
			
		ob_start();
		?>
		<nav class="page-pagination">
			<?php
			$page_attr_str = $page_pagination['page'] . '/' . $page_pagination['numpages'];
			$page_str = '<span class="current-page">' . $page_pagination['page'] . '</span>' . '/' . $page_pagination['numpages'];
			if(isset($page_pagination['prev_page'])){
				?>
				<a 
					href="<?= $page_pagination['prev_page']['url'];?>" 
					class="prev" 
					title="<?= ___('Previous page');?> <?= $page_attr_str;?>" 
					data-number="<?= $page - 1;?>" 
				><i class="fa fa-chevron-left"></i><span class="tx"><?= ___('Previous page');?> <?= $page_str;?></span></a>
				<?php
			}else{
				?>
				<a 
					href="javascript:;" 
					class="prev" 
					title="<?= ___('Previous page');?> <?= $page_attr_str;?>" 
					data-number="1" 
				><i class="fa fa-chevron-left"></i><span class="tx"><?= ___('Previous page');?> <?= $page_str;?></span></a>
				<?php
			}
			if(isset($page_pagination['next_page'])){
				?>
				<a 
					href="<?= $page_pagination['next_page']['url'];?>" 
					class="next" 
					title="<?= ___('Next page');?> <?= $page_attr_str;?>" 
				><span class="tx"><?= $page_str;?> <?= ___('Next page');?></span><i class="fa fa-chevron-right"></i></a>
				<?php
			}else{
				?>
				<a 
					href="javascript:;" 
					class="next" 
					title="<?= ___('Next page');?> <?= $page_attr_str;?>" 
				><span class="tx"><?= $page_str;?> <?= ___('Next page');?></span><i class="fa fa-chevron-right"></i></a>
				<?php
			}
		?>
		</nav>
		<?php
		$cache = html_minify(ob_get_contents());
		ob_end_clean();

		theme_cache::set($cache_id,$cache,$cache_group,3600);
		echo $cache;	
		unset($cache);
		
	}
	public static function adjacent_posts(){
		global $post,$page;
		$cache_id = $post->ID . $page;
		$cache_group = 'post-pagi';

		$cache = theme_cache::get($cache_id,$cache_group);
		if(!empty($cache)){
			echo $cache;
			unset($cache);
			return;
		}
			
		$prev_next_pagination = self::smart_page_pagination([
			'same_category' => true,
		]);
		
		$has_prev = isset($prev_next_pagination['next_post']) ? 'has-prev' : 'no-prev';

		$has_next = isset($prev_next_pagination['prev_post']) ? 'has-next' : 'no-next';
		
		$prev_url = null;
		$next_url = null;
		
		ob_start();
		?>
		<nav class="adjacent-posts <?= $has_prev;?> <?= $has_next;?> row">
		<div class="g-desktop-1-2">
			<?php
			/**
			 * prev
			 */
			if(isset($prev_next_pagination['next_post'])){
				$prev_url = theme_cache::get_permalink($prev_next_pagination['next_post']->ID);
				$prev_title = theme_cache::get_the_title($prev_next_pagination['next_post']->ID);
				$title = isset($prev_next_pagination['rand']) && $prev_next_pagination['rand'] === 'next-post' ? ___('Random: %s') : ___('Previous post: %s');
				?>
				<a href="<?= $prev_url;?>#post-<?= $prev_next_pagination['next_post']->ID;?>" class="left next-post" title="<?= $prev_title;?>">
					<img class="thumbnail" src="<?= self::$thumbnail_placeholder;?>" data-src="<?= self::get_thumbnail_src($prev_next_pagination['next_post']->ID);?>" alt="<?= $prev_title ;?>" width="<?= self::$thumbnail_size[1];?>" height="<?= self::$thumbnail_size[2];?>">
					<h2 class="title"><i class="fa fa-arrow-circle-left"></i> <?= sprintf($title,$prev_title);?></h2>
				</a>
				<?php
			}
			?>
			</div>
			<div class="g-desktop-1-2">
			<?php
			/**
			 * next
			 */
			if(isset($prev_next_pagination['prev_post'])){
				$next_url = theme_cache::get_permalink($prev_next_pagination['prev_post']->ID);
				$next_title = theme_cache::get_the_title($prev_next_pagination['prev_post']->ID);
				$title = isset($prev_next_pagination['rand']) && $prev_next_pagination['rand'] === 'prev-post' ? ___('Random: %s') : ___('Next post: %s');
				?>
				<a href="<?= $next_url;?>#post-<?= $prev_next_pagination['prev_post']->ID;?>" class="right prev-post" title="<?= $next_title;?>">
					<img class="thumbnail" src="<?= self::$thumbnail_placeholder;?>" data-src="<?= self::get_thumbnail_src($prev_next_pagination['prev_post']->ID);?>" alt="<?= $next_title ;?>" width="<?= self::$thumbnail_size[1];?>" height="<?= self::$thumbnail_size[2];?>">
					<h2 class="title"><i class="fa fa-arrow-circle-right"></i> <?= sprintf($title,$next_title);?></h2>
				</a>
				<?php
			}
			?>
		</div>
		</nav>
		<?php
		$cache = html_minify(ob_get_contents());
		ob_end_clean();

		theme_cache::set($cache_id,$cache,$cache_group,3600);
		echo $cache;
		unset($cache);
	}


	public static function theme_comment( $comment, $args, $depth ) {
		global $post;
		
		$GLOBALS['comment'] = $comment;

		switch ( $comment->comment_type ){
			default :
				$classes = ['media'];
				
				if(!empty( $args['has_children'])) 
					$classes[] = 'parent';
					
				if($comment->comment_approved == '0') 
					$classes[] = 'moderation';

				/**
				 * post author checker
				 */
				if($comment->user_id == $post->post_author){
					$is_post_author = true;
					$classes[] = 'is-post-author';
				}else{
					$is_post_author = false;
				}

				/**
				 * check is my comment
				 */
				if($comment->user_id != 0){
					if(theme_cache::get_current_user_id() == $comment->user_id)
						$classes[] = 'is-me';
				}

				/**
				 * author url
				 */
				$author_url = get_comment_author_url();
				if(!empty($author_url) && stripos($author_url,theme_cache::home_url()) === false){
					$author_nofollow = ' rel="external nofollow" ';
				}else{
					$author_nofollow = null;
				}
				?>
<li <?php comment_class($classes);?> id="comment-<?= $comment->comment_ID;?>">
	<div id="comment-body-<?= $comment->comment_ID; ?>" class="comment-body">
	
		<?php if($comment->comment_parent == 0){ ?>
			<div class="media-left">
				<?php if($author_url){ ?>
					<a href="<?= esc_url($author_url);?>" class="avatar-link" target="_blank" <?= $author_nofollow;?> >
						<?= theme_cache::get_avatar($comment,50);?>
					</a>
				<?php }else{
					echo theme_cache::get_avatar($comment,50);
				} ?>
			</div><!-- /.media-left -->
		<?php } ?>
		
		<div class="media-body">

			<div class="comment-content">
				<?php comment_text();?>
				<?php if ($comment->comment_approved == '0'){ ?>
					<div class="comment-awaiting-moderation"><?= status_tip('info',___('Your comment is awaiting moderation.')); ?></div>
				<?php } ?>
			</div>

			<h4 class="media-heading">
				<span class="comment-meta-data author">
					<?php
					if($comment->comment_parent != 0){
						echo theme_cache::get_avatar($comment,50), '&nbsp;';
					}
					comment_author_link();
					?>
				</span>
				<time class="comment-meta-data time" datetime="<?= get_comment_time('c');?>">
					<a href="<?= esc_url(get_comment_link( $comment->comment_ID));?>"><?= friendly_date(get_comment_time('U')); ?></a>
				</time>
				<?php
				if(!theme_cache::is_user_logged_in()){
					/**
					 * if needs register to comment
					 */
					if(theme_cache::get_option('comment_registration')){
						static $reply_link;
						if(!$reply_link)
							$reply_link = '<a rel="nofollow" class="comment-reply-login quick-login-btn" href="' . wp_login_url(theme_cache::get_permalink($comment->comment_post_ID)) . '">' . ___('Reply') . '</a>';
					}else{
						$reply_link = get_comment_reply_link(
							[
								'add_below'		=> 'comment-body', 
								'depth' 		=> $depth,
								'max_depth' 	=> $args['max_depth'],
							],
							$comment,
							$post->ID
						);
					}
				}else{
					$reply_link = get_comment_reply_link(
						[
							'add_below'		=> 'comment-body', 
							'depth' 		=> $depth,
							'max_depth' 	=> $args['max_depth'],
						],
						$comment,
						$post->ID
					);
				}

				$reply_link = preg_replace('/(href=)[^\s]+/','$1"javascript:;"',$reply_link);
				if(!empty($reply_link)){
					?>
					<span class="comment-meta-data comment-reply reply">
						<?= $reply_link;?>
					</span><!-- .reply -->
				<?php } ?>
			</h4>
		</div><!-- /.media-body -->
	</div><!-- /.comment-body -->
		<?php
		}
	}

	public static function the_related_posts(array $args = []){
		global $post;

		$cache_group_id = 'related_posts';
		$cache = theme_cache::get($post->ID,$cache_group_id);
		if($cache){
			echo $cache;
			unset($cache);
			return;
		}
		
		$defaults = [
			'posts_per_page' => 8,
			'orderby' => 'latest',
		];
		$query_args = [
			'post__not_in' => [$post->ID],
		];
		$args = array_merge($defaults,$args);
		
		$content_args = [
			'classes' => ' ',
		];
		
		ob_start();
		?>
		
		<div class="related-posts panel">
			<div class="heading">
				<h3 class="title">
					<i class="fa fa-heart-o"></i> <?= ___('Maybe you will like them');?>
				</h3>
			</div>
			<div class="body">
				<?php
				$same_tag_args = $args;
				$same_tag_query = $query_args;
				/**
				 * if post not tags, get related post from same category
				 */
				if(!get_the_tags()){
					$same_tag_query['category__in'] = array_map(function($term){
						return $term->term_id;
					},get_the_category($post->ID));
					$same_tag_args['orderby'] = 'random';
				}else{
					$same_tag_query['tag__in'] = array_map(function($term){
						return $term->term_id;
					},get_the_tags());
				}
				$query = self::get_posts_query($same_tag_args,$same_tag_query);
				if($query->have_posts()){
					?>
					<div class="row">
						<?php
						foreach($query->posts as $post){
							setup_postdata($post);
							self::archive_card_xs($content_args);
						}
						wp_reset_postdata();
					?>
					</div>
				<?php }else{ ?>
					<div class="page-tip"><?= status_tip('info',___('No data.'));?></div>
				<?php
				}
				//wp_reset_query();
				?>
			</div>

		</div>
		<?php
		$cache = ob_get_contents();
		ob_end_clean();
		theme_cache::set($post->ID,$cache,$cache_group_id,3600);
		echo $cache;
		unset($cache);
	}
	/**
	 * get_page_pagenavi
	 * 
	 * 
	 * @return 
	 * @version 1.0.0
	 * 
	 */
	public static function get_page_pagenavi(){
		// var_dump( theme_features::get_pagination());
		global $page,$numpages;
		$output = null;
		if($numpages < 2) return;
		if($page < $numpages){
			$next_page = $page + 1;
			$output = '<a href="' . theme_features::get_link_page_url($next_page) . '" class="next_page">' . ___('Next page') . '</a>';
		}else{
			$prev_page = $page - 1;
			$output = '<a href="' . theme_features::get_link_page_url($prev_page) . '" class="prev_page">' . ___('Previous page') . '</a>';
		}
		$output = $output ? '<div class="singular_page">' . $output . '</div>' : null;
		$args = array(
			'range' => 3
		);
		$output .= theme_features::get_pagination($args);
		return $output;
	}
	/**
	 * the_recommended
	 */
	public static function the_recommended(){
		if(!class_exists('theme_recommended_post') || !theme_recommended_post::is_enabled())
			return false;

		$cache = theme_recommended_post::get_cache();
		
		if(!empty($cache)){
			echo $cache;
			unset($cache);
			return;
		}
		
		$recomms = theme_recommended_post::get_ids();
		
		if(empty($recomms)){
			?>
			<div class="page-tip"><?= status_tip('info',___('Please set some recommended posts to display.'));?></div>
			<?php
			return false;
		}
		
		global $post;
		$query = new WP_Query([
			'posts_per_page' => theme_recommended_post::get_item('number'),
			'post__in' => $recomms,
			'orderby' => 'ID',
			'ignore_sticky_posts' => true,
		]);
		ob_start();
		if(have_posts()){
			?>
			<div class="home-recomm mod panel">
				<div class="heading">
					<h2 class="title">
						<span class="bg">
						<?php if(class_exists('theme_page_rank')){ ?>
							<a href="<?= theme_page_rank::get_tabs('recommend')['url'];?>">
						<?php } ?>
						<?php if(theme_recommended_post::get_item('icon')){ ?>
							<i class="fa fa-<?= theme_recommended_post::get_item('icon');?>"></i> 
						<?php } ?>
						<?= theme_recommended_post::get_item('title');?>
						<?php if(class_exists('theme_page_rank')){ ?>
							</a>
						<?php } ?>
						</span>
					</h2>
					<?php if(class_exists('theme_page_rank')){ ?>
						<a href="<?= theme_page_rank::get_tabs('recommend')['url'];?>" class="more"><?= ___('more &raquo;');?></a>
					<?php } ?>
				</div>
				<div class="row">
					<?php
					foreach($query->posts as $post){
						setup_postdata($post);
						self::archive_card_lg([
							'classes' => 'g-tablet-1-2 g-tablet-1-4',
							'lazyload' => false,
						]);
					}
					wp_reset_postdata();
					?>
				</div>
			</div>
			<?php
		}
		unset($query,$recomms);
		$cache = ob_get_contents();
		ob_end_clean();
		theme_recommended_post::set_cache($cache);

		echo $cache;
		unset($cache);
	}
	public static function archive_card_lg(array $args = []){
		global $post;
		$args = array_merge([
			'classes' => 'g-tablet-1-4',
			'lazyload' => true,
			'target' => theme_functions::$link_target,
		],$args);

		$args['classes'] .= ' card lg ';
		
		$thumbnail_real_src = theme_functions::get_thumbnail_src($post->ID);
		$permalink = theme_cache::get_permalink($post->ID);
		$post_title = theme_cache::get_the_title($post->ID);
		$author_display_name = theme_cache::get_the_author_meta('display_name',$post->post_author);
		
		?>
		<article class="<?= $args['classes'];?>">
			<div class="card-bg" >
				<a 
					href="<?= $permalink;?>" 
					title="<?= $post_title;?>" 
					class="thumbnail-container" 
					target="<?= $args['target'];?>" 
				>
					<?php
					/**
					 * lazyload img
					 */
					if($args['lazyload']){
						?>
						<img class="thumbnail" src="<?= theme_functions::$thumbnail_placeholder;?>" data-src="<?= $thumbnail_real_src;?>" alt="<?= $post_title;?>" width="<?= self::$thumbnail_size[1];?>" height="<?= self::$thumbnail_size[2];?>" >
					<?php }else{ ?>
						<img class="thumbnail" src="<?= $thumbnail_real_src;?>" alt="<?= $post_title;?>" width="<?= self::$thumbnail_size[1];?>" height="<?= self::$thumbnail_size[2];?>" >
					<?php } ?>

					<?php if(class_exists('theme_colorful_cats')){ ?>
						<div class="card-cat">
							<?php
							/**
							 * cats
							 */
							foreach(get_the_category($post->ID) as $cat){
								$color = theme_colorful_cats::get_cat_color($cat->term_id,true);
								?>
								<span style="background-color:rgba(<?= $color['r'];?>,<?= $color['g'];?>,<?= $color['b'];?>,.8);"><?= $cat->name;?></span>
							<?php } ?>
						</div>
					<?php } ?>

				</a>
				<a 
					href="<?= $permalink;?>" 
					title="<?= $post_title;?>" 
					class="card-title" 
					target="<?= $args['target'];?>" 
				>
					<h3><?= $post_title;?></h3>
				</a>
				<div class="card-meta">
					<a 
						href="<?= theme_cache::get_author_posts_url($post->post_author);?>" 
						class="meta author" 
						title="<?= $author_display_name;?>" 
						target="<?= $args['target'];?>" 
					>
						<img width="32" height="32" src="<?= theme_functions::$avatar_placeholder;?>" data-src="<?= theme_cache::get_avatar_url($post->post_author);?>" alt="<?= $author_display_name;?>" class="avatar"> <span class="tx"><?= $author_display_name;?></span>
					</a>
					<?php
					/**
					 * views
					 */
					if(class_exists('theme_post_views') && theme_post_views::is_enabled()){ ?>
						<span class="meta views" title="<?= ___('Views');?>"><i class="fa fa-play-circle"></i> <?= theme_post_views::get_views($post->ID);?></span>
					<?php } ?>

					<!-- comments count -->
					<span class="meta comments-count" title="<?= ___('Comments');?>">
						<i class="fa fa-comment"></i> <?= (int)$post->comment_count;?>
					</span>
				</div>
			</div>
		</article>
		<?php
	}
	public static function archive_card_sm(array $args = []){
		global $post;
		$args = array_merge([
			'classes' => 'g-tablet-1-4',
			'lazyload' => true,
			'category' => true,
			'target' => theme_functions::$link_target,
		],$args);
		
		$args['classes'] .= ' card sm ';
		
		$thumbnail_real_src = theme_functions::get_thumbnail_src($post->ID);
		$permalink = theme_cache::get_permalink($post->ID);
		$post_title = theme_cache::get_the_title($post->ID);
		$author_display_name = theme_cache::get_the_author_meta('display_name',$post->post_author);
		
		?>
		<article <?php post_class($args['classes']);?>>
			<div class="card-bg" >
				<?php if(class_exists('theme_colorful_cats') && $args['category']){ ?>
					<div class="card-cat">
						<?php
						/**
						 * cats
						 */
						foreach(get_the_category($post->ID) as $cat){
							$color = theme_colorful_cats::get_cat_color($cat->term_id,true);
							?>
							<span style="background-color:rgba(<?= $color['r'];?>,<?= $color['g'];?>,<?= $color['b'];?>,.8);"><?= $cat->name;?></span>
						<?php } ?>
					</div>
				<?php } ?>
				<a 
					href="<?= $permalink;?>" 
					title="<?= $post_title;?>" 
					class="thumbnail-container" 
					target="<?= $args['target'];?>" 
				>
					<?php
					/**
					 * lazyload img
					 */
					if($args['lazyload']){
						?>
						<img 
							class="thumbnail" 
							src="<?= theme_functions::$thumbnail_placeholder;?>" 
							data-src="<?= $thumbnail_real_src;?>" 
							alt="<?= $post_title;?>" 
							width="<?= self::$thumbnail_size[1];?>" 
							height="<?= self::$thumbnail_size[2];?>" 
						>
					<?php }else{ ?>
						<img 
							class="thumbnail" 
							src="<?= $thumbnail_real_src;?>" 
							alt="<?= $post_title;?>" 
							width="<?= self::$thumbnail_size[1];?>" 
							height="<?= self::$thumbnail_size[2];?>" 
						>
					<?php } ?>
				</a>
				
				<a 
					href="<?= $permalink;?>" 
					title="<?= $post_title;?>" 
					class="card-title" 
					target="<?= $args['target'];?>" 
				>
					<h3><?= $post_title;?></h3>
				</a>
				<div class="card-meta">
					<a 
						href="<?= theme_cache::get_author_posts_url($post->post_author);?>" 
						class="meta author" 
						title="<?= $author_display_name;?>" 
						target="<?= $args['target'];?>" 
					>
						<img width="32" height="32" src="<?= theme_functions::$avatar_placeholder;?>" data-src="<?= theme_cache::get_avatar_url($post->post_author);?>" alt="<?= $author_display_name;?>" class="avatar"> <span class="tx"><?= $author_display_name;?></span>
					</a>
					<time class="meta time" datetime="<?= get_the_time('Y-m-d H:i:s',$post->ID);?>" title="<?= get_the_time(___('M j, Y'),$post->ID);?>">
						<?= friendly_date(get_the_time('U',$post->ID));?>
					</time>
				</div>
			</div>
		</article>
		<?php
	}
	public static function the_homebox(array $args = []){
		if(!class_exists('theme_custom_homebox')) 
			return false;
			
		$opt = array_filter((array)theme_custom_homebox::get_options());

		/**
		 * cache
		 */
		$device = wp_is_mobile() ? 'mobile' : 'desktop';
		$sign_status = theme_cache::is_user_logged_in() ? 'login' : 'logout';
		$cache_id = $device . $sign_status;
		$cache = (array)theme_custom_homebox::get_cache();
		if(isset($cache[$cache_id])){
			echo $cache[$cache_id];
			unset($cache);
			return;
		}

		ob_start();
		
		if(empty($opt)){
			?>
			<div class="panel">
				<div class="content">
					<div class="page-tip"><?= status_tip('info',___('Please add some homebox.'));?></div>
				</div>
			</div>
			<?php
			return false;
		}

		global $post;
		static $lazyload_i = 0;
		foreach($opt as $k => $v){
			
			/** display type */
			$display_type = isset($v['display-type']) ? $v['display-type'] : 'all';
			
			/** for login */
			if($display_type === 'login' && !theme_cache::is_user_logged_in())
				continue;
				
			/** for logout */
			if($display_type === 'logout' && theme_cache::is_user_logged_in())
				continue;

				
			if(!isset($v['title']) || trim($v['title']) === '')
				continue;

			$title = esc_html($v['title']);
			
			$link = isset($v['link']) && !empty($v['link']) ? esc_url($v['link']) : false;
			?>
<div id="homebox-<?= $k;?>" class="homebox panel mod">
	<div class="heading">
		<h2 class="title">
			<span class="bg">
				<?php if($link){ ?>
					<a href="<?= $link;?>" title="<?= sprintf(___('More about %s'),$title);?>">
				<?php } ?>
				<?php if(!empty($v['icon'])){ ?>
					<i class="fa fa-<?= $v['icon'];?>"></i> 
				<?php } ?>
				<?= $title;?>
				<?php if($link){ ?>
					</a>
				<?php } ?>
			</span>
		</h2>
		<div class="extra">
			<?php if(!empty($v['keywords'])){ ?>
				<div class="keywords">
					<?php 
					if(wp_is_mobile()){
						$keyword_i = 0;
						foreach(theme_custom_homebox::keywords_to_html($v['keywords']) as $kw){ 
							if($keyword_i == 3)
								break;
							?>
							<a href="<?= esc_url($kw['url']);?>"><?= $kw['name'];?></a>
							<?php 
							++$keyword_i;
						}
					}else{
						foreach(theme_custom_homebox::keywords_to_html($v['keywords']) as $kw){ 
							?>
								<a href="<?= esc_url($kw['url']);?>"><?= $kw['name'];?></a>
							<?php 
						}
					}
					?>
				</div>
			<?php } ?>
		</div>
	</div>
	<div class="row">
		<?php
		$query = new WP_Query([
			'category__in' => isset($v['cats']) ? $v['cats'] : [],
			'posts_per_page' => isset($v['number']) ? (int)$v['number'] : 8,
			'ignore_sticky_posts' => false,
		]);
		if($query->have_posts()){
			$i = 0;
			foreach($query->posts as $post){
				setup_postdata($post);
				self::archive_card_sm([
					'classes' => $i <= 2 ? 'g-tablet-1-3' : 'g-tablet-1-4',
					'lazyload' => wp_is_mobile() && $lazyload_i < 1 ? false : true,
					'category' => false,
				]);
				++$i;
			}
			wp_reset_postdata();
		}else{
			echo status_tip('info',___('No data yet.'));
		}
		unset($query);
		?>
	</div>
	<a href="<?= $link;?>" class="below-more btn btn-block btn-default" target="<?= theme_functions::$link_target;?>"><?= sprintf(___('More about %s'),$title);?> <i class="fa fa-caret-right"></i></a>
	<?php
	/**
	 * ad
	 */
	if(isset($v['ad']) && !empty($v['ad'])){
		?>
		<div class="homebox-ad"><?= stripslashes($v['ad']);?></div>
	<?php } ?>
</div>
			<?php
			++$lazyload_i;
		} /** end foreach */

		$cache[$cache_id] = html_minify(ob_get_contents());
		ob_end_clean();
		
		theme_custom_homebox::set_cache($cache);
		echo $cache[$cache_id];
		unset($cache);
	}
	/**
	 * Theme respond
	 */
	public static function theme_respond(){
		global $post;
		?>
<div id="respond" class="panel">
	<a href="javascript:;" id="cancel-comment-reply-link" class="none" title="<?= ___('Cancel reply');?>">&times;</a>
	<div class="content">
		<div class="page-tip" id="respond-loading-ready">
			<?= status_tip('loading',___('Loading, please wait...'));?>
		</div>
		
		<p id="respond-must-login" class="well hide-on-logged none">
			<?php 
			echo sprintf(
				___('You must be %s to post a comment.'),
				'<a href="' . esc_url(wp_login_url(theme_cache::get_permalink($post->ID))) . '#respond' . '"><strong>' . ___('log-in') . '</strong></a>'
			);
			?>
		</p>
			
		<form 
			id="commentform" 
			action="javascript:;" 
			method="post" 
			class="comment-form media none"
		>
		<div class="media">
			<input type="hidden" name="comment_post_ID" id="comment_post_ID" value="<?= $post->ID;?>">
			<input type="hidden" name="comment_parent" id="comment_parent" value="0">
			
			<div class="media-left hidden-phone">
				<img id="respond-avatar" src="<?= theme_functions::$avatar_placeholder;?>" alt="avatar" class="media-object avatar" width="100" height="100">
			</div>
			<div class="media-body">
				<?php
				/**
				 * for visitor
				 */
				$req = theme_cache::get_option( 'require_name_email' );
				?>
				<!-- author name -->
				<div id="area-respond-visitor" class="row">
					<div class="g-tablet-1-2">
						<div class="form-group">
							<input type="text" 
								class="form-control" 
								name="author" 
								id="comment-form-author" 
								placeholder="<?= ___('Nickname');?><?= $req ? ' * ' : null;?>"
								<?= $req ? ' required ' : null;?>
								title="<?= ___('Whats your nickname?');?>"
							>
						</div><!-- /.form-group -->
					</div><!-- /.g-tablet-1-2 -->
					<!-- author email -->
					<div class="g-tablet-1-2">
						<div class="form-group">
							<input type="email" 
								class="form-control" 
								name="email" 
								id="comment-form-email" 
								placeholder="<?= ___('Email');?><?= $req ? ' * ' : null;?>"
								<?= $req ? ' required ' : null;?>
								title="<?= ___('Whats your Email?');?>"
							>
						</div><!-- /.form-group -->
					</div><!-- /.g-tablet-1-2 -->
				</div><!-- /.row -->				
				<div class="form-group form-group-textarea">
					<textarea 
						name="comment" 
						id="comment-form-comment" 
						class="form-control" 
						rows="3" 
						placeholder="<?= ___('Hi, have something to say?');?>" 
						title="<?= ___('Nothing to say?');?>" 
						required 
					></textarea>
				</div>
				<div class="form-group btn-group-submit">
					<?php
					/**
					 * theme comment emotion pop btn
					 */
					if(class_exists('theme_comment_emotion') && (theme_comment_emotion::is_enabled('kaomoji') || theme_comment_emotion::is_enabled('img'))){
						theme_comment_emotion::display_frontend('pop');
					}
					?>
					<?php
					/**
					 * theme comment emotion
					 */
					if(class_exists('theme_comment_emotion') && (theme_comment_emotion::is_enabled('kaomoji') || theme_comment_emotion::is_enabled('img'))){
						theme_comment_emotion::display_frontend('pop-btn');
					}
					?>
					<button type="submit" class="submit btn btn-success" title="<?= ___('Post comment');?>">
						<i class="fa fa-check"></i> 
						<?= ___('Post comment');?>
					</button>
				</div><!-- .form-group -->
			</div><!-- /.media-body -->
		</div><!-- /.media -->
		</form>
	</div>
</div>
		<?php
	}
	/**
	 * Echo the user list within loop
	 *
	 * @param array $args
	 * @return 
	 * @version 1.0.2
	 */
	public static function the_user_list(array $args){
		$args = array_merge([
			'classes' => 'g-phone-1-3',
			'user_id' => null,
			'extra_title' => '', /** e.g. You have % points */
			'extra' => 'point',
			'target' => theme_functions::$link_target,
		],$args);
		
		/**
		 * extra point value
		 */
		switch($args['extra']){
			/**
			 * user point
			 */
			case 'point':
				if(class_exists('theme_custom_point')){
					$point_value = theme_custom_point::get_point($args['user_id']);
				}
				break;
			/**
			 * user fav be_count
			 */
			case 'fav':
				if(class_exists('custom_post_fav')){
					$point_value = custom_post_fav::get_user_be_fav_count($args['user_id']);
				}
				break;
			/**
			 * user posts count
			 */
			case 'posts':
				if(class_exists('theme_custom_author_profile')){
					$point_value = theme_custom_author_profile::get_count('works',$args['user_id']);
				}else{
					$point_value = count_user_posts($args['user_id']);
				}
				break;
			default:
				$point_value = null;
		}

		if(!empty($args['extra_title']) && $point_value)
			$args['extra_title'] = str_replace('%',$point_value,$args['extra_title']);

		
		$display_name = theme_cache::get_the_author_meta('display_name',$args['user_id']);

		$avatar_url = theme_cache::get_avatar_url($args['user_id']);
		?>
		<div class="user-list <?= $args['classes'];?>">
			<a 
				href="<?= theme_cache::get_author_posts_url($args['user_id'])?>" 
				title="<?= $display_name;?>" 
				target="<?= $args['target'];?>" 
			>
				<div class="avatar-container">
					<img src="<?= theme_functions::$avatar_placeholder;?>" data-src="<?= $avatar_url;?>" alt="<?= $display_name;?>" class="avatar">
				</div>
				<h3 class="author"><?= $display_name;?></h3>
				<?php if($args['extra']){ ?>
					<div class="extra">
						<span class="<?= $args['extra'];?>" title="<?= $args['extra_title'];?>">
							<?= number_format($point_value);?>
						</span>
					</div>
				<?php }/** end args extra */ ?>
			</a>
		</div>
		<?php
	}

	// url translate henson add
	/*function translate_chinese_post_title_to_en_for_slug( $title ) {
	    $translation_render = 'http://fanyi.baidu.com/v2transapi?from=zh&to=en&transtype=realtime&simple_means_flag=3&query='.$title;
	    $wp_http_get = wp_safe_remote_get( $translation_render );
	    if ( empty( $wp_http_get->errors ) ) { 
	        if ( ! empty( $wp_http_get['body'] ) ) {
	            $trans_result = json_decode( $wp_http_get['body'], true );
	            $trans_title = $trans_result['trans_result']['data'][0]['dst'];
	            return $trans_title;
	        }
	    }
	    return $title;
	}*/

}
// add_filter( 'sanitize_title', 'theme_functions::translate_chinese_post_title_to_en_for_slug', 1 );
add_action('after_setup_theme','theme_functions::init');