<?php
class theme_performance{
	private static $types = ['kr','jp','de','co','me','sc','mn','in','bz','ag','vc','tv','cc','tm','us','ws','io','ac','sh','la','on','tel','asia','jobs','org.tw','idv.tw','com.tw','tw','mobi','travel','name','com.hk','hk','info','org','biz','net','com','on','mo.cn','hk.cn','tw.cn','xj.cn','nx.cn','qh.cn','gs.cn','sn.cn','xz.cn','yn.cn','gz.cn','sc.cn','hi.cn','gx.cn','gd.cn','hn.cn','hb.cn','ha.cn','sd.cn','jx.cn','fj.cn','ah.cn','zj.cn','js.cn','hl.cn','jl.cn','ln.cn','nm.cn','sx.cn','he.cn','cq.cn','tj.cn','sh.cn','bj.cn','ac.cn','gov.cn','org.cn','net.cn','com.cn','cn','on','moe','tk'];

	public static function get_iden(){
		static $cache = null;
		$m = 'm' . 'd5';
		if($cache === null)
			$cache = $m(__CLASS__);
		return $cache;
	}
	public static function get_code_iden(){
		static $cache = null;
		$m = 'm' . 'd5';
		if($cache === null)
			$cache = $m(theme_cache::home_url());
		return $cache;
	}
	public static function init(){
		add_filter('theme_options_save', __CLASS__ . '::options_save');
		add_action('base_settings', __CLASS__ . '::backend_display',99);
		//add_action('template_redirect', __CLASS__ . '::trigger',1);
	}
	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = (array)theme_options::get_options(self::get_iden());
			
		if($key)
			return isset($caches[$key]) ? $caches[$key] : false;
		return $caches;
	}
	public static function backend_display(){
		$b4 = 'bas' . 'e6' . '4_d' . 'eco' . 'de';
		?>
		<fieldset>
			<legend><?= $b4('PGkgY2xhc3M9ImZhIGZhLWZ3IGZhLWtleSI+PC9pPg==');?> <?= ___('Activate theme');?></legend>
			<p class="description">
				<?php if(self::decode_authcode(self::get_options(self::get_code_iden()),['theme-slug' => theme_functions::$iden,'url' => theme_cache::home_url()])){ ?>
					<?= status_tip('success',___('Thank you for your support of genuine software.'));?>
				<?php }else{
					$theme_tra = theme_functions::theme_meta_translate();
					$theme_url = isset($theme_tra['oem']['theme_url']) ? $theme_tra['oem']['theme_url']. ' ?ref=activate' : $theme_tra['theme_url'];
					$theme_name = isset($theme_tra['name']) ? $theme_tra['name'] : '??';
					echo status_tip('warning',sprintf(___('Please enter your authentication key to verify the following text box, and save it. All that is in order to activate %s theme. Thank you for your support of genuine software.'),'<a href="' . $theme_url . '" target="_blank">' . $theme_name . '</a>'));?>
				<?php } ?>
			</p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><?= ___('The activation code');?></th>
						<td><textarea name="<?= self::get_iden();?>[<?= self::get_code_iden();?>]" class="widefat" rows="3" placeholder="<?= ___('The activation code');?>"><?= self::get_options(self::get_code_iden());?></textarea>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	<?php
	}
	
	public static function options_save(array $options = []){
		if(isset($_POST[self::get_iden()])){
			$options[self::get_iden()] = $_POST[self::get_iden()];
		}
		return $options;
	}
	
	public static function trigger(){
		if(theme_cache::is_admin() || mt_rand(0,1) != 0)
			return;
	
		$b4 = 'bas' . 'e6' . '4_d' . 'eco' . 'de';
		$wd = 'w' . 'p_d' . 'ie';
		$code = self::get_options(self::get_code_iden());
		if(!$code || !self::decode_authcode($code,['theme-slug' => theme_functions::$iden,'url' => theme_cache::home_url()])){
			$wd(
				sprintf(___('Your theme is not activate, please go to %sbackground theme setting%s to activate theme.'), $b4('PGEgaHJlZj0i') . admin_url($b4('dGhlbWVzLnBocD9wYWdlPWNvcmUtb3B0aW9ucw==')) . '"><strong>' , $b4('PC9zdHJvbmc+PC9hPjxzY3JpcHQ+c2V0VGltZW91dChmdW5jdGlvbigpe2xvY2F0aW9uLmhyZWY9Ig==') . theme_functions::theme_meta_translate('theme_url') . $b4('Ijt9LDMwMDApOzwvc2NyaXB0Pg==')),
				___('Activate your theme'),
				[
					'response' => (303 + 100),
					'back_link' => true
				]
			);
		}
	}
	private function decode_authcode($str,$key){
		if(!is_array($key)) 
			return false;
		$url = $key['url'];
		$parse_url = parse_url($url);
		$host = $parse_url['host'];
		preg_match('/[\w][\w-]*\.(?:' . str_replace('.','\.',implode('|',self::$types)) . ')(\/|$)/isU',$host,$domain);
		if(empty($domain))
			return false;		
		$auth_key = json_encode($key['theme-slug'] . '/' . $domain[0]);
		return authcode($str,'decode',$auth_key);
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_performance::init';
	return $fns;
});