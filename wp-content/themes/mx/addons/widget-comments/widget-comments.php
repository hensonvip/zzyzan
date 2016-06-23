<?php
/*
Feature Name:	widget_comments
Feature URI:	http://www.inn-studio.com
Version:		1.1.0
Description:	widget_comments
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/

add_action('widgets_init','widget_comments::register_widget');
class widget_comments extends WP_Widget{
	public static $iden = 'widget_comments';
	public static $avatar_size = 50;
	function __construct(){
		$this->alt_option_name = self::$iden;
		parent::__construct(
			self::$iden,
			___('Latest comments <small>(custom)</small>'),
			[
				'classname' => self::$iden,
				'description'=> ___('Show the latest comments'),
			]
		);
	}
	private static function get_default_options(){
		return [
			'title' => ___('Latest comments'),
			'number' => 6
		];
	}
	function widget($args,$instance){
		$instance = array_merge(self::get_default_options(),$instance);
		echo $args['before_widget'];
		$comments = get_comments(array(
			'status' => 'approve',
			'number' => isset($instance['number']) ? (int)$instance['number'] : 6,
			'type' => 'comment',
		));
		if(!empty($instance['title'])){
			echo $args['before_title'];
			?>
			<i class="fa fa-comments-o"></i> 
			<?php 
			echo $instance['title'];
			echo $args['after_title'];
		}
		if(!empty($comments)){
			global $comment;
			$comment_bak = $comment;
			
			?>
			<ul class="list-group">
				<?php 
				foreach($comments as $comment){
					/**
					 * cache
					 */
					static $caches = [];
					
					/** author_name */
					if(!isset($caches['author_name'][$comment->comment_author]))
						$caches['author_name'][$comment->comment_author] = esc_html(get_comment_author());
						
					/** avatar placeholder */
					if(!isset($caches['avatar_placeholder']))
						$caches['avatar_placeholder'] = theme_functions::$avatar_placeholder;

					/** comment text */
					$comment_text = str_sub(strip_tags(preg_replace('/<img[^>]+>/i','[' . ___('Image') . ']',get_comment_text($comment->comment_ID))),35);
					?>
<li class="list-group-item">
	<a class="media tooltip top" href="<?= theme_cache::get_permalink($comment->comment_post_ID);?>#comment-<?= $comment->comment_ID;?>" title="<?= theme_cache::get_the_title($comment->comment_post_ID);?>">
		<div class="media-left">
			<img class="avatar media-object" data-src="<?= esc_url(theme_cache::get_avatar_url($comment));?>" src="<?= $caches['avatar_placeholder'];?>" alt="<?= $caches['author_name'][$comment->comment_author];?>" width="<?= self::$avatar_size;?>" height="<?= self::$avatar_size;?>"/>
		</div>
		<div class="media-body">
			<h4 class="media-heading">
				<span class="author"><?= $caches['author_name'][$comment->comment_author];?></span>
				<time datetime="<?= get_comment_time('c');?>">
					<small><?= friendly_date(get_comment_time('U'));?></small>
				</time>
			</h4>
			<div class="text"><?= $comment_text;?></div>
		</div>
	</a>
</li>
				<?php } ?>
			</ul>
			<?php 
			$comment = $comment_bak;
		}else{ ?>
			<div class="content">
				<div class="page-tip"><?= status_tip('info',___('No any comment yet.'));?></div>
			</div>
		<?php
		}
		echo $args['after_widget'];
	}
	function form($instance = []){
		$instance = array_merge(self::get_default_options(),$instance);
		?>
		<p>
			<label for="<?= self::get_field_id('title');?>"><?= ___('Title (optional)');?></label>
			<input 
				id="<?= self::get_field_id('title');?>"
				class="widefat"
				name="<?= self::get_field_name('title');?>" 
				type="text" 
				value="<?= $instance['title'];?>" 
				placeholder="<?= ___('Title (optional)');?>"
			/>
		</p>
		<p>
			<label for="<?= self::get_field_id('number');?>"><?= ___('Number');?></label>
			<input type="number" name="<?= self::get_field_name('number');?>" id="<?= self::get_field_id('number');?>" class="widefat" value="<?= (int)$instance['number'];?>"/>
		</p>
		<?php
	}
	function update($new_instance,$old_instance){
		return array_merge($old_instance,$new_instance);
	}
	public static function register_widget(){
		register_widget(self::$iden);
	}

}
