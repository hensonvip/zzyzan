<?php
/*
Feature Name:	theme-custom-homebox
Feature URI:	http://www.inn-studio.com
Version:		1.1.5
Description:	
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/

class theme_custom_homebox{
	
	public static function init(){
		
		add_filter('theme_options_save',__CLASS__ . '::options_save');
		
		add_action('page_settings',__CLASS__ . '::display_backend');

		add_action('publish_post',__CLASS__ . '::action_public_post');
	}
	public static function action_public_post(){
		self::delete_cache();
	}
	public static function keywords_to_html($keywords = null,$class = null){
		if(!$keywords) return false;
		/** 
		 * split per line
		 */
		$output_kws = [];
		$keyword_arr = explode("\n",$keywords);
		foreach($keyword_arr as $k => $v){
			$kw_arr = explode('=',$v);
			
			if(!isset($kw_arr[0]) || !isset($kw_arr[1]))
				continue;
				
			$output_kws[$k]['name'] = trim($kw_arr[0]);
			$output_kws[$k]['url'] = trim($kw_arr[1]);
		}
		return $output_kws;
	}
	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = (array)theme_options::get_options(__CLASS__);

		if($key)
			return isset($caches[$key]) ? $caches[$key] : null;
		return $caches;
	}

	private static function cat_checkbox_tpl($placeholder){
		$opt = self::get_options();
		$exists_cats = isset($opt[$placeholder]['cats']) ? (array)$opt[$placeholder]['cats'] : [];
		$cats = theme_cache::get_categories(array(
			'hide_empty' => false,
		));
		foreach($cats as $cat){
			$checked = !empty($exists_cats) && in_array($cat->term_id,$exists_cats) ? ' checked ' : null;
			?>
			<label for="<?= __CLASS__;?>-cats-<?= $placeholder;?>-<?= $cat->term_id;?>" class="button <?= empty($checked) ? null : 'button-primary';?>">
				<input 
					type="checkbox" 
					name="<?= __CLASS__;?>[<?= $placeholder;?>][cats][]"
					id="<?= __CLASS__;?>-cats-<?= $placeholder;?>-<?= $cat->term_id;?>"
					value="<?= $cat->term_id;?>"
					<?= $checked;?>
				/>
				<?= esc_html($cat->name);?> - <a href="<?= esc_url(get_category_link($cat->term_id));?>" target="<?= theme_functions::$link_target;?>"><?= urldecode($cat->slug);?></a>
			</label>
			<?php
		}
		unset($cats);
	}
	public static function display_backend(){
		$opt = array_filter((array)self::get_options());
		?>
		<fieldset>
			<legend><i class="fa fa-fw fa-home"></i> <?= ___('Theme home box settings');?></legend>
			<div id="<?= __CLASS__;?>-container" data-tpl="<?= esc_attr(self::get_home_box_tpl());?>">
				<?php
				if(!$opt){
					echo self::get_home_box_tpl(0);
				}else{
					foreach($opt as $k => $v){
						echo self::get_home_box_tpl($k,$opt);
					}
				}
				?>
			</div>
			<table class="form-table" id="<?= __CLASS__;?>-control">
				<tbody>
					<tr>
						<th scope="row"><?= ___('Control');?></th>
						<td>
							<a id="<?= __CLASS__;?>-add" class="add button-primary" href="javascript:;"><i class="fa fa-plus"></i> <?= ___('Add new item');?></a>
						</td>
					</tr>
				</tbody>
			</table>
			<input type="hidden" name="<?= __CLASS__;?>[hash]" value="<?= isset($opt['hash']) ? $opt['hash'] : md5(json_encode($opt));?>">
		</fieldset>
	<?php
	
	}
	private static function get_home_box_tpl($placeholder = '%placeholder%', array $boxes = []){
		$title = isset($boxes[$placeholder]['title']) ? stripcslashes($boxes[$placeholder]['title']) : null;
		
		if($placeholder !== '%placeholder%' && !$title)
			return false;
			
		$icon = isset($boxes[$placeholder]['icon']) ? $boxes[$placeholder]['icon'] : null;
		
		$link = isset($boxes[$placeholder]['link']) ? $boxes[$placeholder]['link'] : null;
		
		$number = isset($boxes[$placeholder]['number']) ? (int)$boxes[$placeholder]['number'] : 7;
		
		$display = isset($boxes[$placeholder]['display-type']) ? $boxes[$placeholder]['display-type'] : 'all';
		
		$keywords = isset($boxes[$placeholder]['keywords']) ? $boxes[$placeholder]['keywords'] : null;

		$ad = isset($boxes[$placeholder]['ad']) ? stripslashes($boxes[$placeholder]['ad']) : null;
		
		ob_start();
		?>
		<table 
			class="form-table <?= __CLASS__;?>-item item tpl-item" 
			id="<?= __CLASS__;?>-item-<?= $placeholder;?>" 
			data-placeholder="<?= $placeholder;?>" 
		>
		<tbody>
		<tr>
			<th><label for="<?= __CLASS__;?>-title-<?= $placeholder;?>"><?= ___('Title');?> - <?= $placeholder;?></label></th>
			<td>
				<input 
					type="text" 
					name="<?= __CLASS__;?>[<?= $placeholder;?>][title]" 
					id="<?= __CLASS__;?>-title-<?= $placeholder;?>" 
					class="widefat" 
					value="<?= esc_attr($title);?>" 
					placeholder="<?= ___('Box title');?>"
				> 
			</td>
		</tr>
		<tr>
			<th>
				<label for="<?= __CLASS__;?>-icon-<?= $placeholder;?>"><?= ___('Icon');?></label>
				<a href="//fortawesome.github.io/Font-Awesome/icons" target="_blank" title="<?= ___('Views all icons');?>">#<?= ___('ALL');?></a>
			</th>
			<td>
				<input 
					type="text" 
					value="<?= $icon;?>" 
					list="<?= __CLASS__;?>-icon-<?= $placeholder;?>-datalist" 
					name="<?= __CLASS__;?>[<?= $placeholder;?>][icon]" 
					id="<?= __CLASS__;?>-icon-<?= $placeholder;?>" 
					class="widefat" 
				><?php icon_option_list(__CLASS__ . '-icon-' . $placeholder . '-datalist');?>
			</td>
		</tr>
		<tr>
			<th><label for="<?= __CLASS__;?>-link-<?= $placeholder;?>"><?= ___('Link');?></label></th>
			<td>
				<input 
					type="url" 
					name="<?= __CLASS__;?>[<?= $placeholder;?>][link]" 
					id="<?= __CLASS__;?>-link-<?= $placeholder;?>" 
					class="widefat" 
					value="<?= esc_attr($link);?>" 
					placeholder="<?= ___('Box link (include http://)');?>"
				>
			</td>
		</tr>
		<tr>
			<th><label for="<?= __CLASS__;?>-number-<?= $placeholder;?>"><?= ___('Show posts');?></label></th>
			<td>
				<input 
					type="number" 
					name="<?= __CLASS__;?>[<?= $placeholder;?>][number]" 
					id="<?= __CLASS__;?>-number-<?= $placeholder;?>" 
					class="small-text" 
					value="<?= $number;?>" 
					placeholder="<?= ___('Posts number.');?>" 
					min="3" 
					step="4" 
				>
			</td>
		</tr>
		<tr>
			<th><label for="<?= __CLASS__;?>-display-type-<?= $placeholder;?>"><?= ___('Display type');?></label></th>
			<td>
				<select  
					name="<?= __CLASS__;?>[<?= $placeholder;?>][display-type]" 
					id="<?= __CLASS__;?>-display-type-<?= $placeholder;?>" 
					class="widefat" 
				>
					<?php the_option_list('all',___('All'),$display);?>
					<?php the_option_list('login',___('Only login'),$display);?>
					<?php the_option_list('logout',___('Only logout'),$display);?>
				</select>
			</td>
		</tr>
		
		<tr>
			<th><?= ___('Categories');?></th>
			<td>
				<?php self::cat_checkbox_tpl($placeholder);?>
			</td>
		</tr>
		<tr>
			<th><label for="<?= __CLASS__;?>-<?= $placeholder;?>-keywords"><?= ___('Keywords and links');?></label></th>
			<td>
				<textarea name="<?= __CLASS__;?>[<?= $placeholder;?>][keywords]" id="<?= __CLASS__;?>-<?= $placeholder;?>-keywords" cols="30" rows="5" class="widefat" placeholder="<?= ___('Eg. Tag1 = http://inn-studio.com');?>"><?= esc_textarea($keywords);?></textarea>
				<span class="description"><?= ___('Per keyword/line');?></span>
			</td>
		</tr>
		<tr>
			<th><label for="<?= __CLASS__;?>-<?= $placeholder;?>-ad"><?= ___('AD code');?></label></th>
			<td>
				<textarea name="<?= __CLASS__;?>[<?= $placeholder;?>][ad]" id="<?= __CLASS__;?>-<?= $placeholder;?>-ad" cols="30" rows="5" class="widefat" placeholder="<?= ___('HTML code will display below this box.');?>"><?= $ad;?></textarea>
			</td>
		</tr>
		<tr>
			<th><?= ___('Control');?></th>
			<td>
				<a href="javascript:;" class="del" data-target="<?= __CLASS__;?>-item-<?= $placeholder;?>"><i class="fa fa-exclamation-circle"></i> <?= ___('Delete this item');?></a>
			</td>
		</tr>
		</tbody>
		</table>
		<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__])){
			$opts[__CLASS__] = $_POST[__CLASS__];
			$old_hash = $_POST[__CLASS__]['hash'];
			
			unset($_POST[__CLASS__]['hash']);
			
			$new_hash = md5(json_encode($_POST[__CLASS__]));
			
			if($old_hash !== $new_hash){
				self::delete_cache();
				$opts[__CLASS__]['hash'] = $new_hash;
			}else{
				$opts[__CLASS__]['hash'] = $old_hash;
			}
		}
		return $opts;
	}
	public static function delete_cache(){
		theme_cache::delete('content',__CLASS__);
	}
	public static function set_cache($data){
		theme_cache::set('content',$data,__CLASS__,3600);
	}
	public static function get_cache(){
		return theme_cache::get('content',__CLASS__);
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_custom_homebox::init';
	return $fns;
});