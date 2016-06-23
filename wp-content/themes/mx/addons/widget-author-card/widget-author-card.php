<?php
/**
 * theme-widget-author
 *
 * @version 1.0.1
 */
class theme_widget_author extends WP_Widget{
	function __construct(){
		$this->alt_option_name = __CLASS__;
		parent::__construct(
			__CLASS__,
			___('Author card <small>(custom)</small>'),
			array(
				'classname' => __CLASS__,
				'description'=> ___('Show the author information.'),
			)
		);
	}
	function widget($args,$instance){
		global $post;
		
		$author_id = $post->post_author;

		echo $args['before_widget'];

		/**
		 * author profile page url
		 */
		if(class_exists('theme_custom_author_profile')){
			$author_url = theme_custom_author_profile::get_tabs('profile',$author_id)['url'];
		}else{
			$author_url = theme_cache::get_author_posts_url($author_id);
		}
		$description = theme_cache::get_the_author_meta('description',$author_id);
		?>
	
		<div id="widget-author-card" class="widget-container content">
			<a href="<?= $author_url;?>" class="author-link" title="<?= ___('Views the author information detail');?>">
				<?= theme_cache::get_avatar($author_id,100);?>
				
				<h3 class="author-card-name">
					<?= theme_cache::get_the_author_meta('display_name',$author_id);?>
				</h3>
				
				<?php if(class_exists('theme_custom_author_profile')){ ?>
					<small class="label label-<?= theme_custom_author_profile::get_roles($author_id)['label'];?>"><?= theme_custom_author_profile::get_roles($author_id)['name'];?></small>
				<?php } ?>
			</a><!-- ./author-link -->
			
			<p class="author-card-description" title="<?= $description;?>" >
				<?php
				if(empty($description)){
					echo ___('The author is lazy, nothing writes here.');
				}else{
					echo $description;
				}
				?>
			</p>
			
			<?php if(class_exists('theme_custom_author_profile')){ ?>
				<div class="author-card-meta-links">
					<!-- works count -->
					<a class="tooltip" href="<?= theme_custom_author_profile::get_tabs('works',$author_id)['url'];?>" title="<?= ___('Views author posts');?>" target="_blank">
						<span class="tx"><i class="fa fa-fw fa-<?= theme_custom_author_profile::get_tabs('works',$author_id)['icon'];?>"></i></span>
						<span class="count"><?= (int)theme_custom_author_profile::get_tabs('works',$author_id)['count'];?></span>
					</a>
					<!-- comments count -->
					<a class="tooltip" href="<?= theme_custom_author_profile::get_tabs('comments',$author_id)['url'];?>" title="<?= ___('Views author comments');?>" target="_blank">
						<span class="tx"><i class="fa fa-fw fa-<?= theme_custom_author_profile::get_tabs('comments',$author_id)['icon'];?>"></i></span>
						<span class="count"><?= (int)theme_custom_author_profile::get_tabs('comments',$author_id)['count'];?></span>
					</a>
					<!-- point -->
					<?php 
						if(class_exists('theme_custom_point_bomb')){ 
						if(class_exists('number_user_nicename')){
							$target_id = number_user_nicename::$prefix_number + $author_id;
						}else{
							$target_id = $author_id;
						}
						?>
						<!-- followers count -->
						<a class="tooltip" href="<?= theme_custom_point_bomb::get_tabs('bomb',$target_id)['url'];?>" rel="nofollow" title="<?= ___('Bomb!');?>" target="_blank">
							<span class="tx"><i class="fa fa-fw fa-bomb"></i></span>
							<span class="count"><?= theme_custom_point::get_point($author_id);?></span>
						</a>
					<?php } ?>
					
					<!-- pm -->
					<?php if(class_exists('theme_custom_pm')){ ?>
						<a target="_blank" class="tooltip" href="<?= theme_custom_pm::get_user_pm_url($author_id);?>" title="<?= ___('Send a private message.');?>">
							<span class="tx"><i class="fa fa-<?= theme_custom_pm::get_tabs('pm')['icon'];?>"></i></span><span class="count"><?= __x('P.M.','Widget author card PM.');?></span>
						</a>
					<?php } ?>
				</div>
			<?php } ?>
		</div>
		<?php
		echo $args['after_widget'];
	}

	public static function register_widget(){
		register_widget(__CLASS__);
	}
}
add_action('widgets_init','theme_widget_author::register_widget' );