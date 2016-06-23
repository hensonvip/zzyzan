<?php
/*
Plugin Name: Gravatar fix
Plugin URI: http://inn-studio.com/gravatar-fix
Description: A simple and easy way to fix your gravatar can not be show in China. Replace by eqoe.cn. 
Author: INN STUDIO
Author URI: http://inn-studio.com
Version: 1.1.2
*/
if(!class_exists('theme_gravatar_fix')){
	class theme_gravatar_fix{
		public static function init(){
			add_filter('get_avatar_url', __CLASS__ . '::get_avatar_url');			
			add_filter('theme_options_save', __CLASS__ . '::options_save');
			
			add_action('base_settings', __CLASS__ . '::display_backend');
			
			add_filter('theme_options_default', __CLASS__ . '::options_default');
		}
		public static function display_backend(){
			?>
			<fieldset>
				<legend><i class="fa fa-fw fa-github-alt"></i> <?= ___('Gravatar fix');?></legend>
				<p class="description"><?= ___('This feature can fix the gravatar image to display in China. Just for Chinese users. If using "Custom default gravatar" please DO NOT enable this feature.');?></p>
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
					</tbody>
				</table>
			</fieldset>
			<?php
		}
		public static function options_save(array $opts = []){
			if(isset($_POST[__CLASS__]))
				$opts[__CLASS__] = $_POST[__CLASS__];
			return $opts;
		}
		public static function options_default(array $opts = []){
			$opts[__CLASS__] = [
				'enabled' => get_locale() === 'zh_CN' ? 1 : -1
			];
			return $opts;
		}
		public static function is_enabled(){
			return self::get_options('enabled') == 1;
		}
		public static function get_options($key = null){
			static $caches = null;
			if($caches === null)
				$caches = theme_options::get_options(__CLASS__);

			if($key)
				return isset($caches[$key]) ? $caches[$key] : false;
			return $caches;
		}
		public static function get_avatar_url($url){
			
			if(!self::is_enabled())
				return $url;
				
			return preg_replace('/[0-9a-z]+\.gravatar\.com\/avatar/i', 'cn.gravatar.com/avatar', $url);
		}
	}
	add_filter('theme_addons',function($fns){
		$fns[] = 'theme_gravatar_fix::init';
		return $fns;
	});
}