<?php
/**
 * theme recommended post
 *
 * @version 2.1.0
 */
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_recommended_post::init';
	return $fns;
});
class theme_recommended_post{
	
	public static function init(){
		add_action('add_meta_boxes',__CLASS__ . '::add_meta_boxes');
		add_action('page_settings',__CLASS__ . '::display_backend');
		add_filter('theme_options_save',__CLASS__ . '::options_save');
		add_filter('theme_options_default',__CLASS__ . '::options_default');
		
		add_action('save_post',__CLASS__ . '::save_post');
		add_action('delete_post',__CLASS__ . '::delete_post');
	}
	public static function add_meta_boxes(){
		$screens = ['post','page'];

		foreach ( $screens as $screen ) {
			add_meta_box(
				__CLASS__,
				___('Recommended post'),
				__CLASS__ . '::box_display',
				$screen,
				'side'
			);
		}
	}
	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = (array)theme_options::get_options(__CLASS__);
		if($key)
			return isset($caches[$key]) ? $caches[$key] : false;
		return $caches;
	}
	public static function box_display($post){
	
		wp_nonce_field(__CLASS__,__CLASS__ . '-nonce' );

		$checked = in_array($post->ID,self::get_ids()) ? ' checked ' : null;
		$btn_class = $checked ? ' button-primary ' : null;
		?>
		<label for="<?= __CLASS__;?>-set" class="button widefat <?= $btn_class;?>">
			<input type="checkbox" id="<?= __CLASS__;?>-set" name="<?= __CLASS__;?>" value="1" <?= $checked;?> />
			<?= ___('Add to recommended posts');?>
		</label>
		<?php
	}
	public static function delete_post($post_id){
		if ( !theme_cache::current_user_can( 'delete_posts' ) )
			return;
		$opt = self::get_options();
		$recomm_posts = isset($opt['ids']) ? (array)$opt['ids'] : [];
		$k = array_search($post_id,$recomm_posts);
		
		if($k !== false){
			unset($opt['ids'][$k]);
			arsort($opt['ids']);
			$opt['ids'] = array_slice($opt['ids'],0,50);
			theme_options::set_options(__CLASS__,$opt);
			self::clear_cache();
		}
	}
	public static function options_default(array $opts = []){
		$opts[__CLASS__] = [
			'enabled' => 1,
			'number' => 8,
			'icon' => 'star-o',
			'title' => ___('Recommend'),
		];
		return $opts;
	}
	public static function save_post($post_id){
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return false;

		if(!isset($_POST[__CLASS__ . '-nonce']) || !wp_verify_nonce($_POST[__CLASS__ . '-nonce'], __CLASS__)) 
			return false;

		$opt = self::get_options();
		
		if(!isset($opt['ids']))
			$opt['ids'] = [];

		/**
		 * set to recomm
		 */
		if(isset($_POST[__CLASS__])){
			if(!in_array($post_id,$opt['ids'])){
				$opt['ids'][] = $post_id;
				arsort($opt['ids']);
				$opt['ids'] = array_slice($opt['ids'],0,50);
				theme_options::set_options(__CLASS__,$opt);
				self::clear_cache();
			}
		}else{
			$key = array_search($post_id,$opt['ids']);
			if($key !== false){
				unset($opt['ids'][$key]);
				$opt['ids'] = array_slice($opt['ids'],0,50);
				theme_options::set_options(__CLASS__,$opt);
				self::clear_cache();
			}
		}
	}
	public static function get_ids(){
		return array_filter((array)self::get_options('ids'));
	}
	public static function is_enabled(){
		return self::get_options('enabled') == 1;
	}
	public static function clear_cache(){
		theme_cache::delete(__CLASS__);
	}
	public static function set_cache($data){
		theme_cache::set(__CLASS__,$data);
	}
	public static function get_cache(){
		return theme_cache::get(__CLASS__);
	}
	public static function get_item($key){
		return self::get_options($key) ? self::get_options($key) : self::options_default()[__CLASS__][$key];
	}
	public static function display_backend(){
		$recomm_posts = self::get_ids();
		?>
		<fieldset>
			<legend><i class="fa fa-fw fa-thumbs-o-up"></i> <?= ___('Recommended posts');?></legend>
			<p><?= ___('Recommended posts will display on home page if enabled.');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th><label for="<?= __CLASS__;?>-enabled"></label><?= ___('Enable or not?');?></th>
						<td>
							<select name="<?= __CLASS__;?>[enabled]" id="<?= __CLASS__;?>-enabled" class="widefat">
								<?php the_option_list(-1,___('Disable'),self::get_options('enabled'));?>
								<?php the_option_list(1,___('Enable'),self::get_options('enabled'));?>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="<?= __CLASS__;?>-title"></label><?= ___('Box title');?></th>
						<td>
							<input type="text" name="<?= __CLASS__;?>[title]" id="<?= __CLASS__;?>-title" value="<?= self::get_item('title');?>" class="widefat">
						</td>
					</tr>
					<tr>
						<th>
							<label for="<?= __CLASS__;?>-icon"><?= ___('Box icon');?></label>
							<a href="//fortawesome.github.io/Font-Awesome/icons" target="_blank" title="<?= ___('Views all icons');?>">#<?= ___('ALL');?></a>
						</th>
						<td>
							<input 
								type="text" 
								value="<?= self::get_item('icon');?>" 
								list="<?= __CLASS__;?>-icon-datalist" 
								name="<?= __CLASS__;?>[icon]" 
								id="<?= __CLASS__;?>-icon" 
								class="widefat" 
							><?php icon_option_list(__CLASS__ . '-icon-datalist');?>
						</td>
					</tr>
					<tr>
						<th><label for="<?= __CLASS__;?>-number"></label><?= ___('Show posts number');?></th>
						<td>
							<input type="number" name="<?= __CLASS__;?>[number]" id="<?= __CLASS__;?>-number" min="4" step="4" value="<?= self::get_item('number');?>" class="short-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><?= ___('Marked posts');?></th>
						<td>
							<?php
							if(!empty($recomm_posts)){
								global $post;
								$query = new WP_Query([
									'posts_per_page' => -1,
									'post__in' => $recomm_posts,
									'ignore_sticky_posts' => true,
								]);
								if($query->have_posts()){
									foreach($query->posts as $post){
										setup_postdata($post);
										?>
<label for="<?= __CLASS__;?>-<?= $post->ID;?>" class="button">
	<input type="checkbox" id="<?= __CLASS__;?>-<?= $post->ID;?>" name="<?= __CLASS__;?>[ids][]" value="<?= $post->ID;?>" checked >
	#<?= $post->ID;?> <?= theme_cache::get_the_title($post->ID);?> 
	<a href="<?= esc_url(get_edit_post_link($post->ID));?>" target="_blank" title="<?= ___('Open in open window');?>"><i class="fa fa-external-link"></i></a>
</label>
										<?php
									}
									wp_reset_postdata();
								}else{
									echo status_tip('info',___('No any post yet'));
								}
							}else{
								echo status_tip('info',___('No any post yet'));
							}
							?>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__])){
			$opts[__CLASS__] = $_POST[__CLASS__];
			self::clear_cache();
		}
		return $opts;
	}
}