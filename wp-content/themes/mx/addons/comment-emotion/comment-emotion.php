<?php
/*
Feature Name:	theme_comment_emotion
Feature URI:	http://inn-studio.com
Version:		2.0.2
Description:	
Author:			INN STUDIO
Author URI:		http://inn-studio.com
*/
class theme_comment_emotion{
	public static function init(){
		add_filter('theme_options_save',__CLASS__ . '::options_save');
		add_filter('theme_options_default',__CLASS__ . '::options_default');
		add_action('page_settings',__CLASS__ . '::display_backend');

		add_filter('pre_comment_content', __CLASS__ . '::filter_pre_comment_content');
	}
	public static function options_default(array $opts = []){
		$opts[__CLASS__] = [
			'kaomoji' => [
				'enabled' => 1,
				'items' => [
					'(⊙⊙！) ',
					'ƪ(‾ε‾“)ʃƪ(',
					'Σ(°Д°;',
					'눈_눈',
					'(๑>◡<๑) ',
					'(❁´▽`❁)',
					'(,,Ծ▽Ծ,,)',
					'（⺻▽⺻ ）',
					'乁( ◔ ౪◔)「',
					'ლ(^o^ლ)',
					'(◕ܫ◕)',
					'凸(= _=)凸'
				]
			],
			'img' => [
				'enabled' => 1,
				'items' => [
					'脸红' => 'http://ww2.sinaimg.cn/large/686ee05djw1eu8ijxc3p7g201c01c3yd.gif',
					'杯具' => 'http://ww1.sinaimg.cn/large/686ee05djw1eu8ikpw34jg201e01emx1.gif',
					'亚历山大' => 'http://ww1.sinaimg.cn/large/686ee05djw1eu8iliwosmg201e01e74h.gif',
					'想要' => 'http://ww1.sinaimg.cn/large/686ee05djw1eu8ilzci2jg202s02sglo.gif',
					'吃惊' => 'http://ww1.sinaimg.cn/large/686ee05djw1eu8j1vay4ej204h049jrb.jpg',
					'好样的' => 'http://ww3.sinaimg.cn/large/686ee05djw1eu8iomh5cbg203g03cdgx.gif',
				]
			]
		];
		$opts[__CLASS__]['kaomoji']['text'] = implode("\n",$opts[__CLASS__]['kaomoji']['items']);

		$items = [];
		foreach($opts[__CLASS__]['img']['items'] as $name => $url){
			$items[] = $name . ' = ' . $url;
		}
		$opts[__CLASS__]['img']['text'] = implode("\n",$items);
		
		return $opts;
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__])){
			$opts[__CLASS__] = $_POST[__CLASS__];
			/**
			 * kaomoji
			 */
			if(!empty($_POST[__CLASS__]['kaomoji']['text'])){
				$opts[__CLASS__]['kaomoji']['items'] = 
					array_map(function($v){
						return str_replace("\r",'',trim($v));
					},explode("\n",$opts[__CLASS__]['kaomoji']['text']));
			}
			
			/**
			 * img
			 */
			if(!empty($_POST[__CLASS__]['img']['text'])){
				$lines = array_filter(explode("\n",$opts[__CLASS__]['img']['text']));
				$items = [];
				foreach($lines as $v){
					if(trim($v) === '')
						continue;
					$line = explode('=',$v);
					$items[trim($line[0])] = trim($line[1]);
				}
				$opts[__CLASS__]['img']['items'] = $items;
			}
		}
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
	public static function display_backend(){
		?>
		<fieldset>
			<legend><i class="fa fa-fw fa-smile-o"></i> <?= ___('Comment emoticon settings');?></legend>
			<p class="description"><?= ___('Comment can use Kaomoji or image emotions if enable.');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><?= ___('Kaomoji emoticon');?></th>
						<td>
							<p>
								<label for="<?= __CLASS__;?>-kaomoji-enabled">
									<input type="checkbox" name="<?= __CLASS__;?>[kaomoji][enabled]" id="<?= __CLASS__;?>-kaomoji-enabled" value="1" <?= self::is_enabled('kaomoji') ? 'checked' : null;?> > 
									<?= ___('Enable');?>
								</label>
							</p>
							<p>
								<textarea name="<?= __CLASS__;?>[kaomoji][text]" id="<?= __CLASS__;?>-kaomoji-text" rows="5" class="widefat"><?= self::get_ems('kaomoji','text');?></textarea>
							</p>
							<p class="description"><?= ___('One per line.');?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?= ___('Image emoticon');?></th>
						<td>
							<p>
								<label for="<?= __CLASS__;?>-img-enabled">
									<input type="checkbox" name="<?= __CLASS__;?>[img][enabled]" id="<?= __CLASS__;?>-img-enabled" value="1" <?= self::is_enabled('img') ? 'checked' : null;?> > 
									<?= ___('Enable');?>
								</label>
							</p>
							<p>
								<textarea name="<?= __CLASS__;?>[img][text]" id="<?= __CLASS__;?>-img-text" rows="5" class="widefat" placeholder="<?= ___('E.g. img01 = http://exmaple.com/img01.jpg');?>"><?= self::get_ems('img','text');?></textarea>
							</p>
							<p class="description"><?= ___('One per line.');?></p>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function is_enabled($key){
		$opt = self::get_options($key);
		return isset($opt['enabled']) && $opt['enabled'] == 1;
	}
	public static function get_ems($key,$type){
		if($type === 'text')
			return stripslashes(self::get_options($key)['text']);
		return self::get_options($key)[$type];
	}
	public static function display_frontend($type){
		switch($type){
			case 'pop-btn':
				?>
				<?php if(self::is_enabled('kaomoji')){ ?>
					<a href="javascript:;" class="comment-emotion-pop-btn" data-target="#<?= __CLASS__;?>-kaomoji" title="<?= ___('Kaomoji');?>"><i class="fa fa-font"></i></a>
				<?php } ?>
				<?php if(self::is_enabled('img')){ ?>
					<a href="javascript:;" class="comment-emotion-pop-btn" data-target="#<?= __CLASS__;?>-img" title="<?= ___('Image emoticon');?>"><i class="fa fa-smile-o"></i></a>
				<?php } ?>
				<?php
				break;
			case 'pop':
				?>
				<div class="comment-emotion-area-pop">
					<?php if(self::is_enabled('kaomoji')){ ?>
						<div id="<?= __CLASS__;?>-kaomoji" class="pop">
							<?php
							foreach(self::get_ems('kaomoji','items') as $name => $item){
								$item = esc_html($item);
								?>
								<a href="javascript:;" data-content="<?= $item;?>"><?= $item;?></a>
							<?php } ?>
						</div>
					<?php } ?>
					<?php if(self::is_enabled('img')){ ?>
						<div id="<?= __CLASS__;?>-img" class="pop">
							<?php foreach(self::get_ems('img','items') as $name => $url){ ?>
								<a href="javascript:;" data-content="<?= '[',$name,']';?>"><img data-url="<?= esc_url($url);?>" alt="<?= esc_html($name);?>"></a>
							<?php } ?>
						</div>
					<?php } ?>
				</div><!-- .area-pop -->
				<?php
				break;
		}
	}
	public static function filter_pre_comment_content($content){
		if(!self::is_enabled('img'))
			return $content;
			
		$item_keys = [];
		$item_value = [];
		foreach(self::get_ems('img','items') as $k => $v){
			$item_keys[] = '[' . $k . ']';
			$item_value[] = '<img src="' . esc_url($v) . '" alt="' . ___('Emotion') . '" class="emotion">';
		}
		return str_replace( $item_keys, $item_value, $content );
	}
	public static function can_show(){
		static $cache = null;
		if($cache === null){
			global $post;
			$cache = theme_cache::is_singular() && !post_password_required() && comments_open();
		}
		return $cache;
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_comment_emotion::init';
	return $fns;
});