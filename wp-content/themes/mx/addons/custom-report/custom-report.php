<?php
class theme_custom_report{
	public static function init(){
		add_filter('theme_options_save', __CLASS__ . '::options_save');
		add_action('page_settings', __CLASS__ . '::display_backend');
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__])){
			$opts[__CLASS__] = $_POST[__CLASS__];
		}
		return $opts;
	}
	public static function is_enabled(){
		return self::get_options('enabled') == 1;
	}
	public static function get_items($key = null){
		$items = self::get_options('items');
		if(!$items)
			return false;
		if($key)
			return isset($items[$key]) ? $items[$key] : false;
		return $items;
	}
	public static function display_backend(){
		$items = self::get_items();
		?>
		<fieldset>
			<legend><i class="fa fa-fw fa-flag"></i> <?= ___('Report settings');?></legend>
			<p class="description"><?= ___('Vistors can report to you as comment if some problems with post. Here are some keywords to use (only available in comment).');?></p>
			
			<input type="text" class="small-text text-select" value="%post_link%" title="<?= ___('Post link');?>" readonly>
			
			<input type="text" class="small-text text-select" value="%post_id%" title="<?= ___('Post ID');?>" readonly>
			
			<input type="text" class="small-text text-select" value="%report_name%" title="<?= ___('Report name');?>" readonly>
			
			
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
						<th><label for="<?= __CLASS__;?>-page"><?= ___('The page of report collection');?></label></th>
						<td>
							<?php theme_features::page_option_list(__CLASS__,'page');?>
						</td>
					</tr>
					<tr>
						<th><label for="<?= __CLASS__;?>-export-text"><?= ___('Export text');?></label></th>
						<td>
							<input type="text" name="<?= __CLASS__;?>[export-text]" id="<?= __CLASS__;?>-export-text" value="<?= esc_attr(self::get_options('export-text'));?>" placeholder="<?= ___('The link export text');?>" class="widefat">
						</td>
					</tr>
				</tbody>
			</table>
			<div id="<?= __CLASS__;?>-container" data-tpl="<?= esc_attr(self::get_tpl_backend());?>" hidden>
				<?php
				if($items){
					foreach($items as $k => $v){
						echo self::get_tpl_backend($k,$items);
					}
				}else{
					echo self::get_tpl_backend(0);
				}
				?>
			</div>
			
			<table class="form-table" id="<?= __CLASS__;?>-control" hidden>
			<tbody>
				<tr>
					<th><?= ___('Control');?></th>
					<td>
						<a href="javascript:;" id="<?= __CLASS__;?>-add" class="add button button-primary"><i class="fa fa-plus"></i> <?= ___('Add new item');?></a>
					</td>
				</tr>
			</tbody>
			</table>
		</fieldset>

		<?php
	}
	public static function get_tpl_backend($placeholder = '%placeholder%', array $item = []){
		$name = isset($item[$placeholder]['name']) ? esc_attr($item[$placeholder]['name']) : null;
		
		$type = isset($item[$placeholder]['type']) ? $item[$placeholder]['type'] : 'standard';
		
		$des = isset($item[$placeholder]['des']) ? esc_attr($item[$placeholder]['des']) : null;
		
		$question = isset($item[$placeholder]['question']) ? esc_attr($item[$placeholder]['question']) : null;

		$success = isset($item[$placeholder]['success']) ? esc_attr($item[$placeholder]['success']) : null;

		$comment = isset($item[$placeholder]['comment']) ? esc_attr($item[$placeholder]['comment']) : null;

		ob_start();
		?>
		<table id="<?= __CLASS__;?>-item-<?= $placeholder;?>" class="form-table item tpl-item" data-placeholder="<?= $placeholder;?>">
			<tbody>
			<tr>
				<th><label for="<?= __CLASS__;?>-<?= $placeholder;?>-name"><?= ___('Name');?> - <?= $placeholder;?></label></th>
				<td>
					<input name="<?= __CLASS__;?>[<?= $placeholder;?>][name]" type="text" id="<?= __CLASS__;?>-<?= $placeholder;?>-name" value="<?= $name;?>" placeholder="<?= ___('The item name, short is better');?>" class="widefat">
				</td>
			</tr>
			<tr>
				<th><label for="<?= __CLASS__;?>-<?= $placeholder;?>-type"><?= ___('Type');?></label></th>
				<td>
					<select name="<?= __CLASS__;?>[<?= $placeholder;?>][type]" id="<?= __CLASS__;?>-<?= $placeholder;?>-type" class="widefat">
						<?php the_option_list('standard',___('Standard'),$type);?>
						<?php the_option_list('custom',___('Custom'),$type);?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="<?= __CLASS__;?>-<?= $placeholder;?>-question"><?= ___('Question');?></label></th>
				<td>
					<input name="<?= __CLASS__;?>[<?= $placeholder;?>][question]" type="text" id="<?= __CLASS__;?>-<?= $placeholder;?>-question" value="<?= $question;?>" placeholder="<?= ___('The item question, only shows in custom type');?>" class="widefat">
				</td>
			</tr>
			<tr>
				<th><label for="<?= __CLASS__;?>-<?= $placeholder;?>-des"><?= ___('Description');?></label></th>
				<td>
					<input name="<?= __CLASS__;?>[<?= $placeholder;?>][des]" type="text" id="<?= __CLASS__;?>-<?= $placeholder;?>-des" value="<?= $des;?>" placeholder="<?= ___('The item description');?>" class="widefat">
				</td>
			</tr>
			<tr>
				<th><label for="<?= __CLASS__;?>-<?= $placeholder;?>-success"><?= ___('After successful reported');?></label></th>
				<td>
					<input name="<?= __CLASS__;?>[<?= $placeholder;?>][success]" type="text" id="<?= __CLASS__;?>-<?= $placeholder;?>-success" value="<?= $success;?>" placeholder="<?= ___('The item after successful reported tip');?>" class="widefat">
				</td>
			</tr>
			<tr>
				<th><label for="<?= __CLASS__;?>-<?= $placeholder;?>-comment"><?= ___('Comment content');?></label></th>
				<td>
					<input name="<?= __CLASS__;?>[<?= $placeholder;?>][comment]" type="text" id="<?= __CLASS__;?>-<?= $placeholder;?>-comment" value="<?= $comment;?>" placeholder="<?= ___('The item comment content');?>" class="widefat">
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
		$html = html_minify(ob_get_contents());
		ob_end_clean();
		return $html;
	}
	public static function options_default(array $opts = []){
		$opts[__CLASS__] = [
			'enabled' => -1,
			'export-text' => ___('Is this problem post?'),
			'items' => [
				[
					'name' => ___('Download failure'),
					'type' => 'standard',
					'question' => '',
					'des' => ___('I can not download.'),
					'success' => ___('Report successful, we will fix it soon, thank you.'),
					'comment' => ___('%report_name%: %post_id% - %post_link%.'),
				],[
					'name' => ___('Duplicate post'),
					'type' => 'custom',
					'question' => ___('Whats duplicate with post ID?'),
					'des' => ___('It is duplicate post.'),
					'success' => ___('Report successful, we will fix it soon, thank you.'),
					'comment' => ___('%report_name%: %post_id% - %post_link%. Duplicate post %duplicate_link%.'),
				],[
					'name' => ___('Other'),
					'type' => 'custom',
					'question' => ___('Whats the reason?'),
					'des' => ___('Other reason.'),
					'success' => ___('Report successful, we will fix it soon, thank you.'),
					'comment' => ___('%report_name%: %post_id% - %post_link%. %detail%'),
				]
			],
		];
		return $opts;
	}
	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = (array)theme_options::get_options(__CLASS__);
		if($key){
			if(isset($caches[$key])){
				return $caches[$key];
			}else{
				$caches[$key] = isset(self::options_default()[__CLASS__][$key]) ? self::options_default()[__CLASS__][$key] : false;
				return $caches[$key];
			}
		}
		return $caches;
	}
	public static function get_page_url(){
		if(self::get_options('page') > 0)
			return theme_cache::get_permalink(self::get_options('page'));
		return false;
	}
	public static function display_frontend(){
		global $psot;
		if(!self::get_page_url())
			return false;
		?>
		<a class="tooltip top" href="<?= self::get_page_url();?>" target="_blank" rel="nofollow" title="<?= self::get_options('export-text');?>"><i class="fa fa-flag"></i></a>
		<?php
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_custom_report::init';
	return $fns;
});