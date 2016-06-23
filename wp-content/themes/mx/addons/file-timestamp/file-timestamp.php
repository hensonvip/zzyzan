<?php
/**
 * @version 1.0.2
 */
class theme_file_timestamp{
	private static $timestamp;
	public static function init(){		
		add_action('advanced_settings' , __CLASS__ . '::display_backend');
		add_action('wp_ajax_' . __CLASS__, __CLASS__ . '::process');
		add_filter('theme_options_save' , __CLASS__ . '::options_save');
	}
	public static function process(){
		if(!theme_cache::current_user_can('manage_options'))
			die(___('You have not permission.'));

		theme_options::set_options(__CLASS__,$_SERVER['REQUEST_TIME']);

		header('location: ' . theme_options::get_url() . '&' . __CLASS__);
		die;
	}

	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__])){
			$opts[__CLASS__] = $_POST[__CLASS__];
		}
		return $opts;
	}
	public static function get_timestamp(){
		if(!self::$timestamp)
			self::$timestamp = theme_options::get_options(__CLASS__);

		if(!self::$timestamp)
			self::$timestamp = theme_features::get_theme_mtime();
			
		return self::$timestamp;
	}
	public static function set_timestamp($value = null){
		if(!$value)
			$value = $_SERVER['REQUEST_TIME'];

		self::$timestamp = $value;
		theme_options::set_options(__CLASS__,self::$timestamp);
	}
	public static function display_backend(){
		?>
		<fieldset>
			<legend><i class="fa fa-fw fa-clock-o"></i> <?= ___('File timestamp');?></legend>
			<p class="description"><?= ___('All theme js, css and images static files are output with timestamp, you can refresh these files after theme updates or when you want.');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th><?= ___('Control');?></th>
						<td>
							<?php 
							if(isset($_GET[__CLASS__])){ 
								echo status_tip('success',___('The file timestamp has been refresh.'));
							}
							?>
							<a href="<?= esc_url(theme_features::get_process_url([
								'action' => __CLASS__
							]));?>" class="button-primary"><i class="fa fa-refresh"></i> <?= ___('Refresh now');?></a>
							<span class="description"><i class="fa fa-warning"></i> <?= ___('Save your settings before click');?></span>

							<input type="hidden" name="<?= __CLASS__;?>" value="<?= self::get_timestamp();?>">
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_file_timestamp::init';
	return $fns;
});