<?php
namespace sinapicv2;

class plugin_options{
	public static $opts;

	public static function init(){
		if(plugin_features::is_admin()){
			\add_filter('plugin_action_links_' . \plugin_basename(dirname(__DIR__) . '/' . __NAMESPACE__ . '.php'), __CLASS__ . '::plugin_action_links' );
			\add_action('admin_menu', __CLASS__ . '::add_page');
		}
		if(plugin_features::is_ajax()){
			\add_action('wp_ajax_options_save_' . __NAMESPACE__ , __CLASS__ . '::process');
		}
		if(self::is_options_page()){
			\add_filter('admin_footer', __CLASS__ . '::backend_js', 1);
		}
	}
	public static function plugin_action_links($links){
		return array_merge([
			'settings' => '<a href="' . plugin_features::admin_url( 'plugins.php?page=' . self::get_options_page_slug()) . '">' . __( 'Settings', __NAMESPACE__ ) . '</a>'
		],$links);
	}

	public static function backend_js(){
		if(!self::current_user_can('manage_options'))
			return;
		if(!self::is_options_page())
			return;
		$config = [
			'lang' => [
				'M01' => __('Loading, please wait...'),
				'E01' => __('Sorry, server is busy now, can not respond your request, please try again later.'),
			],
		];
		?>
		<script>
		window.PLUGIN_CONFIG_<?= __NAMESPACE__;?> = <?= json_encode(\apply_filters('backend_js_config_' . __NAMESPACE__, (array)$config));?>;
		</script>
		<?php
	}
	public static function add_page(){
		if(!self::current_user_can('manage_options'))
			return false;

		\add_plugins_page(
			sprintf(__('%s settings'), plugin_features::get_plugin_data('Name')),
			sprintf(__('%s settings'), plugin_features::get_plugin_data('Name')), 
			'manage_options', 
			self::get_options_page_slug(),
			__CLASS__ . '::display_backend'
		);
	}
	public static function display_backend(){
		?>
		<div class="wrap">
			<form id="backend-options-fm" method="post" action="<?= plugin_features::get_process_url([
				'action' => 'options_save_' . __NAMESPACE__,
			]);?>">
				
				<div class="backend-tab-loading"><?= plugin_functions::status_tip('loading',__('Loading your settings, please wait...'));?></div>
				
				<div id="backend-tab" class="backend-tab">
					<nav class="tab-header">

						<a href="<?= plugin_features::get_plugin_data('PluginURI');?>" target="_blank" title="<?= __('Visit the official of plugin');?>" class="tab-title">
							<?= plugin_features::get_plugin_data('Name');?>
						</a>
					
						<span class="tab-item" title="<?= __('The plugin common basic settings.');?>">
							<i class="fa fa-cog"></i> 
							<span class="tx"><?= __('Basic settings');?></span>
						</span><!-- basic settings -->
						
						<span class="tab-item" title="<?= __('If you in trouble, maybe this label can help you.');?>">
							<i class="fa fa-question-circle"></i> 
							<span class="tx"><?= __('About &amp; help');?></span>
						</span><!-- about and help -->
						
					</nav>

					
					<div class="tab-body">
						<div class="tab-item">
							<?php \do_action('plguin_base_settings_' . __NAMESPACE__);?>
						</div><!-- BASE SETTINGS -->
					
						<div class="tab-item">
							<?php \do_action('plguin_help_settings_' . __NAMESPACE__);?>
						</div><!-- ABOUT and HELP -->
					</div><!-- tab-content -->
				</div><!-- backend-tab -->
		
				<p>
					<input type="hidden" name="<?= __NAMESPACE__;?>[nonce]" value="<?= \wp_create_nonce(__NAMESPACE__);?>">
					
					<button type="submit" class="button button-primary button-large"><i class="fa fa-check"></i> <?= __('Save all settings');?></button>
					
					<label for="options-restore" class="label-options-restore" title="<?= __('Something error with plugin? Try to restore. Be careful, plugin options will be cleared up!');?>">
						<input id="options-restore" name="<?= __NAMESPACE__;?>[restore]" type="checkbox" value="1"/>
						<?= __('Restore to plugin default options');?> <i class="fa fa-history"></i>
					</label>
				</p>
			</form>
		</div>
		<?php
	}
	public static function process(){
		
		if(!isset($_POST[__NAMESPACE__]['nonce']))
			die();
			
		if(!\wp_verify_nonce($_POST[__NAMESPACE__]['nonce'],__NAMESPACE__))
			die();
		
		self::options_save();
		
		\wp_redirect(\add_query_arg(
			'updated',
			true,
			self::get_url()
		));
		die();
	}
	public static function get_url(){
		static $caches = [];
		if(!isset($caches[__NAMESPACE__]))
			$caches[__NAMESPACE__] = plugin_features::admin_url('plugins.php?page=' . self::get_options_page_slug());

		return $caches[__NAMESPACE__];
	}
	public static function current_user_can($key){
		static $caches = [];
		if(!isset($caches[$key]))
			$caches[$key] = \current_user_can($key);

		return $caches[$key];
	}
	public static function get_options_page_slug(){
		return __NAMESPACE__ . '-core-options';
	}
	public static function is_options_page(){
		return self::current_user_can('manage_options') && plugin_features::is_admin() && isset($_GET['page']) && $_GET['page'] === self::get_options_page_slug();
	}
	public static function get_options_default(){
		return \apply_filters('plugin_options_default_' . __NAMESPACE__,[]);
	}
	public static function get_options($key = null){
		/** Default options hook */
		$defaults = self::get_options_default();
		self::$opts = array_merge(
			$defaults,
			(array)\get_option('plugin_options_' . __NAMESPACE__)
		);
		
		if($key){
			if(isset(self::$opts[$key])){
				if(empty(self::$opts[$key]))
					return isset($defaults[$key]) ? $defaults[$key] : null;
				return self::$opts[$key];
			}
			return isset(self::$opts[$key]) ? self::$opts[$key] : null;
		}else{
			return self::$opts;
		}
	}
	public static function set_options($key, $data){
		self::$opts = self::get_options();		
		self::$opts[$key] = $data;
		\update_option('plugin_options_' . __NAMESPACE__, self::$opts);
		return self::$opts;
	}
	private static function options_save(){
		if(!self::current_user_can('manage_options'))
			return false;

		$opts = \apply_filters('plugin_options_save_' . __NAMESPACE__, self::get_options_default());
		
		/** Reset the options? */
		if(isset($_POST[__NAMESPACE__]['restore'])){
			/** Delete theme options */
			\delete_option('plugin_options_' . __NAMESPACE__);
		}else{
			\update_option('plugin_options_' . __NAMESPACE__, $opts);
		}
	}
	public static function delete_options($key){
		self::$opts = self::get_options();
		if(!isset(self::$opts[$key]))
			return false;
		
		unset(self::$opts[$key]);
		\update_option('plugin_options_' . __NAMESPACE__, self::$opts);
		return self::$opts;
	}
}
plugin_options::init();