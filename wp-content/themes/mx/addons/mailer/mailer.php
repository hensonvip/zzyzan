<?php
/** 
 * @version 1.0.0
 */
class theme_mailer{
	private static $debug = false;
	public static function init(){

		/** ajax */
		add_action('wp_ajax_nopriv_' . __CLASS__, __CLASS__ . '::process');
		add_action('wp_ajax_' . __CLASS__, __CLASS__ . '::process');

		add_filter('theme_options_save' , __CLASS__ . '::options_save');
		
		add_action('base_settings' , __CLASS__ . '::display_backend',90);

		add_action('backend_js_config',__CLASS__ . '::backend_js_config');
		
		if(!self::is_enabled())
			return false;

		add_action('phpmailer_init', __CLASS__ . '::phpmailer_init_smtp');
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__])){
			$opts[__CLASS__] = $_POST[__CLASS__];
		}
		return $opts;
	}
	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = theme_options::get_options(__CLASS__);
		if($key)
			return isset($caches[$key]) ? $caches[$key] : false;
		return $caches;
	}
	public static function get_types($key = null){
		$types = [
			'From' => [
				'title' => ___('Form mail'),
				'type' => 'email',
				'placeholder' => ___('E.g., Jack@gmail.com'),
				'des' => ___('You can specify the email address that emails should be sent from. If you leave this blank, the default email will be used.'),
			],
			'FromName' => [
				'title' => ___('From Name'),
				'placeholder' => ___('E.g., Jack'),
				'des' => ___('You can specify the name that emails should be sent from. If you leave this blank, the emails will be sent from your blog name.'),
			],
			'Host' => [
				'title' => ___('SMTP host'),
				'placeholder' => ___('E.g., smtp.gmail.com'),
				'des' => ___('Send all emails via this SMTP server.'),
			],
			'Port' => [
				'title' => ___('SMTP port'),
				'type' => 'number',
				'placeholder' => ___('E.g., 25'),
				'des' => ___('TCP port to connect to.'),
			],
			'SMTPSecure' => [
				'title' => ___('SMTP secure type'),
				'placeholder' => ___('E.g., tls'),
				'des' => ___('Enable TLS encryption, `ssl` also accepted'),
			],
			'Username' => [
				'title' => ___('Username'),
				'placeholder' => ___('E.g., Jack@gmail.com'),
				'des' => ___('SMTP username.'),
			],
			'Password' => [
				'title' => ___('Password'),
				'type' => 'password',
				'placeholder' => ___('Your mail password'),
				'des' => ___('SMTP password.'),
			],
			
		];
		if($key)
			return isset($type[$key]) ? $type[$key] : false;
		return $types;
	}
	public static function display_backend(){
		?>
		<fieldset id="<?= __CLASS__;?>">
			<legend><i class="fa fa-envelope fa-fw"></i> <?= ___('SMTP mail settings');?></legend>
			<p class="description"><?= ___('Send mail using smtp server instead of the default mode.');?></p>
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
					<?php foreach(self::get_types() as $k => $v){ ?>
						<tr>
							<th><label for="<?= __CLASS__;?>-<?= $k;?>"><?= $v['title'];?></label></th>
							<td>
								<input type="<?= isset($v['type']) ? $v['type'] : 'text';?>" name="<?= __CLASS__;?>[<?= $k;?>]" id="<?= __CLASS__;?>-<?= $k;?>" value="<?= stripslashes(self::get_options($k));?>" placeholder="<?= $v['placeholder'];?>" class="<?= isset($v['type']) && $v['type'] === 'number' ? 'short-text' : 'widefat';?>">
								<p class="description"><?= $v['des'];?></p>
							</td>
						</tr>
					<?php } ?>
					<tr>
						<th><label for="<?= __CLASS__;?>-test-mail"><?= ___('Test');?></label></th>
						<td>
							<div id="<?= __CLASS__;?>-tip" class="page-tip none"></div>
							<div id="<?= __CLASS__;?>-area-btn">
								<input type="email" id="<?= __CLASS__;?>-test-mail" placeholder="<?= ___('Type your test mail');?>">
								<a id="<?=__CLASS__;?>-test-btn" href="javascript:;" class="button button-primary"><i class="fa fa-send"></i> <?= ___('Send a test mail');?></a>
							</div>
						</td>
					</tr>
					
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function is_enabled(){
		return self::get_options('enabled') == 1;
	}
	public static function process(){
		//theme_features::check_nonce();
		theme_features::check_referer();
		$output = [];

		$type = isset($_REQUEST['type']) && is_string($_REQUEST['type']) ? $_REQUEST['type'] : null;
		
		switch($type){
			/**
			 * test
			 */
			case 'test':
				if(!theme_cache::current_user_can('manage_options')){
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'invaild_permission',
						'msg' => ___('Sorry, your permission is invaild.'),
					]));
				}
				$test = isset($_POST['test']) && filter_var($_POST['test'],FILTER_VALIDATE_EMAIL) ? $_POST['test'] : false;

				if(!$test){
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'invaild_test_mail',
						'msg' => ___('Sorry, test mail is invaild.'),
					]));
				}
				self::$debug = true;
				
				ob_start();
				?>
				<pre><?php
					echo wp_mail(
						$test,
						___('This is a test email.'),
						___('This is a test email generated by your blog.')
					);
					?></pre>
				<?php
				$mail = ob_get_contents();
				ob_end_clean();

				die(theme_features::json_format([
					'status' => 'info',
					'code' => 'unknow',
					'msg' => $mail,
				]));
			default:
				die(theme_features::json_format([
					'status' => 'error',
					'code' => 'invaild_param',
					'msg' => ___('Sorry, param is invaild.'),
				]));
					
		}
	}
	public static function phpmailer_init_smtp($phpmailer){
		$phpmailer->isSMTP();
		foreach(self::get_types() as $k => $v){
			if(self::$debug === true){
				$phpmailer->$k = stripslashes($_POST[$k]);
			}else{
				$phpmailer->$k = stripslashes(self::get_options($k));
			}
		}
		if(self::$debug === true)
			$phpmailer->SMTPDebug = 4;
		$phpmailer->SMTPAuth = true;
	}
	public static function backend_js_config(array $config){
		$config[__CLASS__] = [
			'process_url' => theme_features::get_process_url([
				'action' => __CLASS__,
				'type' => 'test',
			]),
		];
		return $config;
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_mailer::init';
	return $fns;
});