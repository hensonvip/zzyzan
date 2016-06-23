<?php 
/**
 * Theme Options
 * the theme options and show admin control planel
 * 
 * @version 6.0.0
 * 
 */
class theme_options{
	public static $opts = [];
	public static function init(){
		add_action('admin_menu', __CLASS__ . '::add_page');
		add_action('admin_bar_menu', __CLASS__ . '::add_bar',61);
		add_action('wp_ajax_' . __CLASS__ , __CLASS__ . '::process');
		add_action('admin_init', __CLASS__ . '::admin_init' );
	}
	public static function admin_init(){
		if(!self::is_options_page())
			return false;
		add_action('admin_footer', __CLASS__ . '::backend_js',1);
	}
	public static function get_options($key = null){
		static $mod = null;
		if($mod === null)
			$mod = (array)get_theme_mod(__CLASS__);
			
		/** Default options hook */
		self::$opts = array_merge(
			apply_filters('theme_options_default',[]),
			$mod
		);

		if($key)
			return isset(self::$opts[$key]) ? self::$opts[$key] : false;
		return self::$opts;
	}
	public static function process(){
		
		if(!isset($_POST[__CLASS__]['nonce']))
			die;
			
		if(!wp_verify_nonce($_POST[__CLASS__]['nonce'],__CLASS__))
			die;
		
		self::options_save();
		
		wp_redirect(add_query_arg(
			'updated',
			true,
			self::get_url()
		));
		die;
	}
	public static function get_url(){
		static $cache = null;
		if($cache === null)
			$cache = admin_url('themes.php?page=core-options');
		return $cache;
	}
	public static function backend_js(){

		if(!self::is_options_page())
			return;

		$config = [
			'vars' => [
				'locale' => str_replace('-','_',theme_cache::get_bloginfo('language')),
				'theme_js' => theme_features::get_theme_js(),
				'theme_css' => theme_features::get_theme_css(),
				'theme_images' => theme_features::get_theme_images_url(),
				'process_url' => theme_features::get_process_url(),
				'timestamp' => theme_file_timestamp::get_timestamp(),
			],
			'lang' => [
				'M01' => ___('Loading, please wait...'),
				'E01' => ___('Sorry, server is busy now, can not respond your request, please try again later.'),
			],
		];
		/** Hook 'backend_js_config' */
		?>
		<script>
		window.THEME_CONFIG = <?= json_encode(apply_filters('backend_js_config',(array)$config));?>;
		</script>
		<?php
	}
	public static function display_backend(){
		?>
		<div class="wrap">
			<form id="backend-options-fm" method="post" action="<?= theme_features::get_process_url([
				'action' => __CLASS__,
			]);?>">
				
				<div class="backend-tab-loading"><?= status_tip('loading',___('Loading your settings, please wait...'));?></div>
				
				<div id="backend-tab" class="backend-tab">
					<nav class="tab-header">

						<a href="<?= theme_functions::theme_meta_translate()['theme_url'];?>" target="_blank" title="<?= ___('Visit the official of theme');?>" class="tab-title">
							<?= theme_functions::theme_meta_translate()['name'];?>
						</a>
					
						<span class="tab-item" title="<?= ___('The theme common basic settings.');?>">
							<i class="fa fa-fw fa-cog"></i> 
							<span class="tx"><?= ___('Basic settings');?></span>
						</span><!-- basic settings -->
						
						<span class="tab-item" title="<?= ___('You can customize the theme in this label.');?>">
							<i class="fa fa-fw fa-paint-brush"></i> 
							<span class="tx"><?= ___('Page settings');?></span>
						</span><!-- page settings -->
						
						<span class="tab-item" title="<?= ___('If the theme there are some problems, you can try to use these settings.');?>">
							<i class="fa fa-fw fa-cogs"></i> 
							<span class="tx"><?= ___('Advanced settings');?></span>
						</span><!-- advanced settings -->
						
						<span class="tab-item" title="<?= ___('This settings is for developer, if you want to debug code, you can try this.');?>">
							<i class="fa fa-fw fa-code"></i> 
							<span class="tx"><?= ___('Developer settings');?></span>
						</span><!-- developer mode -->
						
						<span class="tab-item" title="<?= ___('If you in trouble, maybe this label can help you.');?>">
							<i class="fa fa-fw fa-question-circle"></i> 
							<span class="tx"><?= ___('About &amp; help');?></span>
						</span><!-- about and help -->
						
					</nav>

					
					<div class="tab-body">
						<div class="tab-item">
							<?php do_action('base_settings');?>
						</div><!-- BASE SETTINGS -->
					
						<div class="tab-item">
							<?php do_action('page_settings');?>
						</div><!-- PAGE SETTINGS -->
					
						<div class="tab-item">
							<?php do_action('advanced_settings');?>
						</div><!-- ADVANCED SETTINGS -->
					
						<div class="tab-item">
							<?php do_action('dev_settings');?>
						</div><!-- DEVELOPER SETTINGS -->
					
						<div class="tab-item">
							<?php do_action('help_settings');?>
						</div><!-- ABOUT and HELP -->
					</div><!-- tab-content -->
				</div><!-- backend-tab -->
		
				<p>
					<input type="hidden" name="<?= __CLASS__;?>[nonce]" value="<?= wp_create_nonce(__CLASS__);?>">
					
					<button id="submit" type="submit" class="button button-primary button-large"><i class="fa fa-check"></i> <span class="tx"><?= ___('Save all settings');?></span></button>
					
					<label for="options-restore" class="label-options-restore" title="<?= ___('Something error with theme? Try to restore. Be careful, theme options will be cleared up!');?>">
						<input id="options-restore" name="<?= __CLASS__;?>[restore]" type="checkbox" value="1"/>
						<?= ___('Restore to theme default options');?> <i class="fa fa-question-circle"></i>
					</label>
				</p>
			</form>
		</div>
		<?php
	}
	private static function options_save(){
		if(!theme_cache::current_user_can('manage_options'))
			return false;

		$opts_new = apply_filters(__CLASS__ . '_save',[]);
		
		/** Reset the options? */
		if(isset($_POST[__CLASS__]['restore'])){
			/** Delete theme options */
			set_theme_mod(__CLASS__,[]);
		}else{
			set_theme_mod(__CLASS__,$opts_new);
		}
	}
	public static function set_options($key,$data){
		self::$opts = self::get_options();		
		self::$opts[$key] = $data;
		set_theme_mod(__CLASS__,self::$opts);
		return self::$opts;
	}
	public static function delete_options($key){
		self::$opts = self::get_options();
		if(!isset(self::$opts[$key]))
			return false;
		
		unset(self::$opts[$key]);
		set_theme_mod(__CLASS__,self::$opts);
		return self::$opts;
	}
	public static function is_options_page(){
		if(!theme_cache::current_user_can('manage_options'))
			return false;
		return theme_cache::is_admin() && isset($_GET['page']) && $_GET['page'] === 'core-options';
	}
	public static function add_page(){
		if(!theme_cache::current_user_can('manage_options'))
			return false;
		/* Add to theme setting menu */
		add_theme_page(
			___('Theme settings'),
			___('Theme settings'), 
			'edit_themes', 
			'core-options',
			__CLASS__ . '::display_backend'
		);
	}
	public static function add_bar(){
		if(!theme_cache::current_user_can('manage_options'))
			return false;
		
		global $wp_admin_bar;
		$wp_admin_bar->add_menu( array(
			'parent' => 'appearance',
			'id' => 'theme_settings',
			'title' => ___('Theme settings'),
			'href' => self::get_url()
		));
	}
}
theme_options::init();