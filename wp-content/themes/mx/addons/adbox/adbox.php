<?php
/**
 * @version 1.0.1
 */
class theme_adbox{

	public static function init(){
		add_filter('theme_options_save', __CLASS__ . '::options_save');
		add_action('page_settings', __CLASS__ . '::display_backend');
		include __DIR__ . '/widget.php';
	}

	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__]))
			$opts[__CLASS__] = $_POST[__CLASS__];
		return $opts;
	}
	public static function get_options($key = null){
		$caches = null;
		if($caches === null)
			$caches = (array)theme_options::get_options(__CLASS__);

		if($key)
			return isset($caches[$key]) ? $caches[$key] : null;
		return $caches;
	}
	public static function get_ad($key,$device){
		$ads = self::get_options('ads');
		return isset($ads[$device][$key]) ? stripslashes($ads[$device][$key]) : null;
	}
	public static function display_frontend($key){
		$device = wp_is_mobile() ? 'mobile' : 'desktop';
		return self::get_ad($key,$device);
	}
	private static function loop_options(){
		$types = [
			'below-header-menu' => [
				'name' => ___('Below the header menu (not in homepage)'),
				'img-url' => '//ww4.sinaimg.cn/large/686ee05djw1ewafyz00kcj205k02tgli.jpg',
			],
			'above-footer' => [
				'name' => ___('Above the footer (all page)'),
				'img-url' => '//ww4.sinaimg.cn/large/686ee05djw1ewaghq2yzej205l02rglg.jpg',
			],
			'below-post-title' => [
				'name' => ___('Below the post title (singular post page)'),
				'img-url' => '//ww1.sinaimg.cn/large/686ee05djw1ewagy0x0crj205k03uwei.jpg',
			],
			'below-adjacent-post' => [
				'name' => ___('Below the adjacent post (singular post page)'),
				'img-url' => '//ww1.sinaimg.cn/large/686ee05djw1ewagzhziv3j205k03u74b.jpg',
			],
		];
		foreach($types as $k => $v){
			?>
			<tr>
			<th>
				<label for="<?= __CLASS__;?>-ads-desktop-<?= $k;?>">
					<?= $v['name'];?>
					<br>
					<img src="<?= $v['img-url'];?>" alt="desktop-ads-<?= $k;?>">
				</label>
			</th>
			<td>
				<?php foreach([
					'desktop' => ___('Desktop codes'),
					'mobile' => ___('Mobile codes'),
				] as $device => $name){ ?>
					<label for="<?= __CLASS__;?>-ads-<?= $device;?>-<?= $k;?>"><?= $name;?></label>
					<textarea 
						name="<?= __CLASS__;?>[ads][<?= $device;?>][<?= $k;?>]" 
						id="<?= __CLASS__;?>-ads-<?= $device;?>-<?= $k;?>" 
						rows="3" 
						class="widefat" 
						placeholder="<?= ___('You can write AD HTML codes here.');?>" 
					><?= self::get_ad($k,$device);?></textarea>
				<?php } ?>
			</td>
			</tr>
			<?php
		}
	}
	public static function display_backend(){
		?>
		<fieldset>
			<legend><i class="fa fa-fw fa-jpy"></i> <?= ___('ADs settings');?></legend>
			<p class="description">
				<?= ___('You can put some ADs into your site. Here are some AD areas for using.');?>
			</p>
			<table class="form-table">
			<tbody>
				<?php self::loop_options();?>
			</tbody>
			</table>
		</fieldset>
		<?php
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_adbox::init';
	return $fns;
});