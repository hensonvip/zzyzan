<?php
class theme_link_target{

	public static function init(){
		add_filter('theme_options_save', __CLASS__ . '::options_save');
		add_filter('theme_options_default', __CLASS__ . '::options_default');
		add_action('base_settings', __CLASS__ . '::display_backend');
		
		if(theme_functions::$link_target !== self::get_target())
			theme_functions::$link_target = self::get_target();
	}
	public static function options_save(array $opts = []){
		if(!isset($_POST[__CLASS__]))
			return $opts;
		$opts[__CLASS__] = $_POST[__CLASS__];
		return $opts;
	}
	public static function options_default(array $opts = []){
		$opts[__CLASS__] = [
			'target' => '_blank',
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
	public static function get_target(){
		return self::get_options('target');
	}
	public static function display_backend(){
		$opt = (array)self::get_options();
		?>
		<fieldset>
			<legend><i class="fa fa-fw fa-external-link"></i> <?= ___('Global link target settings');?></legend>
			<p class="description"><?= ___('You can settings global link target here.');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th><label for="<?= __CLASS__;?>-target"><?= ___('Global target');?></label></th>
						<td>
							<select name="<?= __CLASS__;?>[target]" id="<?= __CLASS__;?>-target" class="widefat">
								<?php the_option_list('_blank', ___('Open in new window'),self::get_options('target'));?>
								<?php the_option_list('_self', ___('Open in current window'),self::get_options('target'));?>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_link_target::init';
	return $fns;
});