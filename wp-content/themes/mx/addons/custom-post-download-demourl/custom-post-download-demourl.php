<?php
/**
 * @version 1.0.2 
 * henson add
 */
class theme_custom_download_demourl{
	public static $post_meta_key = array(
		'key' => '_theme_custom_download_demourl'
	);
	public static function init(){
		add_filter('theme_options_save',__CLASS__ . '::options_save');

		if(!self::is_enabled())
			return;
			
		add_action('add_meta_boxes', __CLASS__ . '::meta_box_add');
		add_action('save_post_post', __CLASS__ . '::meta_box_save');
	}
	public static function is_enabled(){
		return self::get_options('enabled') == 1;
	}
	public static function options_default(array $opts = []){
		$opts[__CLASS__] = [
			'enabled' => 1,
			'download_demourl' => ''
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
				___("Download demourl"),
				__CLASS__ . '::meta_box_display',
				$screen,
				'side'
			);
		}
	}
	// add download demourl to postmeta database. henson add.
	public static function meta_box_save($post_id){
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
			return;
		if(!isset($_POST[__CLASS__])) 
			return;

		$new_meta = $_POST[__CLASS__];	//获取表单数据（数组）
		$download_demourl = isset($new_meta['download_demourl']) && is_numeric($new_meta['download_demourl']) ? $new_meta['download_demourl'] : null;

		$old_meta = self::get_post_meta($post_id);

		if($new_meta == $old_meta)
			return;
			
		update_post_meta($post_id,self::$post_meta_key['key'],$new_meta);
		
	}
	public static function meta_box_display($post){
		$meta = self::get_post_meta($post->ID);
		//wp_nonce_field(__CLASS__,__CLASS__ . '-nonce');
		?>
		<div class="<?= __CLASS__;?>">
			<input 
				type="text" 
				name="<?= __CLASS__;?>[download_demourl]"
				id="<?= __CLASS__;?>-download_demourl" 
				class="widefat code" 
				title="演示地址"
				placeholder="演示地址 (包括http://)"
				value="<?= isset($meta['download_demourl']) ? $meta['download_demourl'] : null;?>" 
			>
		</div>			
		<?php
	}
	public static function get_text($type){
		return stripslashes($type);
	}

	public static function download_demourl_display(){
		global $post;
		$meta = self::get_post_meta($post->ID);
		
		if(!isset($meta['download_demourl']))
			return false;
		?>
		<a class="meta meta-post-download-demourl" href="<?php echo self::get_text($meta['download_demourl']); ?>" target="_blank" title="<?= ___('Download demourl');?>">
			<span class="tx">演示</span>
		</a>
		<?php
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_custom_download_demourl::init';
	return $fns;
});