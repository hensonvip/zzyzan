<?php
/*
Feature Name:	Developer mode
Feature URI:	http://www.inn-studio.com
Version:		2.0.1
Description:	启用开发者模式，助于维护人员进行调试，运营网站请禁用此模式
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
class theme_dev_mode{
	private static $data = [];
	public static function init(){
		
		add_filter('theme_options_save',__CLASS__ . '::options_save');

		add_action('dev_settings',__CLASS__ . '::display_backend');
		
		add_action('dev_settings',__CLASS__ . '::display_backend_options_list',90);
		
		if(self::get_options('performance')){
			add_action('after_setup_theme',__CLASS__ . '::mark_start_data',1);
			add_action('wp_footer',__CLASS__ . '::hook_footer_performance',90);
		}
		
		if(self::get_options('queries')){
			add_action('wp_footer',__CLASS__ . '::hook_footer_queries',99);
		}
		
	}
	public static function is_enabled(){
		return self::get_options('enabled');
	}
	public static function get_options($key = null){
		static $caches = [];
		if(!isset($caches[__CLASS__]))
			$caches[__CLASS__] = (array)theme_options::get_options(__CLASS__);
			
		if($key === null){
			return $caches[__CLASS__];
		}else{
			return isset($caches[__CLASS__][$key]) ? $caches[__CLASS__][$key] : false;
		}
	}
	public static function mark_start_data(){
		self::$data = array(
			'start-time' => microtime(),
			'start-query' => get_num_queries(),
			'start-memory' => memory_get_usage(),
		);
	}

	public static function display_backend(){
		$opt = self::get_options();
		$checked = self::is_enabled() ? ' checked ' : null;
		
		$checked_queries = isset($opt['queries']) && $opt['queries'] == 1 ? ' checked ' : null;
		
		$checked_performance = isset($opt['performance']) && $opt['performance'] == 1 ? ' checked ' : null;
		?>
		<fieldset>
			<legend><i class="fa fa-fw fa-terminal"></i> <?= ___('Related Options');?></legend>
			<p class="description"><?= ___('For developers to debug the site and it will affect the user experience if enable, please note.');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="<?= __CLASS__;?>-enabled"><?= ___('Developer mode');?></label>
						</th>
						<td>
							<label for="<?= __CLASS__;?>-enabled"><input id="<?= __CLASS__;?>-enabled" name="<?= __CLASS__;?>[enabled]" type="checkbox" value="1" <?= $checked;?> /> <?= ___('Enabled');?></label>

							<span class="description"><?= ___('If enable, your site will work within development mode, or works within high performance product mode.');?></span>

							<input type="hidden" name="<?= __CLASS__;?>[old-enabled]" value="<?= $checked ? 1 : -1 ;?>">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?= __CLASS__;?>-performance"><?= ___('Display frontend performance');?></label>
						</th>
						<td>
							<label for="<?= __CLASS__;?>-performance"><input id="<?= __CLASS__;?>-performance" name="<?= __CLASS__;?>[performance]" type="checkbox" value="1" <?= $checked_performance;?> /> <?= ___('Enabled');?></label>
							<span class="description"><?= ___('Display theme performance information on frontend from F12 console.');?></span>

						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<label for="<?= __CLASS__;?>-queries"><?= ___('Display database queries detail');?></label>
						</th>
						<td>
							<label for="<?= __CLASS__;?>-queries"><input id="<?= __CLASS__;?>-queries" name="<?= __CLASS__;?>[queries]" type="checkbox" value="1" <?= $checked_queries;?> /> <?= ___('Enabled');?></label>
							<span class="description"><?= ___('Display database queries detail on frontend from F12 console.');?></span>

						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function display_backend_options_list(){
		?>
		<fieldset>
			<legend><i class="fa fa-fw fa-bug"></i> <?= ___('Theme options list');?></legend>
			<textarea class="code widefat" cols="50" rows="50" readonly ><?php esc_textarea(print_r(theme_options::get_options()));?></textarea>
		</fieldset>
		<?php
	}
	/**
	 * save
	 * 
	 * 
	 * @params array
	 * @return array
	 * @version 2.0.0
	 * 
	 */
	public static function options_save(array $options = []){
		if(isset($_POST[__CLASS__])){
			$options[__CLASS__] = $_POST[__CLASS__];
			
			$old_enable = isset($_POST[__CLASS__]['old-enabled']) ? $_POST[__CLASS__]['old-enabled'] : -1;

			if(isset($options[__CLASS__]['old-enabled']))
				unset($options[__CLASS__]['old-enabled']);
		}

		return $options;
	}

	public static function hook_footer_performance(){
		?>
		<?php
		self::$data['end-time'] =  microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
		self::$data['end-query'] = get_num_queries();
		self::$data['end-memory'] = memory_get_usage();
		
		self::$data['theme-time'] = self::$data['end-time'] - self::$data['start-time'];
		self::$data['theme-query'] = self::$data['end-query'] - self::$data['start-query'];
		self::$data['theme-memory'] = self::$data['end-memory'] - self::$data['start-memory'];

		$memory_format = '%08.5f';
		$query_format = '%03.0f';
		$time_format = '%06.3f';

		$lang = [
			'des' => ___('Description'),
			'time' => ___('Time (second)'),
			'query' => ___('Query'),
			'memory' => ___('Memory (MB)'),
		];
		$data = [
			/**
			 * theme
			 */
			[
			$lang['des'] 	=> ___('Theme Performance'),
			$lang['time'] 	=> sprintf($time_format,self::$data['theme-time']),
			$lang['query'] 	=> sprintf($query_format,self::$data['theme-query']),
			$lang['memory'] => sprintf($memory_format,self::$data['theme-memory']/1024/1024),
			],
			/**
			 * basic
			 */
			[
			$lang['des'] 	=> ___('Basic Performance'),
			$lang['time'] 	=> sprintf($time_format,self::$data['start-time']),
			$lang['query'] 	=> sprintf($query_format,self::$data['start-query']),
			$lang['memory'] => sprintf($memory_format,self::$data['start-memory']/1024/1024),
			],
			/**
			 * end
			 */
			[
			$lang['des'] 	=> ___('Final Performance'),
			$lang['time'] 	=> sprintf($time_format,self::$data['end-time']),
			$lang['query'] 	=> sprintf($query_format,self::$data['end-query']),
			$lang['memory'] => sprintf($memory_format,self::$data['end-memory']/1024/1024),
			],
		];
		?>
		<script>
		try{console.table(<?= json_encode($data);?>)}catch(e){}
		</script>
		<?php

	}
	public static function hook_footer_queries(){
		global $wpdb;
		?>
		<script>
		try{console.table(<?= json_encode($wpdb->queries);?>)}catch(e){}
		</script>
		<?php
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_dev_mode::init';
	return $fns;
});