<?php
/**
 * @version 1.0.2
 */
class theme_custom_post_source{
	public static $post_meta_key = array(
		'key' => '_theme_custom_post_source'
	);
	public static function init(){
		
		add_action('page_settings',__CLASS__ . '::display_backend');
		add_filter('theme_options_save',__CLASS__ . '::options_save');
		add_filter('theme_options_default',__CLASS__ . '::options_default');

		if(!self::is_enabled())
			return;
			
		add_action('add_meta_boxes', __CLASS__ . '::meta_box_add');
		add_action('save_post_post', __CLASS__ . '::meta_box_save');
	}
	public static function is_enabled(){
		return self::get_options('enabled') == 1;
	}
	public static function options_default(array $opts = []){
		$reprint_prefix = sprintf(
			___('This article is %1$s member %2$s\'s reprint work.'),
			'<a href="%site_url%">%site_name%</a>',
			'<a href="%post_author_url%">%post_author_name%</a>'
		);
		/** START text-original */
		ob_start();
		?>
<li><?= 
	sprintf(
	___('This article is %1$s member %2$s\'s original work.'),
		'<a href="%site_url%">%site_name%</a>',
		'<a href="%post_author_url%">%post_author_name%</a>'
	);
	?></li>
<li><?= 
	sprintf(
		___('Welcome to reprint but must indicate the source url %1$s.'),
		'<a href="%post_url%" target="_blank" rel="nofollow">%post_url%</a>'
	);
	?></li>
	<?php
		$text_original = ob_get_contents();
		ob_end_clean();
		/** END text-original *******************************/

		
		/** START text-reprint *******************************/
		ob_start();
		?>
<li><?= $reprint_prefix;?></li>
<li><?= ___('Source: unknow, author: unknow');?></li>
<?php
		$text_reprint = ob_get_contents();
		ob_end_clean();
		/** END text-reprint *******************************/
		
		/** START text-reprint-author *******************************/
		ob_start();
		?>
<li><?= $reprint_prefix;?></li>
<li><?= sprintf(___('Source: %s, author: unknow'),'%source_author_name%');?></li>
<?php
		$text_reprint_author = ob_get_contents();
		ob_end_clean();
		/** END text-reprint-author *******************************/
		
		/** START text-reprint-author-url *******************************/
		ob_start();
		?>
<li><?= $reprint_prefix;?></li>
<li><?= sprintf(___('Source: %1$s, author: %2$s'),
		'<a href="%source_url%" target="_blank" rel="nofollow">%source_url%</a>',
		'%source_author_name%'
	);?></li>
<?php
		$text_reprint_author_url = ob_get_contents();
		ob_end_clean();
		/** END text-reprint-author-url *******************************/
		
		$opts[__CLASS__] = [
			'enabled' => 1,
			'text-original' => $text_original,
			'text-reprint' => $text_reprint,
			'text-reprint-author' => $text_reprint_author,
			'text-reprint-author-url' => $text_reprint_author_url,
		];
		return $opts;
	}
	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = (array)theme_options::get_options(__CLASS__);
		if($key){
			if(isset($caches[$key])){
				return $caches[$key];
			}else{
				$caches[$key] = isset(self::options_default()[__CLASS__][$key]) ? self::options_default()[__CLASS__][$key] : false;
				return $caches[$key];
			}
		}
		return $caches;
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__])){
			$opts[__CLASS__] = $_POST[__CLASS__];
		}
		return $opts;
	}
	public static function get_types($key = null){
		$types = [
			'original' => [
				'text' => ___('Original')
			],
			'reprint' => [
				'text' => ___('Reprint'),
			]
		];
		if($key)
			return isset($types[$key]) ? $types[$key] : null;
		return $types;
	}
	public static function get_post_meta($post_id = null){
		if(!$post_id){
			global $post;
			$post_id = $post->ID;
		}
		static $caches = [];
		
		if(!isset($caches[$post_id]))
			$caches[$post_id] = array_filter((array)get_post_meta($post_id,self::$post_meta_key['key'],true));
			
		return $caches[$post_id];
	}
	public static function meta_box_add(){
		$screens = array( 'post' );
		foreach ( $screens as $screen ) {
			add_meta_box(
				__CLASS__,
				___('Post source'),
				__CLASS__ . '::meta_box_display',
				$screen,
				'side'
			);
		}
	}
	public static function meta_box_save($post_id){
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
			return;
		if(!isset($_POST[__CLASS__])) 
			return;

		$new_meta = $_POST[__CLASS__];
		$source = isset($new_meta['source']) && is_string($new_meta['source']) ? $new_meta['source'] : null;

		$old_meta = self::get_post_meta($post_id);

		if($new_meta == $old_meta)
			return;
			
		if(!$source || !self::get_types($source))
			return;

		if(isset($new_meta['reprint']['url']))
			$new_meta['reprint']['url'] = esc_url($new_meta['reprint']['url']);
			
		update_post_meta($post_id,self::$post_meta_key['key'],$new_meta);
		
	}
	public static function meta_box_display($post){
		$meta = self::get_post_meta($post->ID);
		//wp_nonce_field(__CLASS__,__CLASS__ . '-nonce');
		?>
		<div class="<?= __CLASS__;?>">
			<select class="widefat" name="<?= __CLASS__;?>[source]" id="<?= __CLASS__;?>-source">
				<?php
				foreach(self::get_types() as $type_id => $type_name){
					the_option_list($type_id,self::get_types($type_id)['text'],$meta['source']);
				}
				?>
			</select>
			<input 
				type="url" 
				name="<?= __CLASS__;?>[reprint][url]" 
				id="<?= __CLASS__;?>-reprint-url" 
				class="widefat code" 
				title="<?= ___('Source URL, for reprint work');?>"
				placeholder="<?= ___('Source URL, for reprint work');?>"
				value="<?= isset($meta['reprint']['url']) ? $meta['reprint']['url'] : null;?>" 
			>
			<input 
				type="text" 
				name="<?= __CLASS__;?>[reprint][author]" 
				id="<?= __CLASS__;?>-reprint-author" 
				class="widefat code" 
				title="<?= ___('Author, for reprint work');?>"
				placeholder="<?= ___('Author, for reprint work');?>"
				value="<?= isset($meta['reprint']['author']) ? esc_attr($meta['reprint']['author']) : null;?>" 
			>
		</div>			
		<?php
	}
	public static function get_text($type){
		return stripslashes(self::get_options($type));
	}
	public static function display_backend(){
		?>
		<fieldset>
			<legend><i class="fa fa-fw fa-truck"></i> <?= ___('Post source settings');?></legend>
			<p class="description"><?= ___('The post source will display below the main content. Here are some keywords to use.');?></p>

			
			<input type="text" class="small-text text-select" value="%site_name%" title="<?= ___('Site name');?>" readonly>
			
			<input type="text" class="small-text text-select" value="%site_url%" title="<?= ___('Site URL');?>" readonly>
			
			<input type="text" class="small-text text-select" value="%post_author_name%" title="<?= ___('Post author name');?>" readonly>
			
			<input type="text" class="small-text text-select" value="%post_author_url%" title="<?= ___('Post author URL');?>" readonly>
			
			<input type="text" class="small-text text-select" value="%post_url%" title="<?= ___('Post URL');?>" readonly>
			
			<input type="text" class="small-text text-select" value="%source_url%" title="<?= ___('Source URL');?>" readonly>
			
			<input type="text" class="small-text text-select" value="%source_author_name%" title="<?= ___('Source author name');?>" readonly>
			
			<table class="form-table">
				<tbody>
				<tr>
					<th><label for="<?= __CLASS__;?>-enabled"><?= ___('Enable or not?');?></label></th>
					<td>
						<select name="<?= __CLASS__;?>[enabled]" id="<?= __CLASS__;?>-enabled" class="widefat">
							<?php the_option_list(-1,___('Disable'),self::get_options('enabled'));?>
							<?php the_option_list(1,___('Enable'),self::get_options('enabled'));?>
						</select>
					</td>
				</tr>
				<!-- original -->
				<tr>
					<th><label for="<?= __CLASS__;?>-text-original"><?= ___('HTML: if the post is original');?></label></th>
					<td>
						<textarea name="<?= __CLASS__;?>[text-original]" id="<?= __CLASS__;?>-text-original" rows="5" class="widefat"><?= self::get_text('text-original');?></textarea>
					</td>
				</tr>
				
				<!-- reprint author and url -->
				<tr>
					<th><label for="<?= __CLASS__;?>-text-reprint-author-url"><?= ___('HTML: if the post is reprint and has original author and URL');?></label></th>
					<td>
						<textarea name="<?= __CLASS__;?>[text-reprint-author-url]" id="<?= __CLASS__;?>-text-reprint-author-url" rows="5" class="widefat"><?= self::get_text('text-reprint-author-url');?></textarea>
					</td>
				</tr>
				
				<!-- reprint author and url -->
				<tr>
					<th><label for="<?= __CLASS__;?>-text-reprint-author"><?= ___('HTML: if the post is reprint and has original author but no URL');?></label></th>
					<td>
						<textarea name="<?= __CLASS__;?>[text-reprint-author]" id="<?= __CLASS__;?>-text-reprint-author" rows="5" class="widefat"><?= self::get_text('text-reprint-author');?></textarea>
					</td>
				</tr>
				
				<!-- reprint no author and no url -->
				<tr>
					<th><label for="<?= __CLASS__;?>-text-reprint"><?= ___('HTML: if the post is reprint but no original and no URL');?></label></th>
					<td>
						<textarea name="<?= __CLASS__;?>[text-reprint]" id="<?= __CLASS__;?>-text-reprint" rows="5" class="widefat"><?= self::get_text('text-reprint');?></textarea>
					</td>
				</tr>
				
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function keywords_convert($content){
		global $post;
		$meta = self::get_post_meta($post->ID);
		$source_url = isset($meta['reprint']['url']) ? esc_url($meta['reprint']['url']) : ___('unknow');
		$source_author_name = isset($meta['reprint']['author']) ? esc_html($meta['reprint']['author']) : ___('unknow');
		 
		return str_replace(
			[
				'%site_name%',
				'%site_url%',
				'%post_author_name%',
				'%post_author_url%',
				'%post_url%',
				'%source_url%',
				'%source_author_name%'
			],[
				theme_cache::get_bloginfo('name'),
				theme_cache::home_url(),
				theme_cache::get_the_author_meta('display_name',$post->post_author),
				theme_cache::get_author_posts_url($post->post_author),
				theme_cache::get_permalink($post->ID),
				$source_url,
				$source_author_name
			],
			$content
		);
	}
	public static function display_frontend(){
		global $post;
		$meta = self::get_post_meta($post->ID);
		
		if(!isset($meta['source']))
			return false;
			
		switch($meta['source']){
			case 'original':
				echo self::keywords_convert(self::get_text('text-original'));
				break;
			case 'reprint':
				/** not author and url */
				if(
					(!isset($meta['reprint']['author']) || empty($meta['reprint']['author']))
					&& 
					(!isset($meta['reprint']['url']) || empty($meta['reprint']['url']))
				){
					echo self::keywords_convert(self::get_text('text-reprint'));
					
				/** only author */
				}else if((isset($meta['reprint']['author']) && !empty($meta['reprint']['author'])) && (!isset($meta['reprint']['url']) || empty($meta['reprint']['url']))){
					echo self::keywords_convert(self::get_text('text-reprint-author'));
					
				/** author and url */
				}else{
					echo self::keywords_convert(self::get_text('text-reprint-author-url'));
				}
				break;
		}
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_custom_post_source::init';
	return $fns;
});