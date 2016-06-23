<?php
/**
 * img placeholder
 *
 * @version 2.2.3
 */
class theme_img_placeholder{
	public static function init(){
		add_filter('theme_options_save', __CLASS__ . '::options_save');
		add_filter('theme_options_default', __CLASS__ . '::options_default');

		add_action('base_settings', __CLASS__ . '::display_backend');

		if(theme_functions::$thumbnail_placeholder !== self::get_options('thumbnail'))
			theme_functions::$thumbnail_placeholder = self::get_options('thumbnail');
			
		if(theme_functions::$avatar_placeholder !== self::get_options('avatar'))
		theme_functions::$avatar_placeholder = self::get_options('avatar');
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__]))
			$opts[__CLASS__] = $_POST[__CLASS__];
		return $opts;
	}
	public static function options_default(array $opts = []){
		$opts[__CLASS__] = [
			'thumbnail' => theme_functions::$thumbnail_placeholder,
			'avatar' => theme_functions::$avatar_placeholder,
		];
		return $opts;
	}
	public static function get_options($key = null){
		static $cache = null;
		if($cache === null)
			$cache = array_filter((array)theme_options::get_options(__CLASS__));
		if($key){
			if(isset($cache[$key]))
				return $cache[$key];
			return isset(self::options_default()[__CLASS__][$key]) ?self::options_default()[__CLASS__][$key] : false;
		}
		return $cache ? $cache : self::options_default()[__CLASS__];
	}
	public static function display_backend(){
		?>
		<fieldset>
			<legend><i class="fa fa-picture-o fa-fw"></i> <?= ___('Image placeholder settings');?></legend>
			<p class="description"><?= ___('You can custom the image placeholder here.');?></p>
			<table class="form-table">
				<tr>
					<th>
						<label for="<?= __CLASS__;?>-thumbnail-url"><?= ___('Thumbnail image URL');?></label>
						<br>
						<a href="<?= self::get_options('thumbnail');?>" target="_blank">
							<img src="<?= self::get_options('thumbnail');?>" alt="thumbnail" width="<?= theme_functions::$thumbnail_size[1];?>" height="<?= theme_functions::$thumbnail_size[2];?>">
						</a>
					</th>
					<td>
						<input class="widefat" type="url" name="<?= __CLASS__;?>[thumbnail]" id="<?= __CLASS__;?>-thumbnail-url" value="<?= self::get_options('thumbnail');?>" placeholder="<?= ___('Your custom thumbnail image URL address');?>">
					</td>
				</tr>
					<tr>
					<th>
						<label for="<?= __CLASS__;?>-avatar-url"><?= ___('Avatar image URL');?></label>
						<br>
						<a href="<?= self::get_options('avatar');?>" target="_blank">
							<img src="<?= self::get_options('avatar');?>" alt="avatar" width="100" height="100">
						</a>
					</th>
					<td>
						<input class="widefat" type="url" name="<?= __CLASS__;?>[avatar]" id="<?= __CLASS__;?>-avatar-url" value="<?= self::get_options('avatar');?>" placeholder="<?= ___('Your custom avatar image URL address');?>">
				</td>
				</tr>
			</table>
		</fieldset>
		<?php
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_img_placeholder::init';
	return $fns;
},5);