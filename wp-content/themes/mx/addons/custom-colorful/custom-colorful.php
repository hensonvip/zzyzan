<?php
/**
 * @version
 */
class theme_custom_colorful{

	public static function init(){

		add_action('page_settings', __CLASS__ . '::display_backend');
		add_filter('theme_options_save', __CLASS__ . '::options_save');
		add_filter('theme_options_default', __CLASS__ . '::options_default');

		add_action('wp_enqueue_scripts', __CLASS__ . '::frontend_css');
		add_action('customize_register', __CLASS__ . '::customize_register');
		add_action('customize_controls_enqueue_scripts', __CLASS__ . '::customizer_live_preview');
		
		if(!theme_options::is_options_page())
			return false;
			
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::backend_css' );
	}
	public static function get_schemes($key = null){
		static $caches = null;
		if($caches === null){
			$caches = [
				[
					'id' => 'default',
					'text' => ___('Default'),
					'colors' => ['#455A64','#607D8B','#FF4081'],
				],[
					'id' => 'sunrise',
					'text' => ___('Sun rise'),
					'colors' => ['#b43c38','#cf4944','#ccaf0b'],
				],[
					'id' => 'coffee',
					'text' => ___('Coffee'),
					'colors' => ['#46403c','#59524c','#c7a589'],
				],[
					'id' => 'palette',
					'text' => ___('Palette'),
					'colors' => ['#413256','#523f6d','#a3b745'],
				],[
					'id' => 'random',
					'text' => ___('Random daily'),
					'colors' => ['#000','#fff','#000'],
				]
			];
		}
		if($key !== null)
			return isset($caches[$key]) ? $caches[$key] : false;
		return $caches;
	}
	public static function customize_register($wp_customize){
		$opt_prefix = theme_options::$iden . '[' . __CLASS__ . ']';
		$choices = [];
		foreach(self::get_schemes() as $v){
			$choices[ $v['id']] = $v['text'];
		}
		$wp_customize->add_section(__CLASS__,[
			'title' 		=> ___('Theme colorful settings'),
			'description' 	=> ___('The theme has some color schemes, you can choose a color scheme you like. Also ,you can try to choose random scheme.'),
			'priority' 		=> 20,
		]);
		$wp_customize->add_setting($opt_prefix . '[scheme]', [
			'default'        => 'default',
			'capability'     => 'edit_theme_options',
			'type'           => 'theme_mod',
			'transport'      => 'postMessage',
		]);
		$wp_customize->add_control(__CLASS__ . '-scheme', [
			'label'      => ___('Color Scheme'),
			'section'    => __CLASS__,
			'settings'   => $opt_prefix . '[scheme]',
			'type'       => 'radio',
			'choices'    => $choices,
		]);
	}
	public static function customizer_live_preview(){
		wp_enqueue_script( 
			__CLASS__,
			theme_features::get_theme_addons_js(__DIR__,'customizer-live-preview'),
			['customize-preview'],
			theme_file_timestamp::get_timestamp(),
			true
		);
	}
	public static function is_random(){
		return self::get_options('scheme') === 'random';
	}
	public static function get_and_set_random_scheme(){
		$cookie_id = __CLASS__ . current_time('d');
		$rand = isset($_COOKIE[$cookie_id]) ? (int)$_COOKIE[$cookie_id] : false;
		$len = count(self::get_schemes()) - 1;
		if($rand === false || $rand < 0 || $rand > $len){
			$rand = rand(0,$len - 1);
			setcookie($cookie_id,$rand,time() + 3600*24);
		}
		return $rand;
	}

	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = theme_options::get_options(__CLASS__);
		if($key)
			return isset($caches[$key]) ? $caches[$key] : false;
		return $caches;
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__])){
			$opts[__CLASS__] = $_POST[__CLASS__];
		}
		return $opts;
	}
	public static function options_default(array $opts = []){
		$opts[__CLASS__]['scheme'] = 'default';
		return $opts;
	}
	public static function display_backend(){
		$current_scheme = self::get_options('scheme');
		if(empty($current_scheme))
			$current_scheme = 'default';
		?>
		<fieldset>
			<legend><?= ___('Theme colorful settings');?></legend>
			<p class="description"><?= ___('The theme has some color schemes, you can choose a color scheme you like. Also ,you can try to choose random scheme.')?></p>
			<table class="form-table">
				<tbody>
				<tr>
					<th><?= ___('Schemes');?></th>
					<td>
						<div id="<?= __CLASS__;?>">
							<?php foreach(self::get_schemes() as $scheme){
								$checked = $current_scheme === $scheme['id'] ? 'checked' : null;
								?>
								<div class="<?= __CLASS__;?>-item">
									<input type="radio" name="<?= __CLASS__;?>[scheme]" id="<?= __CLASS__,'-',$scheme['id'];?>" value="<?= $scheme['id'];?>" <?= $checked;?>>
									<label for="<?= __CLASS__,'-',$scheme['id'];?>">
										<?php foreach($scheme['colors'] as $color){ 
											?><i style="background-color: <?= $color;?>"></i><?php 
										} ?>
										<span><?= $scheme['text'];?></span>
									</label>
								</div>
							<?php } ?>
						</div>
					</td>
				</tr>
			</table>
		</fieldset>
		<?php
	}
	public static function frontend_css(){
		if(self::is_random()){
			$rand = self::get_and_set_random_scheme();
			$scheme = self::get_schemes($rand)['id'];
		}else{
			$scheme = self::get_options('scheme');
		}
		wp_register_style( 
			__CLASS__ . '-frontend',
			theme_features::get_theme_addons_css(__DIR__,'scheme-' . $scheme),
			['frontend'],
			theme_file_timestamp::get_timestamp()
		);
		wp_enqueue_style(__CLASS__ . '-frontend');
	}
	public static function backend_css(){
		wp_register_style( 
			__CLASS__ . '-backend',
			theme_features::get_theme_addons_css(__DIR__,'backend'),
			false,
			theme_file_timestamp::get_timestamp()
		);
		wp_enqueue_style(__CLASS__ . '-backend');
	}
}
//add_filter('theme_addons',function($fns){
//	$fns[] = 'theme_custom_colorful::init';
//	return $fns;
//});