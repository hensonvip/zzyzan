<?php
/**
 * @version 1.0.1
 */
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_full_width_mode::init';
	return $fns;
});
class theme_full_width_mode{

	public static function init(){
		add_filter('frontend_js_config' , __CLASS__ . '::frontend_js_config');
		add_action('page_settings', __CLASS__ . '::display_backend');
		add_filter('theme_options_save', __CLASS__ . '::options_save');
		add_filter('theme_options_default', __CLASS__ . '::options_default');
	}
	public static function options_default(array $opts = []){
		$opts[__CLASS__] = [
			'enabled' => 1
		];
		return $opts;
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__])){
			$opts[__CLASS__] = $_POST[__CLASS__];
		}else{
			$opts[__CLASS__] = [
				'enabled' => -1,
			];
		}
		return $opts;
	}
	public static function is_enabled(){
		return self::get_options('enabled') == 1;
	}
	public static function get_options($key = null){
		static $cache = null;
		if($cache === null)
			$cache = theme_options::get_options(__CLASS__);
		if($key)
			return isset($cache[$key]) ? $cache[$key] : false;
		return $cache;
	}
	public static function display_backend(){
		?>
		<fieldset>
			<legend><i class="fa fa-fw fa-angle-right"></i> <?= ___('Full width mode setings');?></legend>
			<p class="description"><?= ___('In singular post page, you can enable the full-width-mode button if you want.');?></p>
			<table class="form-table">
			<tbody>
			<tr>
				<th><label for="<?= __CLASS__;?>-enabled"><?= ___('Enable or not?');?></label></th>
				<td>
					<input type="checkbox" name="<?= __CLASS__;?>[enabled]" id="<?= __CLASS__;?>-enabled" value="1" <?= self::is_enabled() ? 'checked' : null;?> > 
					<?= ___('Enable');?>
					<span class="description"><?= ___('The full-width-mode button will display near the main content in singular post page if enable.');?></span>
				</td>
			</tr>
			</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function frontend_js_config(array $config){
		if(!theme_cache::is_singular('post') || !self::is_enabled()) 
			return $config;
		$config[__CLASS__] = [
			'lang' => [
				'M01' => ___('Full width mode'),
			],
		];
		return $config;
	}
	public static function frontend_css(){
		if(!theme_cache::is_singular('post') || !self::is_enabled()) 
			return false;
			
		wp_enqueue_style(
			__CLASS__,
			theme_features::get_theme_addons_css(__DIR__),
			'frontend',
			theme_file_timestamp::get_timestamp()
		);
	}
}