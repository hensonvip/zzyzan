<?php
/**
 * @version 1.0.1
 */
class theme_custom_attachment{

	public static function init(){
		add_filter('the_content', __CLASS__ . '::filter_the_content');
	}
	public static function filter_the_content($content){
		if(!theme_cache::is_attachment())
			return $content;
			
		global $post;

		$post_title = theme_cache::get_the_title($post->post_parent);
		if(!wp_attachment_is_image($post->ID))
			return $content;
			
		$current_img_full = wp_get_attachment_image_src($post->ID,'full');
		$current_img_thumbnail = wp_get_attachment_image_src($post->ID,'thumbnail');
		
		$children = get_children([
			'post_parent' => $post->post_parent,
			'post_status' => 'inherit',
			'post_type' => 'attachment',
			'order' => 'ASC',
		]);
		
		$children = array_values($children);
		
		$count = count($children);
		$child_posts = [];
		$current_post_index = 0;
		
		for($i = 0; $i<$count; ++$i){
			$child_img = wp_get_attachment_image_src($children[$i]->ID,'thumbnail');
			$child_posts[$i] = [
				'id' => $children[$i]->ID,
				'permalink' => theme_cache::get_permalink($children[$i]->ID) . '#main',
				'src' => $child_img[0],
				'w' => $child_img[1],
				'h' => $child_img[2],
			];
			if($children[$i]->ID == $post->ID){
				$current_post_index = $i;
			}
		}
		unset($child_img);

		ob_start();
		?>
		<div class="attachment-slide">
			<div class="attachment-slide-content">
				<?php
				/** if current is last post */
				if($current_post_index == $count - 1){
					$url_next = 'javascript:;';
					$title_next = ___('Already last page');
				}else{
					$url_next = $child_posts[$current_post_index + 1]['permalink'];
					$title_next = ___('Next page');
				}
				?>
				<a href="<?= $url_next;?>" title="<?= $title_next;?>">
					<img src="<?= $current_img_full[0];?>" alt="" width="<?= $current_img_full[1];?>" height="<?= $current_img_full[2];?>">
				</a>
			</div>
			<div class="attachment-slide-thumbnail">
				<?php
				for($i = 0; $i<$count; ++$i){
					$class_active = $post->ID === $child_posts[$i]['id'] ? 'active' : null;
					?>
					<a class="<?= $class_active;?>" href="<?= $child_posts[$i]['permalink'];?>">
						<img src="<?= $child_posts[$i]['src'];?>" alt="<?= $post_title;?>" width="<?= $child_posts[$i]['w'];?>" height="<?= $child_posts[$i]['h'];?>">
					</a>
				<?php } ?>
			</div>
		</div>
		<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_custom_attachment::init';
	return $fns;
});