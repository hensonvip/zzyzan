<?php
/*
Feature Name:	Post Share
Feature URI:	http://www.inn-studio.com
Version:		2.0.3
Description:	
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
class theme_post_share{
	public static function init(){
		add_filter('theme_options_default', __CLASS__ . '::options_default');
		add_filter('theme_options_save', __CLASS__ . '::options_save');
		add_action('page_settings', __CLASS__ . '::backend_display');
	}
	public static function get_options($key = null){
		static $caches = [];
		if(!$caches)
			$caches = (array)theme_options::get_options(__CLASS__);
		if($key){
			return isset($caches[$key]) ? $caches[$key] : null;
		}
		return $caches;
	}
	public static function display($args = []){
		global $post;
		$opt = self::get_options();
		$img_url = theme_features::get_thumbnail_src($post->ID);
		$defaults = array(
			'post_title_text' => theme_cache::get_the_title($post->ID),
			'post_url' => theme_cache::get_permalink($post->ID),
			'blog_name' => theme_cache::get_bloginfo('name'),
			'blog_url' => theme_cache::home_url(),
			'img_url' => esc_url($img_url),
			'post_excerpt' => esc_attr(mb_substr(html_minify(strip_tags(get_the_excerpt())),0,120)),
			'post_content' => esc_attr(mb_substr(html_minify(strip_tags(get_the_content())),0,120)),
			'author' => theme_cache::get_the_author_meta('display_name',$post->post_author),
		);
		$output_keywords = array_merge($defaults,$args);
	
		$tpl_keywords = array(
			'%post_title_text%',
			'%post_url%',
			'%blog_name%',
			'%blog_url%',
			'%img_url%',
			'%post_excerpt%',
			'%post_content%',
			'%author%'
			
		);
		$post_share_code = stripslashes(str_ireplace($tpl_keywords,$output_keywords,$opt['code']));

		echo $post_share_code;
	}
	
	public static function backend_display(){
		
		$opt = self::get_options();
		
		?>
		<fieldset>
			<legend><i class="fa fa-fa fa-share-alt"></i> <?= ___('Posts share settings');?></legend>
			<p class="description">
				<?= ___('Share your post to everywhere. Here are some keywords that can be used:');?>
			</p>
			<p class="description">
				<input type="text" class="small-text text-select" value="%post_title_text%" title="<?= ___('Post Title text');?>" readonly />
				<input type="text" class="small-text text-select" value="%post_url%" title="<?= ___('Post URL');?>" readonly />
				<input type="text" class="small-text text-select" value="%blog_name%" title="<?= ___('Blog name');?>" readonly />
				<input type="text" class="small-text text-select" value="%blog_url%" title="<?= ___('Blog URL');?>" readonly />
				<input type="text" class="small-text text-select" value="%img_url%" title="<?= ___('The first picture of the post.');?>" readonly />
				<input type="text" class="small-text text-select" value="%post_excerpt%" title="<?= ___('The excerpt of post.');?>" readonly />
				<input type="text" class="small-text text-select" value="%post_content%" title="<?= ___('The content of post.');?>" readonly />
			</p>
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
					<tr>
						<th scope="row"><?= ___('HTML codes');?></th>
						<td><textarea id="<?= __CLASS__;?>_code" name="<?= __CLASS__;?>[code]" class="widefat" cols="30" rows="10"><?= stripslashes($opt['code']);?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row"><?= esc_html(___('Restore'));?></th>
						<td>
							<label for="<?= __CLASS__;?>_restore">
								<input type="checkbox" id="<?= __CLASS__;?>_restore" name="<?= __CLASS__;?>[restore]" value="1"/>
								<?= ___('Restore the post share settings');?>
							</label>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	<?php
	
	}
	
	public static function options_default(array $opts = []){
		ob_start();
		?>
<div class="bdshare_t bdsharebuttonbox" data-tag="bd_share" data-bdshare="{
	'bdText':'%post_title_text% by %author% <?= ___('-- from %blog_name%');?>',
	'bdUrl':'%post_url%',
	'bdPic':'%img_url%'
}">
	<span class="description"><?= ___('Share to: ');?></span>
	<a class="bds_tsina" data-cmd="tsina" title="<?= sprintf(___('Share to %s'),___('Sina Weibo'));?>" href="javascript:;"></a>
	<a class="bds_qzone" data-cmd="qzone" href="javascript:;" title="<?= sprintf(___('Share to %s'),___('QQ zone'));?>"></a>
	<a class="bds_tieba" data-cmd="tieba" title="<?= sprintf(___('Share to %s'),___('Tieba'));?>" href="javascript:;"></a>
	<a class="bds_weixin" data-cmd="weixin" title="<?= sprintf(___('Share to %s'),___('Wechat'));?>" href="javascript:;"></a>
	<a class="bds_more" data-cmd="more" href="javascript:;"></a>
</div>				
<?php
		$content = ob_get_contents();
		ob_end_clean();

		$opts[__CLASS__] = array(
			'enabled' => 1,
			'code' => $content,
		);

		return $opts;
	}
	public static function is_enabled(){
		return true;
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__]) && !isset($_POST[__CLASS__]['restore'])){
			$opts[__CLASS__] = $_POST[__CLASS__];
		}
		return $opts;
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_post_share::init';
	return $fns;
});