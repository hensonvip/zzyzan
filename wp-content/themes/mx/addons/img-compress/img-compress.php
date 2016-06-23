<?php
/**
 * img compress
 *
 * @version 1.0.2
 */
class theme_img_compress{
	public static function init(){
		add_filter('wp_handle_upload_prefilter', __CLASS__ . '::compress_jpeg_quality', 1, 99 );

		add_filter('theme_options_save', __CLASS__ . '::options_save');
		add_filter('theme_options_default', __CLASS__ . '::options_default');

		add_action('base_settings', __CLASS__ . '::display_backend');
		
		if(theme_cache::current_user_can('manage_options'))
			return;
			
		add_filter('wp_handle_upload_prefilter', __CLASS__ . '::filter_wp_handle_upload_prefilter' );
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__]))
			$opts[__CLASS__] = $_POST[__CLASS__];
		return $opts;
	}
	public static function options_default(array $opts = []){
		$opts[__CLASS__] = [
			'jpeg-quality' => 65,
			'png2jpg' => 1,
		];
		return $opts;
	}
	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = (array)theme_options::get_options(__CLASS__);
			
		if($key)
			return isset($caches[$key]) ? $caches[$key] : false;
		return $caches;
	}
	public static function get_jpg_quality(){
		return self::get_options('jpg-quality') ? (int)self::get_options('jpg-quality') : 65;
	}
	public static function is_png2jpg(){
		return self::get_options('png2jpg') == 1;
	}
	public static function display_backend(){
		?>
		<fieldset>
			<legend><i class="fa fa-fw fa-file-zip-o"></i> <?= ___('Image compress settings');?></legend>
			<p class="description"><?= ___('Global image settings.');?></p>
			<table class="form-table">
				<tr>
					<th><label for="<?= __CLASS__;?>-png2jpg"><?= ___('PNG to JPG format');?></label></th>
					<td>
						<select name="<?= __CLASS__;?>[png2jpg]" id="<?= __CLASS__;?>-png2jpg" class="widefat">
							<?php the_option_list(-1,___('Disable'),self::get_options('png2jpg'));?>
							<?php the_option_list(1,___('Enable'),self::get_options('png2jpg'));?>
						</select>
						<p class="description"><?= ___('It will convent png to jpg image format When user upload image file. This feature always disable if is administrator.');?></p>
					</td>
				</tr>
				<tr>
					<th><label for="<?= __CLASS__;?>-jpg-quality"><?= ___('JPG image compress quality');?></label></th>
					<td>
						<select name="<?= __CLASS__;?>[jpg-quality]" id="<?= __CLASS__;?>-jpg-quality" class="widefat">
							<?php 
							for($i=100;$i>=50;$i-=5){
								the_option_list($i,sprintf(___('Level %d'),$i),self::get_jpg_quality());
							}
							?>
						</select>
						<p class="description"><?= ___('It will compress image When user upload image file.');?></p>
					</td>
				</tr>
			</table>
		</fieldset>
		<?php
	}
	/**
	 * png to jpg
	 *
	 * @param array $file
	 * @return array $file
	 * @version 1.0.0
	 */
	public static function filter_wp_handle_upload_prefilter( $file ){
		if(!self::is_png2jpg())
			return $file;
			
		$file_ext = strtolower(substr(strrchr($file['name'],'.'), 1));
		
		if(!$file_ext || $file_ext !== 'png')
			return $file;
			
		/** rename png to jpg */
		$file['name'] = basename($file['name']) . '.jpg';
		
		$img = @imagecreatefrompng($file['tmp_name']);
		if(!$img)
			return $file;
		
		imagejpeg($img, $file['tmp_name']);
		imagedestroy($img);

	    return $file;
	}
	public static function compress_jpeg_quality($file){
		$file_ext = strtolower(substr(strrchr($file['name'],'.'), 1));
		if(!$file_ext || !($file_ext === 'jpg' || $file_ext === 'jpeg'))
			return $file;
			
		$img = @imagecreatefromjpeg($file['tmp_name']);
		if(!$img)
			return $file;
		
		imagejpeg($img, $file['tmp_name'],self::get_jpg_quality());
		imagedestroy($img);

	    return $file;
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_img_compress::init';
	return $fns;
});