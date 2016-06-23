<?php
class theme_custom_text_mode{
	public static function init(){
		add_filter('template_include', __CLASS__ . '::template_include', 99);
		
		add_filter('theme_options_save', __CLASS__ . '::options_save');

		add_action('page_settings', __CLASS__ . '::display_backend');

		add_filter('body_class', __CLASS__ . '::body_classes');
	}
	public static function in_cat(){
		
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__])){
			$opts[__CLASS__] = $_POST[__CLASS__];
		}
		return $opts;
	}
	public static function is_enabled(){
		return self::get_options('enabled') == 1;
	}
	public static function display_backend(){
		?>
		<fieldset>
			<legend><i class="fa fa-fw fa-newspaper-o"></i> <?= ___('Graphic text mode settings');?></legend>
			<p class="description"><?= ___('Graphic text mode is a different with normal card list mode. It is mixed list with graphic and text.');?></p>
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
					<tr>
						<th><label for="<?= __CLASS__;?>-cat-id"><?= ___('Which category using?');?></label></th>
						<td>
							<?php theme_features::cat_option_list(__CLASS__, 'cat-id');?>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>

		<?php
	}
	public static function options_default(array $opts = []){
		$opts[__CLASS__] = [
			'enabled' => -1,
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
	public static function get_cat_id(){
		return (int)self::get_options('cat-id');
	}
	private static function get_tpl_include($type){
		static $cache = null;
		if($cache === null)
			$cache = locate_template(['tpl/' . $type . '-text.php']);
		return $cache;
	}
	public static function body_classes($classes = []){
		if((theme_cache::is_category() || theme_cache::is_singular('post')) && theme_features::get_cat_root_id() == self::get_cat_id()){
			$classes[] = 'in-text-mode';
		}
		return $classes;
	}
	public static function template_include($template){
		if(!self::is_enabled() || self::get_cat_id() <= 0)
			return $template;
		if(theme_cache::is_category() && theme_features::get_cat_root_id() == self::get_cat_id()){
			return self::get_tpl_include('category');
		}else if(theme_cache::is_singular('post') && theme_features::get_cat_root_id() == self::get_cat_id()){
			return self::get_tpl_include('post');
		}
		return $template;
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_custom_text_mode::init';
	return $fns;
});